<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageTagDto
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageTagDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Page\ApiParametersFindPageCategoryRef;
use App\Utils\Api\Parameters\Content\Page\ApiParametersFindPageTagRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageTagResolver extends AppApiResolver implements ValueResolverInterface
{
    /**
     * Permet de mapper ApiFindPageContent avec Request
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
        if (!is_a($argumentType, ApiFindPageTagDto::class, true)) {
            return [];
        }

        $tabParameters = ApiParametersFindPageTagRef::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->query->get($parameter, '');

            if (empty($value) && isset(ApiParametersFindPageTagRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersFindPageTagRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiParametersFindPageCategoryRef::PARAM_USER_TOKEN] = $this->getUserToken($request);

        $dto = new ApiFindPageTagDto(
            $tabParameters[ApiParametersFindPageTagRef::PARAM_TAG],
            $tabParameters[ApiParametersFindPageTagRef::PARAM_LOCALE],
            intval($tabParameters[ApiParametersFindPageTagRef::PARAM_PAGE]),
            intval($tabParameters[ApiParametersFindPageTagRef::PARAM_LIMIT]),
            $tabParameters[ApiParametersFindPageTagRef::PARAM_USER_TOKEN],
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
