<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de résoudre la validation de l'objet ApiModerateComment
 */

namespace App\Resolver\Api\Content\Comment;

use App\Dto\Api\Content\Comment\ApiModerateCommentDto;
use App\Enum\Admin\Comment\CommentStatus;
use App\Resolver\Api\AppApiResolver;
use App\Enum\Api\Parameters\Content\Comment\ApiModerateCommentParameter;
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

        $content = $this->getJsonContent($request);

        $tabParameters = ApiModerateCommentParameter::PARAMS_REF;
        foreach ($tabParameters as $parameter => $value) {
            if (array_key_exists($parameter, $content)) {
                $value = $content[$parameter];
            } else {
                $value = '';
            }

            if (empty($value) && isset(ApiModerateCommentParameter::PARAMS_DEFAULT_VALUE[$parameter])) {
                $value = ApiModerateCommentParameter::PARAMS_DEFAULT_VALUE[$parameter];
            }
            $tabParameters[$parameter] = $value;
        }

        if (!is_null($request->headers->get('User-token'))) {
            $tabParameters[ApiModerateCommentParameter::USER_TOKEN->value] = $request->headers->get('User-token');
        }

        $this->checkParameters($tabParameters);

        $dto = new ApiModerateCommentDto(
            strval($tabParameters[ApiModerateCommentParameter::STATUS->value]),
            $this->toStringParameter(
                $tabParameters[ApiModerateCommentParameter::MODERATION_COMMENT->value],
                ApiModerateCommentParameter::MODERATION_COMMENT->value,
            ),
            $this->toStringParameter(
                $tabParameters[ApiModerateCommentParameter::USER_TOKEN->value],
                ApiModerateCommentParameter::USER_TOKEN->value,
            ),
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

        $tabStatus = [
            CommentStatus::MODERATE->value,
            CommentStatus::VALIDATE->value,
            CommentStatus::WAIT_VALIDATION->value,
        ];
        // Accepte le status en entier ou en chaîne numérique ("3")
        $status = $parameters[ApiModerateCommentParameter::STATUS->value];
        $status = is_int($status) || (is_string($status) && ctype_digit($status)) ? intval($status) : null;
        if (!in_array($status, $tabStatus, true)) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $translator->trans('api_errors.comment.status.no.valid', domain: 'api_errors'),
            );
        }
    }
}
