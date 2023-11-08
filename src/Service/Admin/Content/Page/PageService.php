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
     * Retourne la liste de status que peut avoir une page
     * @return array
     */
    public function getAllStatus(): array
    {
        return [
            PageConst::STATUS_DRAFT => $this->translator->trans('page.status.draft', domain: 'page'),
            PageConst::STATUS_PUBLISH => $this->translator->trans('page.status.publish', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste de rendu que peut avoir une page
     * @return array
     */
    public function getAllRender(): array
    {
        return [
            PageConst::RENDER_1_BLOCK => $this->translator->trans('page.render.1.block', domain: 'page'),
            PageConst::RENDER_2_BLOCK => $this->translator->trans('page.render.2.block', domain: 'page'),
            PageConst::RENDER_3_BLOCK => $this->translator->trans('page.render.3.block', domain: 'page'),
            PageConst::RENDER_2_1_BLOCK => $this->translator->trans('page.render.2.1.block', domain: 'page'),
            PageConst::RENDER_2_2_BLOCK => $this->translator->trans('page.render.2.2.block', domain: 'page'),
        ];
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
            'onglet_save' => $this->translator->trans('page.onglet.save', domain: 'page'),
            'loading' => $this->translator->trans('page.loading', domain: 'page'),
            'msg_auto_save_success' => $this->translator->trans('page.msg.auto_save.success', domain: 'page'),
            'tag_title' => $this->translator->trans('page.onglet.tag.title', domain: 'page'),
            'tag_sub_title' => $this->translator->trans('page.onglet.tag.sub_title', domain: 'page'),
            'msg_add_tag_success' => $this->translator->trans('page.onglet.tag.msg.add_tag_success', domain: 'page'),
            'msg_del_tag_success' => $this->translator->trans('page.onglet.tag.msg.del_tag_success', domain: 'page'),
            'page_content_form' => [
                'title' => $this->translator->trans('page.page_content_form.title', domain: 'page'),
                'input_url_label' => $this->translator->trans('page.page_content_form.input.url.label', domain: 'page'),
                'input_url_info' => $this->translator->trans('page.page_content_form.input.url.info', domain: 'page'),
                'input_titre_label' =>
                    $this->translator->trans('page.page_content_form.input.titre.label', domain: 'page'),
                'input_titre_info' =>
                    $this->translator->trans('page.page_content_form.input.titre.info', domain: 'page'),
                'list_render_label' => $this->translator->trans('page.page_save.list_render_label', domain: 'page'),
                'list_render_help' => $this->translator->trans('page.page_save.list_render_help', domain: 'page'),
            ],
            'page_content' => [
                'title' => $this->translator->trans('page.page_content.title', domain: 'page'),
            ],
            'page_history' => [
                'title' => $this->translator->trans('page.page_history.title', domain: 'page'),
                'description' => $this->translator->trans('page.page_history.description', domain: 'page'),
                'empty' => $this->translator->trans('page.page_history.empty', domain: 'page'),
                'update' => $this->translator->trans('page.page_history.update', domain: 'page'),
                'for' => $this->translator->trans('page.page_history.for', domain: 'page'),
                'reload' => $this->translator->trans('page.page_history.reload', domain: 'page'),
            ],
            'page_save' => [
                'title' => $this->translator->trans('page.page_save.title', domain: 'page'),
                'list_status_label' => $this->translator->trans('page.page_save.list_status_label', domain: 'page'),
                'list_status_help' => $this->translator->trans('page.page_save.list_status_help', domain: 'page'),
                'btn_save' => $this->translator->trans('page.page_save.btn.save', domain: 'page'),
                'btn_see_ext' => $this->translator->trans('page.page_save.btn.see_ext', domain: 'page'),
            ],
            'auto_complete' => [
                'auto_complete_label' => $this->translator->trans('page.tag.auto_complete.label', domain: 'page'),
                'auto_complete_placeholder' =>
                    $this->translator->trans('page.tag.auto_complete.placeholder', domain: 'page'),
                'auto_complete_help' => $this->translator->trans('page.tag.auto_complete.help', domain: 'page'),
                'auto_complete_btn' => $this->translator->trans('page.tag.auto_complete.btn', domain: 'page')
            ]
        ];
    }
}
