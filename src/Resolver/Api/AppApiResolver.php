<?php

namespace App\Resolver\Api;

use App\Utils\Api\ApiParametersParser;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppApiResolver
{
    /**
     * @param ContainerInterface $handlers
     */
    public function __construct(
        #[
            AutowireLocator([
                'apiParametersParser' => ApiParametersParser::class,
                'validator' => ValidatorInterface::class,
                'translator' => TranslatorInterface::class,
            ]),
        ]
        protected ContainerInterface $handlers,
    ) {}

    /**
     * Valide un objet de type Dto
     * @param mixed $dto
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function validateDto(mixed $dto): void
    {
        $validator = $this->handlers->get('validator');
        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            $nb = $errors->count();
            $msg = [];
            for ($i = 0; $i < $nb; $i++) {
                $msg[] = $errors->get($i)->getMessage() . ' ';
            }
            throw new HttpException(Response::HTTP_FORBIDDEN, implode(',', $msg));
        }
    }

    /**
     * Retourne le user token si celui ci existe
     * @param Request $request
     * @return string
     */
    protected function getUserToken(Request $request): string
    {
        if ($request->headers->has('User-token')) {
            return $request->headers->get('User-token');
        }
        return '';
    }
}
