<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service gérant les tags de l'application
 */
namespace App\Service\Admin\Content\Tag;

use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Tag\TagRender;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TagService extends AppAdminService
{
    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /** Constructeur
     * @param EntityManagerInterface $entityManager
     * @param ContainerBagInterface $containerBag
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     * @param Security $security
     * @param RequestStack $requestStack
     * @param GridService $gridService
     * @param OptionSystemService $optionSystemService
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerBagInterface  $containerBag,
        TranslatorInterface    $translator,
        UrlGeneratorInterface  $router,
        Security               $security,
        RequestStack           $requestStack,
        GridService            $gridService,
        OptionSystemService    $optionSystemService,
        ParameterBagInterface  $parameterBag
    )
    {
        $this->gridService = $gridService;
        $this->optionSystemService = $optionSystemService;
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack, $parameterBag);
    }

    /**
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(Tag::class);
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
            $this->translator->trans('tag.grid.id', domain: 'tag'),
            $this->translator->trans('tag.grid.label', domain: 'tag'),
            $this->translator->trans('tag.grid.color', domain: 'tag'),
            $this->translator->trans('tag.grid.created_at', domain: 'tag'),
            $this->translator->trans('tag.grid.update_at', domain: 'tag'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Tag $element */

            $action = $this->generateTabAction($element);


            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $locale = $this->requestStack->getCurrentRequest()->getLocale();
            $tagRender = new TagRender($element, $locale);

            $data[] = [
                $this->translator->trans('tag.grid.id', domain: 'tag') => $element->getId() . ' ' . $isDisabled,
                $this->translator->trans('tag.grid.label', domain: 'tag') => $tagRender->getHtml(),
                $this->translator->trans('tag.grid.color', domain: 'tag') => $element->getColor(),
                $this->translator->trans('tag.grid.created_at', domain: 'tag') => $element
                    ->getCreatedAt()->format('d/m/y H:i'),
                $this->translator->trans('tag.grid.update_at', domain: 'tag') => $element
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
     * @param Tag $tag
     * @return array[]|string[]
     */
    private function generateTabAction(Tag $tag): array
    {
        $label = $tag->getTagTranslationByLocale($this->requestStack->getCurrentRequest()->getLocale())->getLabel();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $this->router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('tag.confirm.disabled.msg', ['label' => $label], 'tag')];
        if ($tag->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'url' => $this->router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'url' => $this->router->generate('admin_tag_delete', ['id' => $tag->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('tag.confirm.delete.msg', ['label' =>
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
            'id' => $tag->getId(),
            'url' => $this->router->generate('admin_tag_update', ['id' => $tag->getId()]),
            'ajax' => false];

        return $actions;
    }

    /**
     * Traduction pour le formulaire de création / édition d'un tag
     * @return array
     */
    public function getTranslateTagForm(): array
    {
        return [
            'loading' => $this->translator->trans('tag.form.loading', domain: 'tag'),
            'formTitleCreate' => $this->translator->trans('tag.form.title.create', domain: 'tag'),
            'formTitleUpdate' => $this->translator->trans('tag.form.title.update', domain: 'tag'),
            'formInputColorLabel' => $this->translator->trans('tag.form.input.color.label', domain: 'tag'),
            'formInputColorError' => $this->translator->trans('tag.form.input.color.error', domain: 'tag'),
            'formInputLabelLabel' => $this->translator->trans('tag.form.input.label.label', domain: 'tag'),
            'formInputLabelError' => $this->translator->trans('tag.form.input.label.error', domain: 'tag'),
            'linkColorChoice' => $this->translator->trans('tag.form.link.color.choice', domain: 'tag'),
            'colorTitle' => $this->translator->trans('tag.form.color.title', domain: 'tag'),
            'colorDescription' => $this->translator->trans('tag.form.color.description', domain: 'tag'),
            'labelCurrent' => $this->translator->trans('tag.form.title.label.current', domain: 'tag'),
            'labelOther' => $this->translator->trans('tag.form.title.label.other', domain: 'tag'),
            'autoCopy' => $this->translator->trans('tag.form.check.auto.copy', domain: 'tag'),
            'renduTitle' => $this->translator->trans('tag.form.rendu.title', domain: 'tag'),
            'btnSubmitUpdate' => $this->translator->trans('tag.form.submit.update', domain: 'tag'),
            'btnSubmitCreate' => $this->translator->trans('tag.form.submit.create', domain: 'tag'),
            'statTitle' => $this->translator->trans('tag.form.stat.title', domain: 'tag'),
            'formDisabledLabel' => $this->translator->trans('tag.form.input.disabled.label', domain: 'tag'),
        ];

    }

    /**
     * @param string $locale
     * @param string $search
     * @return array
     */
    public function searchByLocale(string $locale, string $search): array
    {
        return [];
    }
}
