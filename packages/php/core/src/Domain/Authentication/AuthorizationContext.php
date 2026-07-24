<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use OidcClient\Domain\PKCE\PKCEPair;
use OidcClient\Contracts\Storage\SessionStorageInterface;
use RuntimeException;


final class AuthorizationContext
{
    private $pkce;

    private $state;

    private $nonce;

    private $createdAt;

    private $clientId;

    private $redirectUri;

    private $tokenEndpoint;

    private $clientSecret;

    public function __construct(
        PKCEPair $pkce,
        string $state,
        string $nonce,
        int $createdAt,
        string $clientId,
        string $redirectUri,
        string $tokenEndpoint,
        ?string $clientSecret = null
    ) {
        $this->pkce = $pkce;
        $this->state = $state;
        $this->nonce = $nonce;
        $this->createdAt = $createdAt;
        $this->clientId = $clientId;
        $this->redirectUri = $redirectUri;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->clientSecret = $clientSecret;
    }

    public static function fromSession(
        SessionStorageInterface $session
    ): self {

        $data = $session->get(
            'oidc.authorization_context'
        );


        if (!$data) {

            throw new RuntimeException(
                'OIDC authorization context not found.'
            );

        }


        return new self(

            $data['pkce'],

            $data['state'],

            $data['nonce'],

            $data['created_at'],

            $data['client_id'],

            $data['redirect_uri'],

            $data['token_endpoint'],

            $data['client_secret'] ?? null

        );

    }

    public function pkce(): PKCEPair
    {
        return $this->pkce;
    }

    public function state(): string
    {
        return $this->state;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function createdAt(): int
    {
        return $this->createdAt;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    public function tokenEndpoint(): string
    {
        return $this->tokenEndpoint;
    }

    /**
     * PKCE verifier asli
     */
    public function codeVerifier(): string
    {
        return $this->pkce->verifier();
    }

    /**
     * PKCE challenge untuk authorization request
     */
    public function codeChallenge(): string
    {
        return $this->pkce->challenge();
    }

    public function isExpired(
        int $ttl = 600
    ): bool {

        return (
            $this->createdAt + $ttl
        ) < time();

    }

    public function validateState(
        string $state
    ): bool {
        return hash_equals($this->state, $state);
    }

}
