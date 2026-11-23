<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Module des commentaires des pages et de leur modération
 */

namespace App\Service\Admin\Module\Core;

use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Global\SvgIcon;
use App\Enum\Admin\Module\CoreModuleId;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Service\Admin\Module\AdminMenuBadgeInterface;
use App\Service\Admin\Module\AdminMenuItem;
use App\Service\Admin\Module\AdminModule;
use App\Service\Admin\Module\AdminModuleInterface;
use App\Service\Admin\Module\AsAdminModule;

use function Symfony\Component\Translation\t;

#[AsAdminModule]
final class CommentModule implements AdminModuleInterface, AdminMenuBadgeInterface
{
    /**
     * Identifiant du module
     * @var string
     */
    public const string ID = CoreModuleId::COMMENT->value;

    public function __construct(private readonly CommentRepository $commentRepository) {}

    /**
     * @return AdminModule
     */
    public function getDefinition(): AdminModule
    {
        return new AdminModule(
            id: self::ID,
            role: 'ROLE_CONTRIBUTEUR',
            label: t('admin_module.module.comment.label', domain: 'admin_module'),
            description: t('admin_module.module.comment.description', domain: 'admin_module'),
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
                label: t('admin_module.menu.comment.label', domain: 'admin_module'),
                icon: 'M9 17h6l3 3v-3h2V9h-2M4 4h11v8H9l-3 3v-3H4V4Z',
                parent: CoreMenu::CONTENT,
                position: 30,
            ),
            new AdminMenuItem(
                id: 'comment_index',
                label: t('admin_module.menu.comment_index.label', domain: 'admin_module'),
                icon: SvgIcon::LIST->value,
                parent: self::ID,
                position: 10,
                legacyRoute: 'admin_comment_index',
            ),
            new AdminMenuItem(
                id: 'comment_moderate',
                label: t('admin_module.menu.comment_moderate.label', domain: 'admin_module'),
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
