<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * EventListener qui va intercepter toute exception
 */
namespace App\EventListener;

use App\Service\Installation\ParseEnvService;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class ExceptionListener
{
    public function __construct(#[AutowireLocator([
        'router' => RouterInterface::class,
        'twig' => Environment::class,
        'parseEnv' => ParseEnvService::class,
    ])] protected ContainerInterface $handlers){}

    /**
     * @param ExceptionEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(ExceptionEvent $event): void
    {

        /** @var Environment $twig */
        $twig = $this->handlers->get('twig');
        /** @var ParseEnvService $parseEnv */
        $parseEnv = $this->handlers->get('parseEnv');

        $exception = $event->getThrowable();
        $response = new Response();

        if ($exception instanceof ConnectionException) {

            $parseResult = $parseEnv->parseEnvFile();
            $msg = $twig->render('installation/exceptions/ConnectionException.twig',
                ['file' => $parseResult['file'], 'envPath'=> $parseEnv->getPathEnvFile(), 'errors' => $parseResult['errors']]
            );
            $response->setContent($msg);
            $event->setResponse($response);

        } elseif ($exception instanceof TableNotFoundException) {
            echo 'Pas de tables';
            echo __FILE__;
            die('A modifier');
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
