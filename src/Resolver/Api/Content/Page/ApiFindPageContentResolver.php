<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiFindPageContent
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Page\ApiParametersFindPageContentRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageContentResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindPageContentDto::class, true)) {
            return [];
        }

        $tabParameters = ApiParametersFindPageContentRef::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->get($parameter, '');

            if (empty($value) && isset(ApiParametersFindPageContentRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersFindPageContentRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiParametersFindPageContentRef::PARAM_USER_TOKEN] = $this->getUserToken($request);

        $dto = new ApiFindPageContentDto(
            intval($tabParameters[ApiParametersFindPageContentRef::PARAM_ID]),
            $tabParameters[ApiParametersFindPageContentRef::PARAM_LOCALE],
            intval($tabParameters[ApiParametersFindPageContentRef::PARAM_PAGE]),
            intval($tabParameters[ApiParametersFindPageContentRef::PARAM_LIMIT]),
            $tabParameters[ApiParametersFindPageContentRef::PARAM_USER_TOKEN]
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
