<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\WebhookMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class WebhookMessageHandler
{
    public function __construct(
        private HttpClientInterface $client
    )
    {
    }

    public function __invoke(WebhookMessage $message): void
    {
        $this->client->request(
            $message->getMethod(),
            (string)$message->getUri(),
            [
                'headers' => $message->getHeaders(),
                'body' => $message->getPayload()
            ]
        );
    }
}
