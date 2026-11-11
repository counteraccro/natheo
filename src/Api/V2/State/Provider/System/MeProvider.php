<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Provider de l'utilisateur de la session de la requête
 */

namespace App\Api\V2\State\Provider\System;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\ForbiddenException;
use App\Api\V2\Mapper\System\MeMapper;
use App\Api\V2\Resource\System\Me;
use App\Api\V2\Security\CurrentUserResolver;
use App\Service\Api\Content\ApiCommentService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProviderInterface<Me>
 */
final readonly class MeProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserResolver $currentUserResolver,
        private ApiCommentService $apiCommentService,
        private TranslatorInterface $translator,
        #[Autowire(param: 'app.path.avatar')] private string $avatarPath,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Me
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Me
    {
        $user =
            $this->currentUserResolver->getUser() ??
            throw new ForbiddenException($this->translator->trans('api_errors.session.required', domain: 'api_errors'));

        /** @var Request $request */
        $request = $context['request'];
        $avatar =
            $user->getAvatar() === null || $user->getAvatar() === ''
                ? null
                : $request->getBasePath() . '/' . trim($this->avatarPath, '/') . '/' . $user->getAvatar();

        return (new MeMapper())->map($user, $avatar, $this->apiCommentService->canModerate($user));
    }
}
