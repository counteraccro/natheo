<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageCategoryDto
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageCategoryDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Page\ApiParametersFindPageCategoryRef;
use App\Utils\Api\Parameters\Content\Page\ApiParametersFindPageContentRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageCategoryResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindPageCategoryDto::class, true)) {
            return [];
        }

        $tabParameters = ApiParametersFindPageCategoryRef::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->query->get($parameter, '');

            if (empty($value) && isset(ApiParametersFindPageCategoryRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersFindPageCategoryRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiParametersFindPageCategoryRef::PARAM_USER_TOKEN] = $this->getUserToken($request);

        $dto = new ApiFindPageCategoryDto(
            $tabParameters[ApiParametersFindPageCategoryRef::PARAM_CATEGORY],
            $tabParameters[ApiParametersFindPageCategoryRef::PARAM_LOCALE],
            intval($tabParameters[ApiParametersFindPageCategoryRef::PARAM_PAGE]),
            intval($tabParameters[ApiParametersFindPageCategoryRef::PARAM_LIMIT]),
            $tabParameters[ApiParametersFindPageCategoryRef::PARAM_USER_TOKEN],
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
