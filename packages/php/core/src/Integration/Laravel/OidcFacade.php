<?php

declare(strict_types=1);

namespace OidcClient\Integration\Laravel;

use Illuminate\Support\Facades\Facade;
use OidcClient\OidcClient;

/**
 * @method static never login()
 * @method static \OidcClient\Domain\Authentication\AuthenticationResult authenticate(array $query)
 * @method static \OidcClient\Application\Authentication\AuthenticationService authentication()
 * @method static \OidcClient\Domain\User\User|null user()
 * @method static \OidcClient\Domain\Token\Token|null token()
 * @method static bool isAuthenticated()
 * @method static void logout()
 * @method static \OidcClient\Config\OidcConfiguration configuration()
 * 
 * @see \OidcClient\OidcClient
 */
final class OidcFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OidcClient::class;
    }
}
