<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiCommentByPage
 */

namespace App\Resolver\Api\Content\Comment;

use App\Dto\Api\Content\Comment\ApiCommentByPageDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Comment\ApiParametersCommentByPageRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiCommentByPageResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiCommentByPageDto::class, true)) {
            return [];
        }

        $tabParameters = ApiParametersCommentByPageRef::PARAMS_REF;
        foreach ($tabParameters as $parameter => $value) {
            $value = $request->get($parameter, '');
            if (empty($value) && isset(ApiParametersCommentByPageRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersCommentByPageRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $this->checkParameters($tabParameters);

        $tabParameters[ApiParametersCommentByPageRef::PARAM_USER_TOKEN] = $this->getUserToken($request);

        $dto = new ApiCommentByPageDto(
            intval($tabParameters[ApiParametersCommentByPageRef::PARAM_ID]),
            $tabParameters[ApiParametersCommentByPageRef::PARAM_PAGE_SLUG],
            $tabParameters[ApiParametersCommentByPageRef::PARAM_LOCALE],
            intval($tabParameters[ApiParametersCommentByPageRef::PARAM_PAGE]),
            intval($tabParameters[ApiParametersCommentByPageRef::PARAM_LIMIT]),
            $tabParameters[ApiParametersCommentByPageRef::PARAM_ORDER_BY],
            $tabParameters[ApiParametersCommentByPageRef::PARAM_ORDER],
            $tabParameters[ApiParametersCommentByPageRef::PARAM_USER_TOKEN]
        );

       $this->validateDto($dto);
        return [$dto];
    }

    /**
     * @param array $parameters
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkParameters(array $parameters): void
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->handlers->get('translator');

        // Cas id et page_slug sont vides (n'existe pas dans la query)
        if (empty($parameters[ApiParametersCommentByPageRef::PARAM_ID])
            && empty($parameters[ApiParametersCommentByPageRef::PARAM_PAGE_SLUG])) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.comment.by.page.not.id.slug.together', domain: 'api_errors'));
        }

        // Cas id et page_slug ensemble
        if (!empty($parameters[ApiParametersCommentByPageRef::PARAM_ID])
            && !empty($parameters[ApiParametersCommentByPageRef::PARAM_PAGE_SLUG])) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $translator->trans('api_errors.comment.by.page.id.slug.together', domain: 'api_errors'));
        }
    }
}
