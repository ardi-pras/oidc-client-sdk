<?php

declare(strict_types=1);

namespace OidcClient\Support;

interface Clock
{
    public function now(): int;
}
