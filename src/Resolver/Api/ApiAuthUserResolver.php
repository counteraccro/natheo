<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */

namespace App\Resolver\Api;

use App\Dto\Api\Authentication\ApiAuthUserDto;
use App\Http\Api\ApiHttpException;
use App\Utils\Api\ApiParametersParser;
use App\Utils\Api\Parameters\ApiParametersUserAuthRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiAuthUserResolver extends AppApiResolver implements ValueResolverInterface
{
    /**
     * Permet de mapper ApiAuthUserDto avec Request
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        // Test pour éviter que ce résolver soit appeler pour autre chose que ApiAuthUserDto
        $argumentType = $argument->getType();
        if (!is_a($argumentType, ApiAuthUserDto::class, true)) {
            return [];
        }

        $data = $this->getJsonContent($request);

        /** @var ApiParametersParser $apiParametersParser */
        $apiParametersParser = $this->handlers->get('apiParametersParser');
        $return = $apiParametersParser->parse(ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER, $data);

        if (!empty($return)) {
            throw new ApiHttpException(Response::HTTP_BAD_REQUEST, $return);
        }

        $dto = new ApiAuthUserDto(
            $this->toStringParameter($data['username'], 'username'),
            $this->toStringParameter($data['password'], 'password'),
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
