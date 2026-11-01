<?php

declare(strict_types=1);
/**
 * Voter des droits de modification, désactivation et suppression des contenus
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Security\Voter;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Service\Admin\System\OptionSystemService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Droits de modification, désactivation et suppression des contenus (Page, Faq, Menu, Tag)
 * Un contributeur gère ses propres contenus, un admin gère tous les contenus
 */
class ContentVoter extends Voter
{
    public const string EDIT = 'CONTENT_EDIT';

    public const string DISABLE = 'CONTENT_DISABLE';

    public const string DELETE = 'CONTENT_DELETE';

    private const array ATTRIBUTES = [self::EDIT, self::DISABLE, self::DELETE];

    private const array SUBJECTS = [Page::class, Faq::class, Menu::class, Tag::class];

    /**
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param OptionSystemService $optionSystemService
     */
    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly OptionSystemService $optionSystemService,
    ) {}

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::ATTRIBUTES, true)) {
            return false;
        }

        // instanceof plutôt que ::class pour accepter les proxies Doctrine
        foreach (self::SUBJECTS as $class) {
            if ($subject instanceof $class) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ordre des vérifications : option de suppression (DELETE), rôle admin, puis propriétaire
     * @param string $attribute
     * @param Page|Faq|Menu|Tag $subject
     * @param TokenInterface $token
     * @param Vote|null $vote
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::DELETE && !$this->optionSystemService->canDelete()) {
            $vote?->addReason('La suppression des données est désactivée.');
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        // Un contenu sans propriétaire n'est géré que par un admin
        $owner = $subject->getUser();
        return $owner !== null && $owner->getId() === $user->getId();
    }
}
