<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service gérant la création de page
 */

namespace App\Service\Admin\Content\Page;

use App\Entity\Admin\Content\Page\Page;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageStatistiqueKey;
use App\Utils\Content\Tag\TagRender;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageService extends AppAdminService
{
    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router,
        Security               $security,
        RequestStack           $requestStack,
        ParameterBagInterface  $parameterBag,
        GridService            $gridService,
        OptionSystemService    $optionSystemService
    )
    {
        $this->gridService = $gridService;
        $this->optionSystemService = $optionSystemService;
        parent::__construct($entityManager, $containerBag, $translator, $router,
            $security, $requestStack, $parameterBag);
    }

    /**
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(Page::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit): array
    {
        $column = [
            $this->translator->trans('page.grid.id', domain: 'page'),
            $this->translator->trans('page.grid.title', domain: 'page'),
            $this->translator->trans('page.grid.status', domain: 'page'),
            $this->translator->trans('page.grid.tag', domain: 'page'),
            $this->translator->trans('page.grid.comment', domain: 'page'),
            $this->translator->trans('page.grid.nb_see', domain: 'page'),
            $this->translator->trans('page.grid.update_at', domain: 'page'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Page $element */

            $action = $this->generateTabAction($element);


            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $locale = $this->requestStack->getCurrentRequest()->getLocale();
            $titre = $element->getPageTranslationByLocale($locale)->getTitre();

            $data[] = [
                $this->translator->trans('page.grid.id', domain: 'page') => $element->getId() . ' ' . $isDisabled,
                $this->translator->trans('page.grid.title', domain: 'page') => $titre,
                $this->translator->trans('page.grid.status', domain: 'page') =>
                    $this->getStatusStr($element->getStatus()),
                $this->translator->trans('page.grid.tag', domain: 'page') => $this->getTags($element->getTags()),
                $this->translator->trans('page.grid.comment', domain: 'page') => 0,
                $this->translator->trans('page.grid.nb_see', domain: 'page') =>
                    $element->getPageStatistiqueByKey(PageStatistiqueKey::KEY_PAGE_NB_VISITEUR)->getValue(),
                $this->translator->trans('page.grid.update_at', domain: 'page') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => json_encode($action),
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);

    }

    /**
     * Génère le tableau d'action pour le Grid des sidebarElement
     * @param Page $page
     * @return array[]|string[]
     */
    private function generateTabAction(Page $page): array
    {
        $label = $page->getPageTranslationByLocale($this->requestStack->getCurrentRequest()->getLocale())->getTitre();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $this->router->generate('admin_page_update_disabled', ['id' => $page->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('page.confirm.disabled.msg', ['label' => $label], 'page')];
        if ($page->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'url' => $this->router->generate('admin_page_update_disabled', ['id' => $page->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'url' => $this->router->generate('admin_page_delete', ['id' => $page->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('page.confirm.delete.msg', ['label' =>
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
            'url' => $this->router->generate('admin_page_update', ['id' => $page->getId()]),
            'ajax' => false];

        return $actions;
    }

    /**
     * Retourne le status sous forme d'un label traduit
     * @param int $status
     * @return string
     */
    public function getStatusStr(int $status): string
    {
        return match ($status) {
            PageConst::STATUS_DRAFT => $this->translator->trans('page.status.draft', domain: 'page'),
            PageConst::STATUS_PUBLISH => $this->translator->trans('page.status.publish', domain: 'page'),
            default => $this->translator->trans('page.status.inconnu', domain: 'page'),
        };
    }

    /**
     * Génère une liste de tags au format HTML
     * @param Collection $tags
     * @return string
     */
    public function getTags(Collection $tags): string
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $return = '';
        foreach ($tags as $tag) {
            $tagRender = new TagRender($tag, $locale);
            $return .= ' ' . $tagRender->getHtml();
        }
        return trim($return);
    }

    /**
     * Retourne les traductions pour les pages
     * @return array
     */
    public function getPageTranslation(): array
    {
        return [
            'select_locale' => $this->translator->trans('page.select.locale', domain: 'page'),
            'onglet_content' => $this->translator->trans('page.onglet.content', domain: 'page'),
            'onglet_seo' => $this->translator->trans('page.onglet.seo', domain: 'page'),
            'onglet_tags' => $this->translator->trans('page.onglet.tag', domain: 'page'),
            'onglet_history' => $this->translator->trans('page.onglet.history', domain: 'page'),
            'loading' => $this->translator->trans('page.loading', domain: 'page'),
            'msg_auto_save_success' => $this->translator->trans('page.msg.auto_save.success', domain: 'page'),
            'page_content_form' => [
                'input_url_label' => $this->translator->trans('page.page_content_form.input.url.label', domain: 'page'),
                'input_url_info' => $this->translator->trans('page.page_content_form.input.url.info', domain: 'page'),
                'input_titre_label' =>
                    $this->translator->trans('page.page_content_form.input.titre.label', domain: 'page'),
                'input_titre_info' =>
                    $this->translator->trans('page.page_content_form.input.titre.info', domain: 'page'),
            ],
            'page_history' => [
                'description' => $this->translator->trans('page.page_history.description', domain: 'page'),
            ]
        ];
    }
}
