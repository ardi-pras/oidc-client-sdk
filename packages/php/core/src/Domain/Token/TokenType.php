<?php

declare(strict_types=1);

namespace OidcClient\Domain\Token;

enum TokenType: string
{
    case Bearer = 'Bearer';
}