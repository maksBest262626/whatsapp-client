<?php

declare(strict_types=1);

namespace Maksb\WhatsappClient\Client\Response;

interface ResponseInterface
{
    public function getData(): array;

    public function isError(): bool;

    public function isSuccess(): bool;
}
