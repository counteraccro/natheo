<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiAddComment
 */

namespace App\Resolver\Api\Content\Comment;

use App\Dto\Api\Content\Comment\ApiAddCommentDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Comment\ApiParametersAddCommentRef;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiAddCommentResolver extends AppApiResolver implements ValueResolverInterface
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
        if (!is_a($argumentType, ApiAddCommentDto::class, true)) {
            return [];
        }

        $content = json_decode($request->getContent(), true);

        $tabParameters = ApiParametersAddCommentRef::PARAMS_REF;
        foreach ($tabParameters as $parameter => $value) {
            if (array_key_exists($parameter, $content)) {
                $value = $content[$parameter];
            } else {
                $value = '';
            }

            if (empty($value) && isset(ApiParametersAddCommentRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersAddCommentRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        $this->checkParameters($tabParameters);

        $dto = new ApiAddCommentDto(
            intval($tabParameters[ApiParametersAddCommentRef::PARAM_ID]),
            $tabParameters[ApiParametersAddCommentRef::PARAM_PAGE_SLUG],
            $tabParameters[ApiParametersAddCommentRef::PARAM_LOCALE],
            $tabParameters[ApiParametersAddCommentRef::PARAM_AUTHOR],
            $tabParameters[ApiParametersAddCommentRef::PARAM_EMAIL],
            $tabParameters[ApiParametersAddCommentRef::PARAM_COMMENT],
            $tabParameters[ApiParametersAddCommentRef::PARAM_IP],
            $tabParameters[ApiParametersAddCommentRef::PARAM_USER_AGENT],
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
        if (
            empty($parameters[ApiParametersAddCommentRef::PARAM_ID]) &&
            empty($parameters[ApiParametersAddCommentRef::PARAM_PAGE_SLUG])
        ) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans('api_errors.comment.add.not.id.slug.together', domain: 'api_errors'),
            );
        }

        // Cas id et page_slug ensemble
        if (
            !empty($parameters[ApiParametersAddCommentRef::PARAM_ID]) &&
            !empty($parameters[ApiParametersAddCommentRef::PARAM_PAGE_SLUG])
        ) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans('api_errors.comment.add.id.slug.together', domain: 'api_errors'),
            );
        }
    }
}
