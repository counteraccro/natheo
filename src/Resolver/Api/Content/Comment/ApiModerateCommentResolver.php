<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiModerateComment
 */

namespace App\Resolver\Api\Content\Comment;

use App\Dto\Api\Content\Comment\ApiModerateCommentDto;
use App\Resolver\Api\AppApiResolver;
use App\Utils\Api\Parameters\Content\Comment\ApiParametersModerateCommentRef;
use App\Utils\Content\Comment\CommentConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiModerateCommentResolver extends AppApiResolver implements ValueResolverInterface
{
    /**
     * Permet de mapper ApiModerateCommentDto avec Request
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
        if (!is_a($argumentType, ApiModerateCommentDto::class, true)) {
            return [];
        }

        $content = json_decode($request->getContent(), true);

        $tabParameters = ApiParametersModerateCommentRef::PARAMS_REF;
        foreach ($tabParameters as $parameter => $value) {
            if (array_key_exists($parameter, $content)) {
                $value = $content[$parameter];
            } else {
                $value = '';
            }

            if (empty($value) && isset(ApiParametersModerateCommentRef::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiParametersModerateCommentRef::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        if (!is_null($request->headers->get('User-token'))) {
            $tabParameters[ApiParametersModerateCommentRef::PARAM_USER_TOKEN] = $request->headers->get('User-token');
        }

        $this->checkParameters($tabParameters);

        $dto = new ApiModerateCommentDto(
            $tabParameters[ApiParametersModerateCommentRef::PARAM_STATUS],
            $tabParameters[ApiParametersModerateCommentRef::PARAM_MODERATION_COMMENT],
            $tabParameters[ApiParametersModerateCommentRef::PARAM_USER_TOKEN],
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

        $tabStatus = [CommentConst::MODERATE, CommentConst::VALIDATE, CommentConst::WAIT_VALIDATION];
        if (!in_array($parameters[ApiParametersModerateCommentRef::PARAM_STATUS], $tabStatus, true)) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $translator->trans('api_errors.comment.status.no.valid', domain: 'api_errors'),
            );
        }
    }
}
