<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Module des commentaires des pages et de leur modération
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Comment\CommentStatus;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Service\Admin\Module\AdminMenuBadgeInterface;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

#[AsAdminModule]
final class CommentModule implements AdminModuleInterface, AdminMenuBadgeInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = 'comment';

    public function __construct(private readonly CommentRepository $commentRepository) {}

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: 'admin_module.module.comment.label',
            description: 'admin_module.module.comment.description',
            locked: true,
            routePrefixes: ['admin_comment_'],
        );
    }

    /**
     * @return AdminMenuItem[]
     */
    public function getMenuItems(): array
    {
        return [
            new AdminMenuItem(
                id: self::ID,
                label: 'admin_module.menu.comment.label',
                icon: 'M9 17h6l3 3v-3h2V9h-2M4 4h11v8H9l-3 3v-3H4V4Z',
                parent: CoreMenu::CONTENT,
                position: 30,
            ),
            new AdminMenuItem(
                id: 'comment_index',
                label: 'admin_module.menu.comment_index.label',
                icon: 'M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5',
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_comment_index',
            ),
            new AdminMenuItem(
                id: 'comment_moderate',
                label: 'admin_module.menu.comment_moderate.label',
                icon: 'M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                parent: self::ID,
                position: 20,
                legacyRoute: 'admin_comment_moderate_comments',
            ),
        ];
    }

    /**
     * Nombre de commentaires en attente de modération, sur le dossier et sur la modération
     * @return array<string, int>
     */
    public function getBadges(): array
    {
        $count = $this->commentRepository->count(['status' => CommentStatus::WAIT_VALIDATION->value]);
        return $count === 0 ? [] : [self::ID => $count, 'comment_moderate' => $count];
    }
}
