<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * EventListener qui va intercepter toute exception
 */

namespace App\EventListener;

use App\Http\Api\ApiHttpException;
use App\Http\Api\ApiResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionListener
{
    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
                'logger' => LoggerInterface::class,
            ]),
        ]
        protected ContainerInterface $handlers,
        #[Autowire('%kernel.debug%')] private readonly bool $debug = false,
    ) {}

    /**
     * @param ExceptionEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (
            in_array('application/json', $request->getAcceptableContentTypes()) ||
            'json' === $request->getContentTypeFormat() ||
            str_contains($request->getUri(), '/api/')
        ) {
            $response = $this->createApiResponse($exception);
            $event->setResponse($response);
        }
    }

    /**
     * Créer l'API réponse.
     * Hors mode debug, le message d'une exception non HTTP n'est jamais renvoyé au client
     * @param \Throwable $exception
     * @return ApiResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createApiResponse(\Throwable $exception): ApiResponse
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->handlers->get('translator');

        if (!($exception instanceof HttpExceptionInterface)) {
            $this->handlers->get('logger')->error($exception->getMessage(), ['exception' => $exception]);

            $errors = $this->debug ? [$exception->getMessage()] : [];
            return new ApiResponse(
                $translator->trans('api_errors.internal.server.error', domain: 'api_errors'),
                null,
                $errors,
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        $statusCode = $exception->getStatusCode();
        $message = match ($statusCode) {
            Response::HTTP_BAD_REQUEST => $translator->trans('api_errors.bad.request', domain: 'api_errors'),
            Response::HTTP_UNAUTHORIZED => $translator->trans('api_errors.access.unauthorized', domain: 'api_errors'),
            Response::HTTP_FORBIDDEN => $translator->trans('api_errors.access.denied', domain: 'api_errors'),
            Response::HTTP_NOT_FOUND => $translator->trans('api_errors.not.found', domain: 'api_errors'),
            Response::HTTP_METHOD_NOT_ALLOWED => $translator->trans(
                'api_errors.method.not.allowed',
                domain: 'api_errors',
            ),
            Response::HTTP_TOO_MANY_REQUESTS => $translator->trans(
                'api_errors.too.many.requests',
                domain: 'api_errors',
            ),
            Response::HTTP_INTERNAL_SERVER_ERROR => $translator->trans(
                'api_errors.internal.server.error',
                domain: 'api_errors',
            ),
            default => Response::$statusTexts[$statusCode] ?? 'HTTP error',
        };

        if ($statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->handlers->get('logger')->error($exception->getMessage(), ['exception' => $exception]);
        }

        return new ApiResponse($message, null, $this->getErrors($exception), $statusCode, $exception->getHeaders());
    }

    /**
     * Retourne la liste des erreurs à renvoyer pour une exception HTTP
     * @param HttpExceptionInterface $exception
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getErrors(HttpExceptionInterface $exception): array
    {
        if ($exception instanceof ApiHttpException) {
            return $exception->getErrors();
        }

        // Message anglais du composant Security (#[IsGranted]) remplacé par un message traduit
        if ($exception->getPrevious() instanceof AccessDeniedException) {
            return [$this->handlers->get('translator')->trans('api_errors.access.role.denied', domain: 'api_errors')];
        }

        return [$exception->getMessage()];
    }
}
