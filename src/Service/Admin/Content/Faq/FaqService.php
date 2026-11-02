<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Service gérant la FAQ
 */

namespace App\Service\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqStatistique;
use App\Entity\Admin\Content\Page\PageContent;
use App\Enum\Admin\Content\Faq\FaqCsrfToken;
use App\Enum\Admin\Content\Faq\FaqStatistiqueAction;
use App\Enum\Admin\Content\Faq\FaqStatistiqueKey;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\Content\Page\PageContentRepository;
use App\Security\Voter\ContentVoter;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FaqService extends AppAdminService
{
    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, array $queryParams): array
    {
        $translator = $this->getTranslator();
        $gridService = $this->getGridService();
        $locale = $this->getCurrentLocale();
        $queryParams['locale'] = $locale;
        $csrfTokenManager = $this->getCsrfTokenManager();
        $csrfTokens = [
            FaqCsrfToken::DISABLED->value => $csrfTokenManager->getToken(FaqCsrfToken::DISABLED->value)->getValue(),
            FaqCsrfToken::DELETE->value => $csrfTokenManager->getToken(FaqCsrfToken::DELETE->value)->getValue(),
        ];

        $column = [
            $translator->trans('faq.grid.id', domain: 'faq'),
            $translator->trans('faq.grid.title', domain: 'faq'),
            $translator->trans('faq.grid.nb_categories', domain: 'faq'),
            $translator->trans('faq.grid.nb_questions', domain: 'faq'),
            $translator->trans('faq.grid.update_at', domain: 'faq'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $queryParams);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Faq $element */

            $data[] = [
                $translator->trans('faq.grid.id', domain: 'faq') => $element->getId(),
                $translator->trans('faq.grid.title', domain: 'faq') => $this->getEscapedTitle($element, $locale),
                $translator->trans('faq.grid.nb_categories', domain: 'faq') => $this->getStatistiqueValue(
                    $element,
                    FaqStatistiqueKey::NB_CATEGORIES,
                ),
                $translator->trans('faq.grid.nb_questions', domain: 'faq') => $this->getStatistiqueValue(
                    $element,
                    FaqStatistiqueKey::NB_QUESTIONS,
                ),
                $translator->trans('faq.grid.update_at', domain: 'faq') => $element->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $this->generateTabAction($element, $locale, $csrfTokens),
                'isDisabled' => $element->isDisabled(),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate),
            GridService::KEY_LIST_ORDER_FIELD => [
                'id' => $translator->trans('faq.grid.id', domain: 'faq'),
                'title' => $translator->trans('faq.grid.title', domain: 'faq'),
                'updateAt' => $translator->trans('faq.grid.update_at', domain: 'faq'),
            ],
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Retourne une liste de FAQ paginée
     * @param int $page
     * @param int $limit
     * @param array $queryParams
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, array $queryParams): Paginator
    {
        $repo = $this->getRepository(Faq::class);
        return $repo->getAllPaginate($page, $limit, $queryParams);
    }

    /**
     * Génère le tableau d'actions de la grille des FAQ, selon les droits (ContentVoter)
     * @param Faq $faq
     * @param string $locale
     * @param array<string, string> $csrfTokens jetons CSRF indexés par identifiant FaqCsrfToken
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Faq $faq, string $locale, array $csrfTokens): array
    {
        $router = $this->getRouter();
        $translator = $this->getTranslator();
        $security = $this->getSecurity();

        $label = $this->getEscapedTitle($faq, $locale);
        $actions = [];

        if ($security->isGranted(ContentVoter::DISABLE, $faq)) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
                'ajax' => true,
                'csrf' => $csrfTokens[FaqCsrfToken::DISABLED->value],
            ];
            if (!$faq->isDisabled()) {
                $actionDisabled['label'] = [
                    'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ];
                $actionDisabled['confirm'] = true;
                $actionDisabled['msgConfirm'] = $translator->trans(
                    'faq.confirm.disabled.msg',
                    ['label' => $label],
                    'faq',
                );
            }
            $actions[] = $actionDisabled;
        }

        if ($security->isGranted(ContentVoter::DELETE, $faq)) {
            $actions[] = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'color' => 'danger',
                'type' => 'delete',
                'url' => $router->generate('admin_faq_delete', ['id' => $faq->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('faq.confirm.delete.msg', ['label' => $label], 'faq'),
                'csrf' => $csrfTokens[FaqCsrfToken::DELETE->value],
            ];
        }

        if ($security->isGranted(ContentVoter::EDIT, $faq)) {
            $actions[] = [
                'label' => [
                    'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
                ],
                'color' => 'primary',
                'id' => $faq->getId(),
                'url' => $router->generate('admin_faq_update', ['id' => $faq->getId()]),
                'ajax' => false,
            ];
        }

        return $actions;
    }

    /**
     * Retourne le titre de la FAQ dans la locale, échappé pour un affichage HTML
     * @param Faq $faq
     * @param string $locale
     * @return string
     */
    public function getEscapedTitle(Faq $faq, string $locale): string
    {
        return htmlspecialchars((string) $faq->getFaqTranslationByLocale($locale)?->getTitle(), ENT_QUOTES);
    }

    /**
     * Retourne la valeur d'une statistique de la FAQ, 0 si elle n'existe pas
     * @param Faq $faq
     * @param FaqStatistiqueKey $key
     * @return int
     */
    public function getStatistiqueValue(Faq $faq, FaqStatistiqueKey $key): int
    {
        return intval($faq->getFaqStatistiqueByKey($key->value)?->getValue() ?? 0);
    }

    /**
     * Retourne le nombre de pages utilisant la FAQ
     * @param Faq $faq
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function countPages(Faq $faq): int
    {
        /** @var PageContentRepository $repo */
        $repo = $this->getRepository(PageContent::class);
        return $repo->countPagesByTypeAndTypeId(PageContentType::FAQ->value, $faq->getId());
    }

    /**
     * Met à jour une statistique de la FAQ en fonction de sa clé, de son action et de sa valeur
     * La statistique est créée si elle n'existe pas
     * @param Faq $faq
     * @param string $key
     * @param FaqStatistiqueAction $action
     * @param int $value
     * @param bool $flush
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateFaqStatistique(
        Faq $faq,
        string $key,
        FaqStatistiqueAction $action = FaqStatistiqueAction::ADD,
        int $value = 1,
        bool $flush = true,
    ): void {
        $faqStat = $faq->getFaqStatistiqueByKey($key);
        if ($faqStat === null) {
            $faqStat = new FaqStatistique();
            $faqStat->setKey($key)->setValue('0');
            $faq->addFaqStatistique($faqStat);
        }

        $val = intval($faqStat->getValue());
        $val = match ($action) {
            FaqStatistiqueAction::ADD => $val + $value,
            FaqStatistiqueAction::SUB => max(0, $val - $value),
            FaqStatistiqueAction::OVERWRITE => $value,
        };
        $faqStat->setValue(strval($val));
        $this->save($faqStat, $flush);
    }

    /**
     * Retourne la locale de la requête courante, ou la langue par défaut hors requête
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getCurrentLocale(): string
    {
        $request = $this->getRequestStack()->getCurrentRequest();
        if ($request !== null) {
            return $request->getLocale();
        }
        return (string) $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_DEFAULT_LANGUAGE->value);
    }
}
