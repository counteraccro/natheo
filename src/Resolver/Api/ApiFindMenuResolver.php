<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */

namespace App\Resolver\Api;

use App\Dto\Api\Menu\ApiFindMenuDto;
use App\Utils\Api\ApiParametersParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiFindMenuResolver extends AppApiResolver implements ValueResolverInterface
{
    public function __construct(
        private ApiParametersParser $apiParametersParser,
        private ValidatorInterface  $validator
    )
    {
    }

    /**
     * Permet de mapper ApiAuthUserDto avec Request
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {

        // Test pour éviter que ce résolver soit appeler pour autre chose que ApiAuthUserDto
        $argumentType = $argument->getType();
        if (!is_a($argumentType, ApiFindMenuDto::class, true)) {
            return [];
        }

        var_dump($request->get('id', 'pas ici'));

        $dto = new ApiFindMenuDto(
            0, 'page-Slug', 1, 'fr'
        );

        /*$data = json_decode($request->getContent(), true);
        $return = $this->apiParametersParser->parse(ApiParametersUserAuthRef::PARAMS_REF_AUTH_USER, $data);

        if (!empty($return)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, implode(',', $return));
        }

        $dto = new ApiAuthUserDto(
            $data['username'],
            $data['password']
        );*/


        return [$dto];
    }
}
