<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */

namespace App\Resolver\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Page\ApiParametersFindPageRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ApiFindPageResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindPageDto::class, true)) {
            return [];
        }

        $tabParameters = ApiParametersFindPageRef::PARAMS_REF;

        foreach ($tabParameters as $parameter => $value) {
            $value = $request->get($parameter, '');
            if ($parameter === ApiParametersFindPageRef::PARAM_MENU_POSITION && !empty($value)) {
                $tab = explode(',', $value);
                $value = [];
                foreach ($tab as $tmp) {
                    $value[intval($tmp)] = intval($tmp);
                }
            }
            if (empty($value) && isset(ApiParametersFindPageRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersFindPageRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $tabParameters[ApiParametersFindPageRef::PARAM_USER_TOKEN] = $this->getUserToken($request);

        $dto = new ApiFindPageDto(
            $tabParameters[ApiParametersFindPageRef::PARAM_SLUG],
            $tabParameters[ApiParametersFindPageRef::PARAM_LOCALE],
            $tabParameters[ApiParametersFindPageRef::PARAM_PAGE],
            $tabParameters[ApiParametersFindPageRef::PARAM_LIMIT],
            filter_var($tabParameters[ApiParametersFindPageRef::PARAM_SHOW_MENU], FILTER_VALIDATE_BOOLEAN),
            $tabParameters[ApiParametersFindPageRef::PARAM_MENU_POSITION],
            $tabParameters[ApiParametersFindPageRef::PARAM_USER_TOKEN]
        );

        $this->validateDto($dto);
        return [$dto];
    }
}
