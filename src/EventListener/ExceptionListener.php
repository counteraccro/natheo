<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * EventListener qui va intercepter toute exception
 */

namespace App\EventListener;

use App\Http\Api\ApiResponse;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionListener
{
    public function __construct(#[AutowireLocator([
        'translator' => TranslatorInterface::class,
    ])] protected ContainerInterface $handlers)
    {
    }

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

        if (in_array('application/json', $request->getAcceptableContentTypes()) || 'json' === $request->getContentTypeFormat() || str_contains($request->getUri(), '/api/')) {
            $response = $this->createApiResponse($exception);
            $event->setResponse($response);
        }
    }

    /**
     * Créer l'API réponse
     * @param Exception $exception
     * @return ApiResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function createApiResponse(\Throwable $exception): ApiResponse
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->handlers->get('translator');
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $errors = [];

        $message = $exception->getMessage();
        if ($exception instanceof AccessDeniedHttpException) {
            // Erreur 403
            $message = $translator->trans('api_errors.access.denied', domain: 'api_errors');
        }

        if ($exception instanceof NotFoundHttpException) {
            // Erreur 404
            $message = $translator->trans('api_errors.not.found', domain: 'api_errors');
        }

        if ($exception instanceof HttpException) {
            $message = match ($exception->getStatusCode()) {
                Response::HTTP_UNAUTHORIZED => $translator->trans('api_errors.access.unauthorized', domain: 'api_errors'),
                Response::HTTP_FORBIDDEN => $translator->trans('api_errors.access.denied', domain: 'api_errors'),
                Response::HTTP_NOT_FOUND => $translator->trans('api_errors.not.found', domain: 'api_errors'),
                Response::HTTP_INTERNAL_SERVER_ERROR => $translator->trans('api_errors.internal.server.error', domain: 'api_errors'),
                default => 'Code HTTP non pris en compte '. __FILE__ . ':' .  __LINE__,
            };
            $errors = explode(',', $exception->getMessage());
        }
        return new ApiResponse($message, null, $errors, $statusCode);
    }
}
