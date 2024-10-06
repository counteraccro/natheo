<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */
namespace App\Resolver\Api;

use App\Dto\Api\Authentication\ApiAuthUserDto;
use App\Utils\Api\ApiParametersParser;
use App\Utils\Api\ApiParametersRef;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ApiAuthUserResolver implements ValueResolverInterface
{
    public function __construct(
        private ApiParametersParser $apiParametersParser,
        private ValidatorInterface  $validator
    ) {
    }

    /**
     * Permet de mapper ApiAuthUserDto avec Request
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = json_decode($request->getContent(), true);
        $return = $this->apiParametersParser->parse(ApiParametersRef::PARAMS_REF_AUTH_USER, $data);

        if (!empty($return)) {
            throw new HttpException(Response::HTTP_FORBIDDEN,implode(', ', $return));
        }

        $dto = new ApiAuthUserDto(
            $data['username'],
            $data['password']
        );

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $nb = $errors->count();
            $msg = [];
            for ($i = 0; $i < $nb; $i++) {
                $msg[] = $errors->get($i)->getMessage() . ' ';
            }
            throw new HttpException(Response::HTTP_FORBIDDEN,implode(', ', $msg));
        }
        return [$dto];
    }
}
