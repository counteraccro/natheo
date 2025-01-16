<?php
/**
 * @author Gourdon Aymeric
 * @version 1.4
 * Service gérant la création de page
 */

namespace App\Service\Admin\Content\Page;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageTranslation;
use App\Entity\Admin\System\User;
use App\Repository\Admin\Content\Page\PageTranslationRepository;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageHistory;
use App\Utils\Content\Page\PageStatistiqueKey;
use App\Utils\Content\Tag\TagRender;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PageService extends AppAdminService
{

    /**
     * Retourne une liste de page paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllPaginate(int $page, int $limit, string $search = null, $userId = null): Paginator
    {
        $repo = $this->getRepository(Page::class);
        return $repo->getAllPaginate($page, $limit, $search, $userId);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @param int|null $userId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null, int $userId = null): array
    {
        $translator = $this->getTranslator();
        $requestStack = $this->getRequestStack();
        $gridService = $this->getGridService();

        $column = [
            $translator->trans('page.grid.id', domain: 'page'),
            $translator->trans('page.grid.title', domain: 'page'),
            $translator->trans('page.grid.status', domain: 'page'),
            $translator->trans('page.grid.tag', domain: 'page'),
            $translator->trans('page.grid.comment', domain: 'page'),
            $translator->trans('page.grid.nb_see', domain: 'page'),
            $translator->trans('page.grid.update_at', domain: 'page'),
            $translator->trans('page.grid.author', domain: 'page'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search, $userId);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Page $element */

            $action = $this->generateTabAction($element);


            $isDisabled = '';
            $isLandingPage = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            if ($element->isLandingPage()) {
                $isLandingPage = '<i class="bi bi-pin-angle-fill"></i>';
            }

            $locale = $requestStack->getCurrentRequest()->getLocale();
            $titre = $element->getPageTranslationByLocale($locale)->getTitre();

            $data[] = [
                $translator->trans('page.grid.id', domain: 'page') => $element->getId() . ' ' . $isDisabled . ' ' . $isLandingPage,
                $translator->trans('page.grid.title', domain: 'page') => $titre,
                $translator->trans('page.grid.status', domain: 'page') =>
                    $this->getStatusStr($element->getStatus()),
                $translator->trans('page.grid.tag', domain: 'page') => $this->getTags($element->getTags()),
                $translator->trans('page.grid.comment', domain: 'page') => 0,
                $translator->trans('page.grid.nb_see', domain: 'page') =>
                    $element->getPageStatistiqueByKey(PageStatistiqueKey::KEY_PAGE_NB_READ)->getValue(),
                $translator->trans('page.grid.update_at', domain: 'page') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
                $translator->trans('page.grid.author', domain: 'page') => $element->getUser()->getLogin(),
                GridService::KEY_ACTION => $action,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $gridService->getFormatedSQLQuery($dataPaginate)
        ];
        return $gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param Page $page
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Page $page): array
    {
        $translator = $this->getTranslator();
        $router = $this->getRouter();
        $optionSystemService = $this->getOptionSystemService();
        $requestStack = $this->getRequestStack();

        $label = $page->getPageTranslationByLocale($requestStack->getCurrentRequest()->getLocale())->getTitre();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $router->generate('admin_page_update_disabled', ['id' => $page->getId()]),
            'type' => 'put',
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('page.confirm.disabled.msg', ['label' => $label], 'page')];
        if ($page->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $router->generate('admin_page_update_disabled', ['id' => $page->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
                'url' => $router->generate('admin_page_delete', ['id' => $page->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('page.confirm.delete.msg', ['label' =>
                    $label], 'page')
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
            'id' => $page->getId(),
            'url' => $router->generate('admin_page_update', ['id' => $page->getId()]),
            'ajax' => false];


        if (!$page->isLandingPage()) {

            $actions[] = ['label' => '<i class="bi bi-pin-angle-fill"></i>',
                'url' => $router->generate('admin_page_switch_Landing_page', ['id' => $page->getId()]),
                'type' => 'put',
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('page.confirm.landing.page.msg', ['label' => $label], 'page')];
        }


        return $actions;
    }

    /**
     * Retourne le status sous forme d'un label traduit
     * @param int $status
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStatusStr(int $status): string
    {
        $translator = $this->getTranslator();

        return match ($status) {
            PageConst::STATUS_DRAFT => $translator->trans('page.status.draft', domain: 'page'),
            PageConst::STATUS_PUBLISH => $translator->trans('page.status.publish', domain: 'page'),
            default => $translator->trans('page.status.inconnu', domain: 'page'),
        };
    }

    /**
     * Retourne la liste de status que peut avoir une page
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllStatus(): array
    {
        $translator = $this->getTranslator();

        return [
            PageConst::STATUS_DRAFT => $translator->trans('page.status.draft', domain: 'page'),
            PageConst::STATUS_PUBLISH => $translator->trans('page.status.publish', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste de rendu que peut avoir une page
     * @return array
     */
    public function getAllRender(): array
    {
        $translator = $this->getTranslator();

        return [
            PageConst::RENDER_1_BLOCK => $translator->trans('page.render.1.block', domain: 'page'),
            PageConst::RENDER_2_BLOCK => $translator->trans('page.render.2.block', domain: 'page'),
            PageConst::RENDER_3_BLOCK => $translator->trans('page.render.3.block', domain: 'page'),
            PageConst::RENDER_2_BLOCK_BOTTOM => $translator->trans('page.render.2.block.bottom', domain: 'page'),
            PageConst::RENDER_3_BLOCK_BOTTOM => $translator->trans('page.render.3.block.bottom', domain: 'page'),
            PageConst::RENDER_1_2_BLOCK => $translator->trans('page.render.1.2.block', domain: 'page'),
            PageConst::RENDER_2_1_BLOCK => $translator->trans('page.render.2.1.block', domain: 'page'),
            PageConst::RENDER_2_2_BLOCK => $translator->trans('page.render.2.2.block', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste de choix de type de content
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllContent(): array
    {
        $translator = $this->getTranslator();

        return [
            PageConst::CONTENT_TYPE_TEXT => $translator->trans('page.content.text', domain: 'page'),
            PageConst::CONTENT_TYPE_FAQ => $translator->trans('page.content.type.faq', domain: 'page'),
            PageConst::CONTENT_TYPE_LISTING => $translator->trans('page.content.type.listing', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste des catégories
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllCategories(): array
    {
        $translator = $this->getTranslator();
        return [
            PageConst::PAGE_CATEGORY_PAGE => $translator->trans('page.category.page', domain: 'page'),
            PageConst::PAGE_CATEGORY_PROJET => $translator->trans('page.category.projet', domain: 'page'),
            PageConst::PAGE_CATEGORY_ARTICLE => $translator->trans('page.category.article', domain: 'page'),
            PageConst::PAGE_CATEGORY_BLOG => $translator->trans('page.category.blog', domain: 'page'),
            PageConst::PAGE_CATEGORY_EVENEMEMT => $translator->trans('page.category.evenement', domain: 'page'),
            PageConst::PAGE_CATEGORY_NEWS => $translator->trans('page.category.news', domain: 'page'),
            PageConst::PAGE_CATEGORY_EVOLUTION => $translator->trans('page.category.evolution', domain: 'page'),
            PageConst::PAGE_CATEGORY_DOCUMENTATION => $translator->trans('page.category.documentation', domain: 'page'),
            PageConst::PAGE_CATEGORY_FAQ => $translator->trans('page.category.faq', domain: 'page'),
        ];
    }

    /**
     * Retourne une catégorie en fonction de son id
     * @param int $id
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCategoryById(int $id): string
    {
        $categories = $this->getAllCategories();
        if (isset($categories[$id])) {
            return $categories[$id];
        }
        return '';
    }

    /**
     * Génère une liste de tags au format HTML
     * @param Collection $tags
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTags(Collection $tags): string
    {
        $requestStack = $this->getRequestStack();

        $locale = $requestStack->getCurrentRequest()->getLocale();
        $return = '';
        foreach ($tags as $tag) {
            $tagRender = new TagRender($tag, $locale);
            $return .= ' ' . $tagRender->getHtml();
        }
        return trim($return);
    }

    /**
     * Permet de détecter si la date du dernier history enregistré est supérieur à la date
     * d'update de la page.
     * Retourne un tableau avec un message si la date du dernier history est supérieur
     * @param Page $page
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDiffBetweenHistoryAndPage(Page $page): array
    {
        $security = $this->getSecurity();
        $containerBag = $this->getContainerBag();
        $translator = $this->getTranslator();

        $return = [
            'show_msg' => false,
            'id' => 0,
            'msg' => '',
        ];

        /** @var User $user */
        $user = $security->getUser();
        $pageHistory = new PageHistory($containerBag, $user);
        $history = $pageHistory->getHistory($page->getId());
        if (empty($history)) {
            return $return;
        }

        if ($page->getUpdateAt() === null || $history[0]['time'] > $page->getUpdateAt()->getTimestamp()) {

            $msg = $translator->trans('page.msg.history.reload', domain: 'page');
            if ($page->getUpdateAt() === null) {
                $msg = $translator->trans('page.msg.history.new.reload', domain: 'page');
            }

            $return = [
                'show_msg' => true,
                'id' => $history[0]['id'],
                'msg' => $msg
            ];

        }
        return $return;
    }

    /**
     * @param string $url
     * @param int|null $id
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isUniqueUrl(string $url, int $id = null): bool
    {
        /** @var PageTranslationRepository $pageTransRepo */
        $pageTransRepo = $this->getRepository(PageTranslation::class);
        try {
            return $pageTransRepo->isUniqueUrl($url, $id);
        } catch (NonUniqueResultException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Retourne une liste d'élements type en fonction du type
     * @param int $type
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getListeContentByType(int $type): array
    {
        $translator = $this->getTranslator();

        $locale = $this->getLocales()['current'];
        $list = [];
        $selected = 0;
        $label = $help = '';

        switch ($type) {
            case PageConst::CONTENT_TYPE_FAQ :
                $repo = $this->getRepository(Faq::class);
                $list = $repo->getListeFaq($locale);
                $selected = array_key_first($list);
                $label = $translator->trans('page.content.faq.list', domain: 'page');
                $help = $translator->trans('page.content.faq.list.help', domain: 'page');
                break;
            case PageConst::CONTENT_TYPE_LISTING:
                $list = $this->getAllCategories();
                $selected = array_key_first($list);
                $label = $translator->trans('page.content.listing.list', domain: 'page');
                $help = $translator->trans('page.content.listing.list.help', domain: 'page');
                break;
            default:
                break;
        }

        return [
            'list' => $list,
            'selected' => $selected,
            'label' => $label,
            'help' => $help
        ];
    }

    /**
     * Permet de retourner les info du block en fonction du type et de son typeId
     * @param int $type
     * @param int $typeId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfoContentByTypeAndTypeId(int $type, int $typeId): array
    {
        $translator = $this->getTranslator();

        $typeStr = $translator->trans('page.content.type', domain: 'page') . ' : ';

        switch ($type) {
            case PageConst::CONTENT_TYPE_FAQ :
                /** @var Faq $faq */
                $faq = $this->findOneById(Faq::class, $typeId);
                $typeStr .= $translator->trans('page.content.type.faq', domain: 'page');
                $info = $faq->getFaqTranslationByLocale($this->getLocales()['current'])->getTitle();
                break;
            case PageConst::CONTENT_TYPE_LISTING :
                $typeStr .= $translator->trans('page.content.type.listing', domain: 'page');
                $info = $translator->trans('page.content.type.listing.info', ['type' => $this->getAllCategories()[$typeId]], domain: 'page');
                break;
            default:
                $typeStr .= $translator->trans('page.content.type.unknown', domain: 'page');
                $info = $translator->trans('page.content.info.unknown', domain: 'page');
        }

        return [
            'type' => $typeStr,
            'info' => $info
        ];

    }

    /**
     * Retourne l'ensemble des titres et url des pages du site
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllTitleAndUrlPage(): array
    {
        $return = [];
        $tab = $this->getAllPaginate(1, 100000);

        foreach ($tab as $page) {
            /** @var Page $page */
            $pageTranslations = $page->getPageTranslations();
            foreach ($pageTranslations as $pageTranslation) {
                $return[$page->getId()][$pageTranslation->getLocale()]
                    = [
                    'title' => $pageTranslation->getTitre(),
                    'url' => $pageTranslation->getUrl(),
                ];
            }
        }
        return $return;
    }

    /**
     * Force toutes les pages sauf $excludeId à false pour le champ landingPage
     * @param int $excludeId
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function switchLandingPage(int $excludeId): void
    {
        $repository = $this->getRepository(Page::class);
        $pages = $repository->getAllWithoutExclude('id', $excludeId);

        foreach ($pages as $page) {
            /** @var Page $page */
            $page->setLandingPage(false);
            $this->save($page, true);
        }
    }

    /**
     * Retourne une liste formatée de page pour les liens externes
     * @param string $locale
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFormatedListePageForInternalLink(string $locale): array
    {
        $listePages = $this->findBy(Page::class, ['disabled' => false, 'status' => PageConst::STATUS_PUBLISH]);

        $return = [];
        foreach ($listePages as $page) {
            /** @var Page $page */
            $pageTranslations = $page->getPageTranslationByLocale($locale);
            $return[$page->getId()] = [
                'id' => $page->getId(),
                'title' => $pageTranslations->getTitre(),
            ];
        }
        return $return;
    }
}
