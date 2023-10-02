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
        ContainerBagInterface $containerBag,
        TranslatorInterface $translator,
        UrlGeneratorInterface $router,
        Security $security,
        RequestStack $requestStack,
        ParameterBagInterface $parameterBag,
        GridService $gridService,
        OptionSystemService $optionSystemService
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
                $this->translator->trans('page.grid.status', domain: 'page') => 'Status',
                $this->translator->trans('page.grid.tag', domain: 'page') => 'Tag',
                $this->translator->trans('page.grid.comment', domain: 'page') => 'Nb Comment',
                $this->translator->trans('page.grid.nb_see', domain: 'page') => 'Nb Comment',
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
     * @param Page $tag
     * @return array[]|string[]
     */
    private function generateTabAction(Page $page): array
    {
        $label = $page->getPageTranslationByLocale($this->requestStack->getCurrentRequest()->getLocale())->getTitre();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $this->router->generate('#', ['id' => $page->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('page.confirm.disabled.msg', ['label' => $label], 'page')];
        if ($page->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'url' => $this->router->generate('#', ['id' => $page->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'url' => $this->router->generate('#', ['id' => $page->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('page.confirm.delete.msg', ['label' =>
                    $label], 'tag')
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
            'url' => $this->router->generate('#', ['id' => $page->getId()]),
            'ajax' => false];

        return $actions;
    }
}
