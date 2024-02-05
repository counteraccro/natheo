<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service gérant la FAQ
 */

namespace App\Service\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\AppService;
use App\Utils\Content\Faq\FaqConst;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaqService extends AppAdminService
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
        parent::__construct($entityManager, $containerBag, $translator, $router, $security, $requestStack,
            $parameterBag);
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
            $this->translator->trans('faq.grid.id', domain: 'faq'),
            $this->translator->trans('faq.grid.title', domain: 'faq'),
            $this->translator->trans('faq.grid.nb_questions', domain: 'faq'),
            $this->translator->trans('faq.grid.update_at', domain: 'faq'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Faq $element */

            $action = $this->generateTabAction($element);


            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $locale = $this->requestStack->getCurrentRequest()->getLocale();
            $titre = $element->getFaqTranslationByLocale($locale)->getTitle();

            $data[] = [
                $this->translator->trans('faq.grid.id', domain: 'faq') => $element->getId() . ' ' . $isDisabled,
                $this->translator->trans('faq.grid.title', domain: 'faq') => $titre,
                $this->translator->trans('faq.grid.nb_questions', domain: 'faq') =>
                    $element->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS)->getValue(),
                $this->translator->trans('faq.grid.update_at', domain: 'faq') => $element
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
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit): Paginator
    {
        $repo = $this->getRepository(Faq::class);
        return $repo->getAllPaginate($page, $limit);
    }

    /**
     * Génère le tableau d'action pour le Grid des faqs
     * @param Faq $faq
     * @return array[]|string[]
     */
    private function generateTabAction(Faq $faq): array
    {
        $label = $faq->getFaqTranslationByLocale($this->requestStack->getCurrentRequest()->getLocale())->getTitle();

        $actionDisabled = ['label' => '<i class="bi bi-eye-slash-fill"></i>',
            'url' => $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('faq.confirm.disabled.msg', ['label' => $label], 'faq')];
        if ($faq->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'url' => $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'url' => $this->router->generate('admin_faq_delete', ['id' => $faq->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $this->translator->trans('faq.confirm.delete.msg', ['label' =>
                    $label], 'faq')
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = ['label' => '<i class="bi bi-pencil-fill"></i>',
            'id' => $faq->getId(),
            'url' => $this->router->generate('admin_faq_update', ['id' => $faq->getId()]),
            'ajax' => false];

        return $actions;
    }

    /**
     * Retourne les traductions pour la création / édition d'une FAQ
     * @return array
     */
    public function getFaqTranslation(): array
    {
        return [
            'select_locale' => $this->translator->trans('faq.select.locale', domain: 'faq'),
            'loading' => $this->translator->trans('faq.select.loading', domain: 'faq'),
            'error_empty_value' => $this->translator->trans('faq.error.empty.value', domain: 'faq'),
            'save' =>  $this->translator->trans('faq.save', domain: 'faq'),
            ];
    }
}
