<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\EndpointConfigService;
use App\Message\WebhookMessage;
use Nyholm\Psr7\Uri;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/webhook")
 */
class WebhookController extends AbstractController
{
    /**
     * @param Request $request
     * @param EndpointConfigService $endpointConfigService
     * @param MessageBusInterface $messageBus
     * @param string $slug
     * @return Response
     * @Route("/{slug}", name="webhook", requirements={"slug"=".+"})
     */
    public function handle(
        Request $request,
        EndpointConfigService $endpointConfigService,
        MessageBusInterface $messageBus,
        string $slug = ''
    ): Response
    {
        $payload = $request->getContent();
        foreach ($endpointConfigService->getEndpoints() as $endpoint) {
            if ($endpoint['preserve_path']) {
                $path = $endpoint['path'] . '/' . $slug;
            } else {
                $path = $endpoint['path'];
            }

            if ($endpoint['preserve_query']) {
                $query = $request->getQueryString();
            } else {
                $query = '';
            }

            $uri = new Uri();
            $uri = $uri->withScheme($endpoint['scheme'])
                ->withHost($endpoint['host'])
                ->withPort(array_key_exists('port', $endpoint) ? $endpoint['port'] : null)
                ->withPath($path)
                ->withQuery($query !== null ? $query : '' );

            $headers = [];
            if (array_key_exists('headers', $endpoint)) {
                foreach ($endpoint['headers'] as $key => $value) {
                    $headers[$key] = $value;
                }
            }

            $messageBus->dispatch(
                new WebhookMessage(
                    $uri,
                    $headers,
                    $endpoint['method'],
                    $payload
                )
            );
        }
        return new Response('kthxbye');
    }
}
