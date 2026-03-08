<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service gérant la FAQ
 */

namespace App\Service\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Faq\FaqConst;
use App\Utils\Content\Faq\FaqFactory;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use App\Utils\Global\OrderEntity;
use App\Utils\System\Options\OptionSystemKey;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
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
        $requestStack = $this->getRequestStack();
        $gridService = $this->getGridService();

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

            $action = $this->generateTabAction($element);

            $optionSystemService = $this->getOptionSystemService();

            $locale = $optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
            if ($requestStack->getCurrentRequest() !== null) {
                $locale = $requestStack->getCurrentRequest()->getLocale();
            }
            $titre = $element->getFaqTranslationByLocale($locale)->getTitle();

            $data[] = [
                $translator->trans('faq.grid.id', domain: 'faq') => $element->getId(),
                $translator->trans('faq.grid.title', domain: 'faq') => $titre,
                $translator->trans('faq.grid.nb_categories', domain: 'faq') => $element
                    ->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES)
                    ->getValue(),
                $translator->trans('faq.grid.nb_questions', domain: 'faq') => $element
                    ->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS)
                    ->getValue(),
                $translator->trans('faq.grid.update_at', domain: 'faq') => $element->getUpdateAt()->format('d/m/y H:i'),
                GridService::KEY_ACTION => $action,
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
     * Retourne une liste de tag paginé
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
     * Génère le tableau d'action pour le Grid des faqs
     * @param Faq $faq
     * @return array[]|string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function generateTabAction(Faq $faq): array
    {
        $requestStack = $this->getRequestStack();
        $router = $this->getRouter();
        $translator = $this->getTranslator();
        $optionSystemService = $this->getOptionSystemService();

        $locale = $optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
        if ($requestStack->getCurrentRequest() !== null) {
            $locale = $requestStack->getCurrentRequest()->getLocale();
        }
        $label = $faq->getFaqTranslationByLocale($locale)->getTitle();

        $actionDisabled = [
            'label' => [
                'M3.933 13.909A4.357 4.357 0 0 1 3 12c0-1 4-6 9-6m7.6 3.8A5.068 5.068 0 0 1 21 12c0 1-3 6-9 6-.314 0-.62-.014-.918-.04M5 19 19 5m-4 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
            ],
            'type' => 'put',
            'color' => 'primary',
            'url' => $router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $translator->trans('faq.confirm.disabled.msg', ['label' => $label], 'faq'),
        ];
        if ($faq->isDisabled()) {
            $actionDisabled = [
                'label' => [
                    'M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z',
                    'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                ],
                'color' => 'primary',
                'type' => 'put',
                'url' => $router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
                'ajax' => true,
            ];
        }

        $actionDelete = '';
        if ($optionSystemService->canDelete()) {
            $actionDelete = [
                'label' => [
                    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
                ],
                'color' => 'danger',
                'type' => 'delete',
                'url' => $router->generate('admin_faq_delete', ['id' => $faq->getId()]),
                'ajax' => true,
                'confirm' => true,
                'msgConfirm' => $translator->trans('faq.confirm.delete.msg', ['label' => $label], 'faq'),
            ];
        }

        $actions = [];
        $actions[] = $actionDisabled;
        if ($actionDelete != '') {
            $actions[] = $actionDelete;
        }

        // Bouton edit
        $actions[] = [
            'label' => [
                'M10.779 17.779 4.36 19.918 6.5 13.5m4.279 4.279 8.364-8.643a3.027 3.027 0 0 0-2.14-5.165 3.03 3.03 0 0 0-2.14.886L6.5 13.5m4.279 4.279L6.499 13.5m2.14 2.14 6.213-6.504M12.75 7.04 17 11.28',
            ],
            'color' => 'primary',
            'id' => $faq->getId(),
            'url' => $router->generate('admin_faq_update', ['id' => $faq->getId()]),
            'ajax' => false,
        ];

        return $actions;
    }

    /**
     * Met à jour une FAQ stat en fonction de la FAQ, de sa clé, de son action et de sa valeur<br />
     * $action peut être FaqConst::STATISTIQUE_ACTION_ADD, FaqConst::STATISTIQUE_ACTION_SUB ou
     * FaqConst::STATISTIQUE_ACTION_OVERWRITE
     * @param Faq $faq
     * @param string $key
     * @param string $action
     * @param int $value
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateFaqStatistique(
        Faq $faq,
        string $key,
        string $action = FaqConst::STATISTIQUE_ACTION_ADD,
        int $value = 1,
    ): void {
        $faqStat = $faq->getFaqStatistiqueByKey($key);
        $val = $faqStat->getValue();
        switch ($action) {
            case FaqConst::STATISTIQUE_ACTION_ADD:
                $val = $val + $value;
                break;
            case FaqConst::STATISTIQUE_ACTION_SUB:
                $val = $val - $value;
                if ($val < 0) {
                    $val = 0;
                }
                break;
            case FaqConst::STATISTIQUE_ACTION_OVERWRITE:
                $val = $value;
                break;
            default:
        }
        $faqStat->setValue($val);
        $this->save($faqStat);
    }
}
