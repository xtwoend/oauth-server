<?php

namespace OAuthServer;

interface OneTimePasswordInterface
{
    public function verify(string $phone, string $code): bool;
}