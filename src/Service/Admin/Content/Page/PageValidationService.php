<?php

declare(strict_types=1);
/**
 * Validation des pages (option OS_PAGE_VALIDATION) : soumission, validation, refus et notifications
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageValidationStatus;
use App\Enum\Admin\Global\Notification\Notification;
use App\Enum\Admin\System\User\Role;
use App\Factory\Admin\Global\Notification\NotificationFactory;
use App\Repository\Admin\System\UserRepository;
use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PageValidationService extends AppAdminService
{
    /**
     * Longueur maximale du motif de refus
     */
    public const int MAX_REASON_LENGTH = 1000;

    /**
     * True si l'option « Validation des pages » est active
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isActive(): bool
    {
        return $this->getOptionSystemService()->isPageValidationActive();
    }

    /**
     * True si l'utilisateur courant doit soumettre ses pages au lieu de les publier
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mustSubmit(): bool
    {
        return $this->isActive() && !$this->canValidate();
    }

    /**
     * True si l'utilisateur courant peut valider ou refuser une page
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function canValidate(): bool
    {
        return $this->getSecurity()->isGranted(Role::ADMIN->value);
    }

    /**
     * True si la page est en attente de validation
     * @param Page $page
     * @return bool
     */
    public function isPending(Page $page): bool
    {
        return $page->getValidationStatus() === PageValidationStatus::PENDING->value;
    }

    /**
     * Une page en attente n'est plus modifiable par un contributeur
     * @param Page $page
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isLocked(Page $page): bool
    {
        return $this->isPending($page) && !$this->canValidate();
    }

    /**
     * Passe la page en attente de validation et prévient les administrateurs
     * @param Page $page
     * @param User $user
     * @param string $locale
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function submit(Page $page, User $user, string $locale): void
    {
        $page->setValidationStatus(PageValidationStatus::PENDING->value);
        $page->setValidationUser($user);
        $page->setValidationAt(new \DateTime());
        $page->setValidationComment(null);
        $this->save($page);

        $params = $this->getNotificationParams($page, $user, $locale);
        foreach ($this->getValidators() as $validator) {
            $this->notify($validator, Notification::PAGE_VALIDATION_SUBMITTED->value, $params);
        }
    }

    /**
     * Termine la demande de validation après la publication de la page et, si elle était en attente,
     * prévient l'auteur de la soumission
     * @param Page $page
     * @param User $user
     * @param string $locale
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function approve(Page $page, User $user, string $locale): void
    {
        $wasPending = $this->isPending($page);
        $this->clear($page);
        if (!$wasPending) {
            return;
        }

        $submitter = $page->getValidationUser();

        if ($submitter !== null && $submitter->getId() !== $user->getId()) {
            $this->notify(
                $submitter,
                Notification::PAGE_VALIDATION_APPROVED->value,
                $this->getNotificationParams($page, $user, $locale),
            );
        }
    }

    /**
     * Refuse la page : elle redevient modifiable, avec le motif du refus, et l'auteur de la soumission est prévenu
     * @param Page $page
     * @param User $user
     * @param string|null $reason
     * @param string $locale
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function refuse(Page $page, User $user, ?string $reason, string $locale): void
    {
        $reason = $reason !== null ? mb_substr(trim($reason), 0, self::MAX_REASON_LENGTH) : '';
        $page->setValidationStatus(PageValidationStatus::REFUSED->value);
        $page->setValidationComment($reason !== '' ? $reason : null);
        $this->save($page);

        $submitter = $page->getValidationUser();
        if ($submitter !== null && $submitter->getId() !== $user->getId()) {
            $params = $this->getNotificationParams($page, $user, $locale);
            $params['reason'] = htmlspecialchars($reason !== '' ? $reason : '-', ENT_QUOTES);
            $this->notify($submitter, Notification::PAGE_VALIDATION_REFUSED->value, $params);
        }
    }

    /**
     * Remet la page sans demande de validation (retrait de la soumission, abandon du brouillon, archivage)
     * @param Page $page
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function clear(Page $page): void
    {
        if ($page->getValidationStatus() === PageValidationStatus::NONE->value) {
            return;
        }
        $page->setValidationStatus(PageValidationStatus::NONE->value);
        $page->setValidationComment(null);
        $this->save($page);
    }

    /**
     * Remet toutes les pages en attente sans demande de validation, quand l'option est désactivée
     * @return int nombre de pages modifiées
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function clearAllPending(): int
    {
        return (int) $this->getEntityManager()
            ->createQueryBuilder()
            ->update(Page::class, 'p')
            ->set('p.validationStatus', ':none')
            ->where('p.validationStatus = :pending')
            ->setParameter('none', PageValidationStatus::NONE->value)
            ->setParameter('pending', PageValidationStatus::PENDING->value)
            ->getQuery()
            ->execute();
    }

    /**
     * État de validation de la page pour l'éditeur
     * @param Page $page
     * @return array<string, mixed>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getState(Page $page): array
    {
        return [
            'validation_active' => $this->isActive(),
            'validation_status' => $page->getValidationStatus(),
            'validation_message' => $this->getMessage($page),
            'must_submit' => $this->mustSubmit(),
            'can_validate' => $this->canValidate(),
        ];
    }

    /**
     * Message HTML de l'éditeur sur l'état de validation de la page, vide s'il n'y a rien à signaler
     * @param Page $page
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMessage(Page $page): string
    {
        $translator = $this->getTranslator();
        if ($this->isPending($page)) {
            $params = [
                'user' => htmlspecialchars($page->getValidationUser()?->getLogin() ?? '-', ENT_QUOTES),
                'date' => $page->getValidationAt()?->format('d/m/Y H:i') ?? '-',
            ];
            $key = $this->canValidate()
                ? 'page.validation.banner_pending_validator'
                : 'page.validation.banner_pending_locked';
            return $translator->trans($key, $params, 'page');
        }

        if ($page->getValidationStatus() === PageValidationStatus::REFUSED->value) {
            $reason = $page->getValidationComment();
            if ($reason === null) {
                return $translator->trans('page.validation.banner_refused_no_reason', domain: 'page');
            }
            return $translator->trans(
                'page.validation.banner_refused',
                ['reason' => nl2br(htmlspecialchars($reason, ENT_QUOTES))],
                'page',
            );
        }

        if ($this->mustSubmit()) {
            return $translator->trans('page.validation.banner_must_submit', domain: 'page');
        }
        return '';
    }

    /**
     * Administrateurs et super administrateurs actifs
     * @return User[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getValidators(): array
    {
        /** @var UserRepository $repository */
        $repository = $this->getRepository(User::class);
        $validators = [];
        foreach ([Role::ADMIN, Role::SUPER_ADMIN] as $role) {
            foreach ($repository->findByRole($role->value) as $user) {
                $validators[$user->getId()] = $user;
            }
        }
        return array_values($validators);
    }

    /**
     * Paramètres communs des notifications de validation
     * @param Page $page
     * @param User $user auteur de l'action
     * @param string $locale
     * @return array<string, string|int>
     */
    private function getNotificationParams(Page $page, User $user, string $locale): array
    {
        return [
            'login' => htmlspecialchars((string) $user->getLogin(), ENT_QUOTES),
            'page' => $this->getPageService()->getEscapedTitle($page, $locale),
            'page_id' => (int) $page->getId(),
        ];
    }

    /**
     * Ajoute une notification à l'utilisateur si les notifications sont actives
     * @param User $user
     * @param string $key
     * @param array $params
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function notify(User $user, string $key, array $params): void
    {
        if (!$this->getOptionSystemService()->canNotification()) {
            return;
        }
        $this->save((new NotificationFactory($user))->addNotification($key, $params)->getUser());
    }
}
