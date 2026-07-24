<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Contracts\Repository\TokenRepositoryInterface;
use OidcClient\Contracts\Repository\UserRepositoryInterface;
use OidcClient\Domain\Authentication\AuthenticationResult;
use OidcClient\Domain\Authentication\AuthorizationResponse;

final class CallbackService
{
    private $config;

    private $storage;

    private $tokenRepository;

    private $userRepository;

    public function __construct(
        OidcConfiguration $config,
        AuthorizationContextStorage $storage,
        TokenRepositoryInterface $tokenRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->config = $config;
        $this->storage = $storage;
        $this->tokenRepository = $tokenRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(
        AuthorizationResponse $response
    ): AuthenticationResult {

        /*
         * STEP 1
         */

        $context = $this->storage->load();

        if ($context === null) {
            return AuthenticationResult::failed(
                'Authorization session not found.'
            );
        }

        /*
         * STEP 2
         */

        if ($context->isExpired()) {

            return AuthenticationResult::failed(
                'Authorization session expired.'
            );
        }

        /*
         * STEP 3
         */

        if (
            !hash_equals(
                $context->state(),
                $response->state()
            )
        ) {

            return AuthenticationResult::failed(
                'Invalid state.'
            );
        }

        /*
         * STEP 4
         */

        $token = $this
            ->tokenRepository
            ->exchangeAuthorizationCode(
                $response,
                $context
            );

        /*
         * STEP 5
         */

        $user = $this
            ->userRepository
            ->userInfo(
                $token
            );

        /*
         * STEP 6
         */

        $this->storage->clear();

        return AuthenticationResult::success(
            $user,
            $token
        );
    }
}
