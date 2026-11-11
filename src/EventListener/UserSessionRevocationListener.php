<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Révoque les sessions API d'un utilisateur quand son mot de passe change ou que son compte est désactivé
 */

namespace App\EventListener;

use App\Entity\Admin\System\User;
use App\Repository\Admin\System\UserSessionRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
final readonly class UserSessionRevocationListener
{
    public function __construct(private UserSessionRepository $userSessionRepository) {}

    /**
     * @param User $user
     * @param PreUpdateEventArgs $args
     * @return void
     */
    public function preUpdate(User $user, PreUpdateEventArgs $args): void
    {
        $disabled = $args->hasChangedField('disabled') && $args->getNewValue('disabled') === true;
        if ($disabled || $args->hasChangedField('password')) {
            $this->userSessionRepository->deleteByUser($user);
        }
    }
}
