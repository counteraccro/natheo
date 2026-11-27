<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Nombre de commentaires par statut de modération, lus en une requête
 */

namespace App\Api\V2\State\Provider\Admin\Content;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\V2\Exception\NotFoundException;
use App\Api\V2\Resource\Admin\Content\AdminCommentStatusCount;
use App\Service\Api\Admin\Content\ApiAdminCommentService;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class AdminCommentStatusCountProvider implements ProviderInterface
{
    public function __construct(
        private ApiAdminCommentService $commentService,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return AdminCommentStatusCount[]|AdminCommentStatusCount
     * @throws NotFoundException statut inconnu
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): array|AdminCommentStatusCount {
        $counts = $this->commentService->countByStatus();
        if ($operation instanceof CollectionOperationInterface) {
            return array_map(
                fn(string $status) => new AdminCommentStatusCount($status, $counts[$status]),
                array_keys($counts),
            );
        }

        $status = (string) ($uriVariables['status'] ?? '');
        if (!isset($counts[$status])) {
            throw new NotFoundException($this->translator->trans('comment.error.status_invalid', domain: 'comment'));
        }
        return new AdminCommentStatusCount($status, $counts[$status]);
    }
}
