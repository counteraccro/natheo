<?php

declare(strict_types=1);
/**
 * Gestion du brouillon des pages publiées : enregistrement, publication, abandon, prévisualisation
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageDraft;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Populate\Admin\Content\Page\PagePopulate;
use App\Repository\Admin\Content\Page\PageDraftRepository;
use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PageDraftService extends AppAdminService
{
    /**
     * Une page publiée se modifie au travers d'un brouillon, la version en ligne restant inchangée
     * @param Page $page
     * @return bool
     */
    public function isDraftMode(Page $page): bool
    {
        return $page->getId() !== null && $page->getStatus() === PageStatus::PUBLISH->value;
    }

    /**
     * Une page archivée ne peut plus être modifiée
     * @param Page $page
     * @return bool
     */
    public function isReadOnly(Page $page): bool
    {
        return $page->getStatus() === PageStatus::ARCHIVED->value;
    }

    /**
     * Retourne le brouillon de la page, null si elle n'en a pas
     * @param Page $page
     * @return PageDraft|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDraft(Page $page): ?PageDraft
    {
        if ($page->getId() === null) {
            return null;
        }
        return $this->getRepository(PageDraft::class)->findOneBy(['page' => $page]);
    }

    /**
     * Retourne les ids, parmi $pageIds, des pages qui ont un brouillon
     * @param int[] $pageIds
     * @return int[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPageIdsWithDraft(array $pageIds): array
    {
        /** @var PageDraftRepository $repository */
        $repository = $this->getRepository(PageDraft::class);
        return $repository->getPageIdsWithDraft($pageIds);
    }

    /**
     * Normalise les données reçues de l'éditeur pour le brouillon : même id et même statut que la page en ligne
     * @param Page $page
     * @param array $data
     * @return array
     */
    public function normalizeData(Page $page, array $data): array
    {
        $data['id'] = $page->getId();
        $data['status'] = $page->getStatus();
        return $data;
    }

    /**
     * Construit une copie de travail de la page avec les données $data, sans jamais toucher la page en ligne.
     * La copie doit être libérée avec releaseWorkingCopy() après usage
     * @param Page $page
     * @param array $data
     * @return PagePopulate
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createWorkingCopy(Page $page, array $data): PagePopulate
    {
        $copy = clone $page;
        return (new PagePopulate($copy, $this->normalizeData($page, $data), $this->getPageService()))->populate();
    }

    /**
     * Retire la copie de travail des menus (côté inverse) pour qu'elle ne soit jamais enregistrée
     * @param Page $copy
     * @return void
     */
    public function releaseWorkingCopy(Page $copy): void
    {
        foreach ($copy->getMenus() as $menu) {
            $menu->getPages()->removeElement($copy);
        }
    }

    /**
     * Crée ou met à jour le brouillon de la page
     * @param Page $page
     * @param array $data
     * @param User $user
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveDraft(Page $page, array $data, User $user): bool
    {
        $draft = $this->getDraft($page) ?? (new PageDraft())->setPage($page);
        $draft->setData($this->normalizeData($page, $data));
        $draft->setUser($user);
        // Force la mise à jour même si les données sont identiques
        $draft->setUpdateAt(new \DateTime());

        $this->save($draft);
        return $this->getResponseAjax()['success'];
    }

    /**
     * Applique les données $data à la page en ligne puis supprime son brouillon, en une transaction
     * @param Page $page
     * @param array $data
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function publishDraft(Page $page, array $data): bool
    {
        $pageService = $this->getPageService();
        $draft = $this->getDraft($page);
        $populate = (new PagePopulate($page, $this->normalizeData($page, $data), $pageService))->populate();
        $page = $populate->getPage();

        $entityManager = $this->getEntityManager();
        try {
            $entityManager->wrapInTransaction(function () use ($entityManager, $page, $draft, $pageService) {
                $entityManager->persist($page);
                if ($draft !== null) {
                    $entityManager->remove($draft);
                }
                $entityManager->flush();
                if ($page->isLandingPage()) {
                    $pageService->switchLandingPage($page->getId());
                }
            });
        } catch (\Throwable $exception) {
            $this->getLogger()->error($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Supprime le brouillon de la page s'il existe
     * @param Page $page
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function discardDraft(Page $page): bool
    {
        $draft = $this->getDraft($page);
        if ($draft === null) {
            return true;
        }
        $this->remove($draft);
        return $this->getResponseAjax()['success'];
    }

    /**
     * Archive la page : elle quitte le site et ne peut plus être modifiée, son brouillon est supprimé
     * @param Page $page
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function archive(Page $page): bool
    {
        $draft = $this->getDraft($page);
        $entityManager = $this->getEntityManager();
        try {
            $entityManager->wrapInTransaction(function () use ($entityManager, $page, $draft) {
                $page->setStatus(PageStatus::ARCHIVED->value);
                $entityManager->persist($page);
                if ($draft !== null) {
                    $entityManager->remove($draft);
                }
                $entityManager->flush();
            });
        } catch (\Throwable $exception) {
            $this->getLogger()->error($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Retourne la page telle qu'elle sera une fois son brouillon publié, ou null si elle n'a pas de brouillon.
     * La copie doit être libérée avec releaseWorkingCopy() après usage
     * @param Page $page
     * @return Page|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPreviewPage(Page $page): ?Page
    {
        $draft = $this->getDraft($page);
        if ($draft === null) {
            return null;
        }
        return $this->createWorkingCopy($page, $draft->getData())->getPage();
    }
}
