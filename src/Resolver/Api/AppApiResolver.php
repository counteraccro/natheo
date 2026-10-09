<?php

declare(strict_types=1);

namespace App\Resolver\Api;

use App\Http\Api\ApiHttpException;
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
            $msg = [];
            foreach ($errors as $error) {
                $msg[] = $error->getMessage();
            }
            throw new ApiHttpException(Response::HTTP_BAD_REQUEST, $msg);
        }
    }

    /**
     * Retourne le corps JSON de la requête sous forme de tableau, HttpException 400 si le JSON est invalide
     * @param Request $request
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getJsonContent(Request $request): array
    {
        $content = json_decode($request->getContent(), true);
        if (!is_array($content)) {
            $translator = $this->handlers->get('translator');
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $translator->trans('api_errors.request.body.invalid', domain: 'api_errors'),
            );
        }
        return $content;
    }

    /**
     * Convertit un paramètre du corps JSON en chaîne, HttpException 400 si ce n'est pas une valeur scalaire
     * @param mixed $value
     * @param string $name
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function toStringParameter(mixed $value, string $name): string
    {
        if ($value !== null && !is_scalar($value)) {
            $translator = $this->handlers->get('translator');
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $translator->trans('api_errors.params.not.string', ['param' => $name], domain: 'api_errors'),
            );
        }
        return strval($value);
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
