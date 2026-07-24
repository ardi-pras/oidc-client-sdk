<?php

declare(strict_types=1);

namespace Tests\Presentation;

use OidcClient\Presentation\Ui;
use OidcClient\Domain\User\User;
use OidcClient\Domain\User\Claims;
use OidcClient\Domain\Token\Token;
use OidcClient\Domain\Token\TokenType;
use PHPUnit\Framework\TestCase;

final class UiTest extends TestCase
{
    private User $user;
    private Token $token;

    protected function setUp(): void
    {
        $claims = new Claims([
            'sub' => '1234567890',
            'name' => 'John Doe',
            'email' => 'john.doe@company.com',
            'preferred_username' => 'johndoe',
            'roles' => ['admin', 'developer']
        ]);
        $this->user = new User($claims);

        $this->token = new Token(
            'sample-access-token',
            'sample-refresh-token',
            'sample-id-token',
            time() + 3600,
            TokenType::Bearer,
            'openid profile email'
        );
    }

    public function testLoginButtonHtml(): void
    {
        $html = Ui::loginButton('https://sso.company.com/login');

        $this->assertStringContainsString('Sign in with SSO', $html);
        $this->assertStringContainsString('https://sso.company.com/login', $html);
    }

    public function testUserProfileCardHtml(): void
    {
        $html = Ui::userProfileCard($this->user, 'https://sso.company.com/logout');

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('john.doe@company.com', $html);
        $this->assertStringContainsString('admin', $html);
        $this->assertStringContainsString('developer', $html);
        $this->assertStringContainsString('JD', $html); // Initials
        $this->assertStringContainsString('https://sso.company.com/logout', $html);
    }

    public function testDebugDashboardHtml(): void
    {
        $html = Ui::debugDashboard($this->user, $this->token);

        $this->assertStringContainsString('OIDC SSO Developer Dashboard', $html);
        $this->assertStringContainsString('sample-access-token', $html);
        $this->assertStringContainsString('sample-id-token', $html);
        $this->assertStringContainsString('sample-refresh-token', $html);
        $this->assertStringContainsString('1234567890', $html); // sub
        $this->assertStringContainsString('Bearer', $html);
    }
}
