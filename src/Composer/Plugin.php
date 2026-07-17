<?php

declare(strict_types=1);

namespace OidcClient\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstall',
            ScriptEvents::POST_UPDATE_CMD => 'onPostInstall',
        ];
    }

    public function onPostInstall(): void
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $rootDir = dirname($vendorDir);
        $composerJsonPath = $rootDir . DIRECTORY_SEPARATOR . 'composer.json';

        if (!is_writable($composerJsonPath) || !is_readable($composerJsonPath)) {
            $this->io->writeError('OidcClient: skipping automatic composer.json update (unreadable/unwritable).');
            return;
        }

        $content = file_get_contents($composerJsonPath);
        if ($content === false) {
            $this->io->writeError('OidcClient: failed to read composer.json');
            return;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            $this->io->writeError('OidcClient: composer.json is not valid JSON');
            return;
        }

        $providerClass = 'OidcClient\\Integration\\Laravel\\OidcServiceProvider';

        if (!isset($data['extra'])) {
            $data['extra'] = [];
        }

        if (!isset($data['extra']['laravel'])) {
            $data['extra']['laravel'] = ['providers' => []];
        }

        if (!isset($data['extra']['laravel']['providers'])) {
            $data['extra']['laravel']['providers'] = [];
        }

        // Ensure providers is an array
        if (!is_array($data['extra']['laravel']['providers'])) {
            $data['extra']['laravel']['providers'] = (array) $data['extra']['laravel']['providers'];
        }

        // Add provider if not present
        if (!in_array($providerClass, $data['extra']['laravel']['providers'], true)) {
            $data['extra']['laravel']['providers'][] = $providerClass;

            // Backup original composer.json
            @copy($composerJsonPath, $composerJsonPath . '.oidc.bak');

            $newContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            $result = file_put_contents($composerJsonPath, $newContent);

            if ($result === false) {
                $this->io->writeError('OidcClient: failed to write updated composer.json');
                return;
            }

            $this->io->write('OidcClient: added Laravel service provider to composer.json (extra.laravel.providers)');
        } else {
            $this->io->write('OidcClient: Laravel service provider already present in composer.json');
        }

        // Also ensure PSR-4 mapping for CodeIgniter / namespaced usage is present
        $psr4Namespace = 'OidcClient\\';
        $psr4Path = 'vendor/oidc-client/sdk/packages/php/core/src/';

        if (!isset($data['autoload'])) {
            $data['autoload'] = ['psr-4' => []];
        }

        if (!isset($data['autoload']['psr-4'])) {
            $data['autoload']['psr-4'] = [];
        }

        // Ensure psr-4 is an array
        if (!is_array($data['autoload']['psr-4'])) {
            $data['autoload']['psr-4'] = (array) $data['autoload']['psr-4'];
        }

        if (!array_key_exists($psr4Namespace, $data['autoload']['psr-4'])) {
            $data['autoload']['psr-4'][$psr4Namespace] = $psr4Path;

            // Backup original composer.json if not already backed up
            @copy($composerJsonPath, $composerJsonPath . '.oidc.bak');

            $newContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
            $result = file_put_contents($composerJsonPath, $newContent);

            if ($result === false) {
                $this->io->writeError('OidcClient: failed to write updated composer.json (psr-4)');
                return;
            }

            $this->io->write('OidcClient: added PSR-4 autoload mapping for OidcClient to composer.json (autoload.psr-4)');
        } else {
            $this->io->write('OidcClient: PSR-4 autoload mapping for OidcClient already present in composer.json');
        }

        // If this is a CodeIgniter project, try to enable composer autoload in application/config/config.php
        $ciConfigPath = $rootDir . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

        if (is_file($ciConfigPath)) {
            if (!is_readable($ciConfigPath) || !is_writable($ciConfigPath)) {
                $this->io->writeError('OidcClient: found CodeIgniter config file but it is not readable/writable; skipping composer_autoload update.');
            } else {
                $ciContent = file_get_contents($ciConfigPath);
                if ($ciContent === false) {
                    $this->io->writeError('OidcClient: failed to read CodeIgniter config.php');
                } else {
                    $updated = false;

                    // If composer_autoload line exists, replace its value
                    if (preg_match("/\$config\['composer_autoload'\]\s*=\s*[^;]+;/", $ciContent)) {
                        $newCiContent = preg_replace("/\$config\['composer_autoload'\]\s*=\s*[^;]+;/", "\$config['composer_autoload'] = TRUE;", $ciContent, 1, $count);
                        if ($count > 0) {
                            $ciContent = $newCiContent;
                            $updated = true;
                        }
                    } else {
                        // Insert after the opening defined('BASEPATH') line if present, otherwise after <?php
                        if (preg_match("/(defined\('\w+'\)\s*OR\s*exit\([^;]*;\r?\n)/", $ciContent, $m, PREG_OFFSET_CAPTURE)) {
                            $insertPos = $m[0][1] + strlen($m[0][0]);
                            $ciContent = substr_replace($ciContent, "\n\$config['composer_autoload'] = TRUE;\n", $insertPos, 0);
                            $updated = true;
                        } elseif (preg_match('/<\?php\r?\n/', $ciContent, $m2, PREG_OFFSET_CAPTURE)) {
                            $insertPos = $m2[0][1] + strlen($m2[0][0]);
                            $ciContent = substr_replace($ciContent, "\n\$config['composer_autoload'] = TRUE;\n", $insertPos, 0);
                            $updated = true;
                        }
                    }

                    if ($updated) {
                        // Backup original
                        @copy($ciConfigPath, $ciConfigPath . '.oidc.bak');
                        $res = file_put_contents($ciConfigPath, $ciContent);
                        if ($res === false) {
                            $this->io->writeError('OidcClient: failed to write updated CodeIgniter config.php');
                        } else {
                            $this->io->write('OidcClient: enabled composer_autoload in application/config/config.php (backup saved as config.php.oidc.bak)');
                        }
                    } else {
                        $this->io->write('OidcClient: composer_autoload already configured in CodeIgniter config or insertion point not found');
                    }
                }
            }
        }
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // no-op
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // no-op
    }
}
