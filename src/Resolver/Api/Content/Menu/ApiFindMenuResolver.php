<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAuthUserDto
 */

namespace App\Resolver\Api\Content\Menu;

use App\Dto\Api\Content\Menu\ApiFindMenuDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Menu\ApiParametersFindMenuRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiFindMenuResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiFindMenuDto::class, true)) {
            return [];
        }

        $tabParameters = ApiParametersFindMenuRef::PARAMS_REF;
        foreach ($tabParameters as $parameter => $value) {
            $value = $request->get($parameter, '');
            if (empty($value) && isset(ApiParametersFindMenuRef::PARAMS_REF_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersFindMenuRef::PARAMS_REF_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $this->checkParameters($tabParameters);

        $tabParameters[ApiParametersFindMenuRef::PARAM_USER_TOKEN] = $this->getUserToken($request);

        $dto = new ApiFindMenuDto(
            intval($tabParameters[ApiParametersFindMenuRef::PARAM_ID]),
            $tabParameters[ApiParametersFindMenuRef::PARAM_PAGE_SLUG],
            $tabParameters[ApiParametersFindMenuRef::PARAM_POSITION],
            $tabParameters[ApiParametersFindMenuRef::PARAM_LOCALE],
            $tabParameters[ApiParametersFindMenuRef::PARAM_USER_TOKEN]
        );

       $this->validateDto($dto);
        return [$dto];
    }

    private function checkParameters(array $parameters): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->handlers->get('translator');

        // Cas id et page_slug sont vides (n'existe pas dans la query)
        if (empty($parameters[ApiParametersFindMenuRef::PARAM_ID])
            && empty($parameters[ApiParametersFindMenuRef::PARAM_PAGE_SLUG])) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.find.menu.not.id.slug.together', domain: 'api_errors'));
        }

        // Cas id et page_slug ensemble
        if (!empty($parameters[ApiParametersFindMenuRef::PARAM_ID])
            && !empty($parameters[ApiParametersFindMenuRef::PARAM_PAGE_SLUG])) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.find.menu.id.slug.together', domain: 'api_errors'));
        }
    }
}
