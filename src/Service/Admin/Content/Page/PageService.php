<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
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
use App\Service\Admin\MarkdownEditorService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageHistory;
use App\Utils\Content\Page\PageStatistiqueKey;
use App\Utils\Content\Tag\TagRender;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
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

    /**
     * @var MarkdownEditorService
     */
    private MarkdownEditorService $markdownEditorService;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(#[AutowireLocator([
        'logger' => LoggerInterface::class,
        'entityManager' => EntityManagerInterface::class,
        'containerBag' => ContainerBagInterface::class,
        'translator' => TranslatorInterface::class,
        'router' => UrlGeneratorInterface::class,
        'security' => Security::class,
        'requestStack' => RequestStack::class,
        'parameterBag' => ParameterBagInterface::class,
        'optionSystemService' => OptionSystemService::class,
        'gridService' => GridService::class,
        'markdownEditorService' => MarkdownEditorService::class
    ])] ContainerInterface $handlers)
    {
        $this->gridService = $handlers->get('gridService');
        $this->optionSystemService = $handlers->get('optionSystemService');
        $this->markdownEditorService = $handlers->get('markdownEditorService');
        parent::__construct($handlers);
    }

    /**
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $repo = $this->getRepository(Page::class);
        return $repo->getAllPaginate($page, $limit, $search);
    }

    /**
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return array
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null): array
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

        $dataPaginate = $this->getAllPaginate($page, $limit, $search);

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
                GridService::KEY_ACTION => $action,
            ];
        }

        $tabReturn = [
            GridService::KEY_NB => $nb,
            GridService::KEY_DATA => $data,
            GridService::KEY_COLUMN => $column,
            GridService::KEY_RAW_SQL => $this->gridService->getFormatedSQLQuery($dataPaginate)
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
            'type' => 'put',
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('page.confirm.disabled.msg', ['label' => $label], 'page')];
        if ($page->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $this->router->generate('admin_page_update_disabled', ['id' => $page->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
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
            PageConst::RENDER_2_BLOCK_BOTTOM => $this->translator->trans('page.render.2.block.bottom', domain: 'page'),
            PageConst::RENDER_3_BLOCK_BOTTOM => $this->translator->trans('page.render.3.block.bottom', domain: 'page'),
            PageConst::RENDER_1_2_BLOCK => $this->translator->trans('page.render.1.2.block', domain: 'page'),
            PageConst::RENDER_2_1_BLOCK => $this->translator->trans('page.render.2.1.block', domain: 'page'),
            PageConst::RENDER_2_2_BLOCK => $this->translator->trans('page.render.2.2.block', domain: 'page'),
        ];
    }

    /**
     * Retourne la liste de choix de type de content
     * @return array
     */
    public function getAllContent(): array
    {
        return [
            PageConst::CONTENT_TYPE_TEXT => $this->translator->trans('page.content.text', domain: 'page'),
            PageConst::CONTENT_TYPE_FAQ => $this->translator->trans('page.content.type.faq', domain: 'page'),
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
        $return = [
            'show_msg' => false,
            'id' => 0,
            'msg' => '',
        ];

        /** @var User $user */
        $user = $this->security->getUser();
        $pageHistory = new PageHistory($this->containerBag, $user);
        $history = $pageHistory->getHistory($page->getId());
        if (empty($history)) {
            return $return;
        }

        if ($page->getUpdateAt() === null || $history[0]['time'] > $page->getUpdateAt()->getTimestamp()) {

            $msg = $this->translator->trans('page.msg.history.reload', domain: 'page');
            if ($page->getUpdateAt() === null) {
                $msg = $this->translator->trans('page.msg.history.new.reload', domain: 'page');
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
     * @param int $typeId
     * @return array
     */
    public function getListeContentByType(int $type): array
    {
        $locale = $this->getLocales()['current'];
        $list = [];
        $selected = 0;
        $label = $help = '';

        switch ($type) {
            case PageConst::CONTENT_TYPE_FAQ :
                $repo = $this->getRepository(Faq::class);
                $list = $repo->getListeFaq($locale);
                $selected = array_key_first($list);
                $label = $this->translator->trans('page.content.faq.list', domain: 'page');
                $help = $this->translator->trans('page.content.faq.list.help', domain: 'page');
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
     */
    public function getInfoContentByTypeAndTypeId(int $type, int $typeId): array
    {
        $typeStr = $this->translator->trans('page.content.type', domain: 'page') . ' : ';

        switch ($type) {
            case PageConst::CONTENT_TYPE_FAQ :
                /** @var Faq $faq */
                $faq = $this->findOneById(Faq::class, $typeId);
                $typeStr .= $this->translator->trans('page.content.type.faq', domain: 'page');
                $info = $faq->getFaqTranslationByLocale($this->getLocales()['current'])->getTitle();
                break;
            default:
                $typeStr .= $this->translator->trans('page.content.type.unknown', domain: 'page');
                $info = $this->translator->trans('page.content.info.unknown', domain: 'page');
        }

        return [
            'type' => $typeStr,
            'info' => $info
        ];

    }
}
