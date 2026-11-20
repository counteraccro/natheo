<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Droits de l'utilisateur de la requête sur un élément d'administration
 */

namespace App\Api\V2\Security;

use App\Api\V2\Output\Admin\Permissions;
use App\Security\Voter\ContentVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class AdminPermissionResolver
{
    public function __construct(private AuthorizationCheckerInterface $authorizationChecker) {}

    /**
     * Droits sur un contenu (Page, Faq, Menu, Tag), votés par ContentVoter
     * @param object $content
     * @return Permissions
     */
    public function forContent(object $content): Permissions
    {
        return new Permissions(
            edit: $this->authorizationChecker->isGranted(ContentVoter::EDIT, $content),
            disable: $this->authorizationChecker->isGranted(ContentVoter::DISABLE, $content),
            delete: $this->authorizationChecker->isGranted(ContentVoter::DELETE, $content),
        );
    }
}
