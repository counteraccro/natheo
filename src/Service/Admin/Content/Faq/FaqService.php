<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service gérant la FAQ
 */

namespace App\Service\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use Doctrine\ORM\EntityManagerInterface;
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
    ])] ContainerInterface $handlers)
    {
        $this->gridService = $handlers->get('gridService');
        $this->optionSystemService = $handlers->get('optionSystemService');
        parent::__construct($handlers);
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
            'type' => 'put',
            'url' => $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
            'ajax' => true,
            'confirm' => true,
            'msgConfirm' => $this->translator->trans('faq.confirm.disabled.msg', ['label' => $label], 'faq')];
        if ($faq->isDisabled()) {
            $actionDisabled = [
                'label' => '<i class="bi bi-eye-fill"></i>',
                'type' => 'put',
                'url' => $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]),
                'ajax' => true
            ];
        }

        $actionDelete = '';
        if ($this->optionSystemService->canDelete()) {

            $actionDelete = [
                'label' => '<i class="bi bi-trash"></i>',
                'type' => 'delete',
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
     * Met à jour le champ disabled d'une question en fonction de $value
     * @param int $id
     * @param bool $value
     * @return string
     */
    public function updateDisabledQuestion(int $id, bool $value): string
    {
        /** @var FaqQuestion $question */
        $question = $this->findOneById(FaqQuestion::class, $id);
        $question->setDisabled($value);
        $this->save($question);

        $title = $question->getFaqQuestionTranslationByLocale($this->getLocales()['current'])->getTitle();

        if ($value) {
            return $this->translator->trans('faq.question.disabled.ok', ['question' => $title], domain: 'faq');
        }
        return $this->translator->trans('faq.question.enabled.ok', ['question' => $title], domain: 'faq');
    }

    /**
     * Met à jour le champ disabled d'une category en fonction de $value
     * si allQuestion est à true, réactive l'ensemble des questions associées à la catégorie
     * @param int $id
     * @param bool $allQuestion
     * @param bool $value
     * @return string
     */
    public function updateDisabledCategory(int $id, bool $allQuestion, bool $value): string
    {
        /** @var FaqCategory $category */
        $category = $this->findOneById(FaqCategory::class, $id);
        $category->setDisabled($value);

        foreach ($category->getFaqQuestions() as $question) {
            if (!$value && $allQuestion) {
                $question->setDisabled(false);
            } elseif ($value) {
                $question->setDisabled(true);
            }
        }

        $this->save($category);

        $title = $category->getFaqCategoryTranslationByLocale($this->getLocales()['current'])->getTitle();

        if ($value) {
            return $this->translator->trans('faq.category.disabled.ok', ['category' => $title], domain: 'faq');
        }

        if ($allQuestion) {
            return $this->translator->trans('faq.category.enabled.all.questions.ok',
                ['category' => $title], domain: 'faq');
        }
        return $this->translator->trans('faq.category.enabled.ok', ['category' => $title], domain: 'faq');

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
            'save' => $this->translator->trans('faq.save', domain: 'faq'),
            'new_faq' => $this->translator->trans('faq.new.title', domain: 'faq'),
            'new_faq_input_title' => $this->translator->trans('faq.new.input.title', domain: 'faq'),
            'new_faq_help' => $this->translator->trans('faq.new.help', domain: 'faq'),
            'new_faq_btn' => $this->translator->trans('faq.new.btn', domain: 'faq'),
            'new_category_btn' => $this->translator->trans('faq.new.category.btn', domain: 'faq'),
            'new_faq_error_empty' => $this->translator->trans('faq.new.input.error.empty', domain: 'faq'),
            'toast_title_success' => $this->translator->trans('faq.toast.title.success', domain: 'faq'),
            'toast_time' => $this->translator->trans('faq.toast.time', domain: 'faq'),
            'toast_title_error' => $this->translator->trans('faq.toast.title.error', domain: 'faq'),
            'faq_disabled_btn_ok' => $this->translator->trans('faq.disabled_btn_ok', domain: 'faq'),
            'faq_disabled_btn_ko' => $this->translator->trans('faq.disabled_btn_ko', domain: 'faq'),
            'faq_question_disabled' => $this->translator->trans('faq.question.disabled', domain: 'faq'),
            'faq_category_disabled' => $this->translator->trans('faq.category.disabled', domain: 'faq'),
            'faq_category_disabled_title' => $this->translator->trans('faq.category.disabled.title', domain: 'faq'),
            'faq_category_disabled_message' => $this->translator->trans('faq.category.disabled.message', domain: 'faq'),
            'faq_question_disabled_title' => $this->translator->trans('faq.question.disabled.title', domain: 'faq'),
            'faq_question_disabled_message' => $this->translator->trans('faq.question.disabled.message', domain: 'faq'),
            'faq_category_enabled_title' => $this->translator->trans('faq.category.enabled.title', domain: 'faq'),
            'faq_category_enabled_message' => $this->translator->trans('faq.category.enabled.message', domain: 'faq'),
            'faq_category_enabled_message_2' =>
                $this->translator->trans('faq.category.enabled.message.2', domain: 'faq'),
            'faq_question_enabled_title' => $this->translator->trans('faq.question.enabled.title', domain: 'faq'),
            'faq_question_enabled_message' => $this->translator->trans('faq.question.enabled.message', domain: 'faq'),
            'faq_category_new_title' => $this->translator->trans('faq.category.new.title', domain: 'faq'),
            'faq_category_new_liste_cat' => $this->translator->trans('faq.category.new.liste.cat', domain: 'faq'),
            'faq_category_new_after' => $this->translator->trans('faq.category.new.after', domain: 'faq'),
            'faq_category_new_before' => $this->translator->trans('faq.category.new.before', domain: 'faq'),
            'faq_category_new_help' => $this->translator->trans('faq.category.new.help', domain: 'faq'),
        ];
    }
}
