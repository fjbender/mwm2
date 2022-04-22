<?php declare(strict_types=1);

namespace App\Message;

use Nyholm\Psr7\Uri;

class WebhookMessage
{
    public function __construct(
        private Uri $uri,
        private array $headers,
        private string $method,
        private string $payload,
    )
    {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getUri(): Uri
    {
        return $this->uri;
    }
}
