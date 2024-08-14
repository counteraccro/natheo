<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * EventListener qui va intercepter toute exception
 */
namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class ExceptionListener
{
    public function __construct(#[AutowireLocator([
        'router' => RouterInterface::class,
        'twig' => Environment::class
    ])] protected ContainerInterface $handlers){}

    /**
     * @param ExceptionEvent $event
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ExceptionEvent $event): void
    {

        /** @var Environment $twig */
        $twig = $this->handlers->get('twig');

        // You get the exception object from the received event
        $exception = $event->getThrowable();
        /*$message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );*/

        $msg = $twig->render('installation/exceptions/ConnectionException.twig');

        $response = new Response();
        $response->setContent($msg);

        // Customize your response object to display the exception details
        //$response = new Response();
        //$response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details

        if ($exception instanceof ConnectionException) {
            /*$twig->render('install/install_bdd/index.html.twig');
            echo 'Pas de base de données';
            echo __FILE__;
            die('A modifier');*/

            $response->setStatusCode($exception->getCode());
           //$response->headers->replace($exception->get());



            //$event->setResponse(new RedirectResponse($router->generate('app_install_install_bdd')));

            /*$event->setController(function () use ($router) {
                return new RedirectResponse($router->generate('app_install_install_bdd'));
            });*/

        } else if ($exception instanceof TableNotFoundException) {
            echo 'Pas de tables';
            echo __FILE__;
            die('A modifier');
        } else {
            //$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
