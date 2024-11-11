<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */

namespace App\Resolver\Api;

use App\Dto\Api\Authentication\ApiAuthUserDto;
use App\Utils\Api\Parameters\ApiParametersUserAuthRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        $data = json_decode($request->getContent(), true);
        $apiParametersParser = $this->handlers->get('apiParametersParser');
        $return = $apiParametersParser->parse(ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER, $data);

        if (!empty($return)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, implode(',', $return));
        }

        $dto = new ApiAuthUserDto(
            $data['username'],
            $data['password']
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
