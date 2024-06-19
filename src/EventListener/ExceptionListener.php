<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * EventListener qui va intercepter toute exception
 */
namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        // TODO A REFAIRE PROPREMENT

        // You get the exception object from the received event
        $exception = $event->getThrowable();
        /*$message = sprintf(
            'My Error says: %s with code: %s',
            $exception->getMessage(),
            $exception->getCode()
        );*/

        // Customize your response object to display the exception details
        //$response = new Response();
        //$response->setContent($message);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details

        if ($exception instanceof ConnectionException) {
            echo 'Pas de base de données';
            echo __FILE__;
            die('A modifier');
        } else if ($exception instanceof TableNotFoundException) {
            echo 'Pas de tables';
            echo __FILE__;
            die('A modifier');
        } else {
            //$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // sends the modified response object to the event
        //$event->setResponse($response);
    }
}
