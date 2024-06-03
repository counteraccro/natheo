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
use App\Utils\Content\Faq\FaqConst;
use App\Utils\Content\Faq\FaqFactory;
use App\Utils\Content\Faq\FaqStatistiqueKey;
use App\Utils\Global\OrderEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
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
     * Construit le tableau de donnée à envoyé au tableau GRID
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAllFormatToGrid(int $page, int $limit, string $search = null): array
    {
        $translator = $this->handlers->get('translator');
        $requestStack = $this->handlers->get('requestStack');
        $gridService = $this->handlers->get('gridService');

        $column = [
            $translator->trans('faq.grid.id', domain: 'faq'),
            $translator->trans('faq.grid.title', domain: 'faq'),
            $translator->trans('faq.grid.nb_categories', domain: 'faq'),
            $translator->trans('faq.grid.nb_questions', domain: 'faq'),
            $translator->trans('faq.grid.update_at', domain: 'faq'),
            GridService::KEY_ACTION,
        ];

        $dataPaginate = $this->getAllPaginate($page, $limit, $search);

        $nb = $dataPaginate->count();
        $data = [];
        foreach ($dataPaginate as $element) {
            /* @var Faq $element */

            $action = $this->generateTabAction($element);


            $isDisabled = '';
            if ($element->isDisabled()) {
                $isDisabled = '<i class="bi bi-eye-slash"></i>';
            }

            $locale = $requestStack->getCurrentRequest()->getLocale();
            $titre = $element->getFaqTranslationByLocale($locale)->getTitle();

            $data[] = [
                $translator->trans('faq.grid.id', domain: 'faq') => $element->getId() . ' ' . $isDisabled,
                $translator->trans('faq.grid.title', domain: 'faq') => $titre,
                $translator->trans('faq.grid.nb_categories', domain: 'faq') =>
                    $element->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES)->getValue(),
                $translator->trans('faq.grid.nb_questions', domain: 'faq') =>
                    $element->getFaqStatistiqueByKey(FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS)->getValue(),
                $translator->trans('faq.grid.update_at', domain: 'faq') => $element
                    ->getUpdateAt()->format('d/m/y H:i'),
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
     * Retourne une liste de tag paginé
     * @param int $page
     * @param int $limit
     * @param string|null $search
     * @return Paginator
     */
    public function getAllPaginate(int $page, int $limit, string $search = null): Paginator
    {
        $repo = $this->getRepository(Faq::class);
        return $repo->getAllPaginate($page, $limit, $search);
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
     * Retourne une liste de Category trier par renderOrder
     * @param int $id
     * @return array
     */
    public function getListeCategoryOrderByFaq(int $id): array
    {
        /** @var Faq $faq */
        $faq = $this->findOneById(Faq::class, $id);

        $return = [];
        foreach ($faq->getFaqCategories() as $faqCategory) {
            /** @var FaqCategory $faqCategory */
            $return[] = [
                'id' => $faqCategory->getId(),
                'value' => $this->translator->trans('faq.position', domain: 'faq')
                    . ' ' . $faqCategory->getRenderOrder() . ' - '
                    . $faqCategory->getFaqCategoryTranslationByLocale($this->getLocales()['current'])->getTitle()];
        }
        return $return;
    }

    /**
     * Retourne une liste de question triée par renderOrder
     * @param int $id
     * @return array
     */
    public function getListeQuestionOrderByCategory(int $id): array
    {
        /** @var FaqCategory $category */
        $category = $this->findOneById(FaqCategory::class, $id);

        $return = [];
        foreach ($category->getFaqQuestions() as $faqQuestion) {
            /** @var FaqQuestion $faqQuestion */
            $return[] = [
                'id' => $faqQuestion->getId(),
                'value' => $this->translator->trans('faq.position', domain: 'faq')
                    . ' ' . $faqQuestion->getRenderOrder() . ' - '
                    . $faqQuestion->getFaqQuestionTranslationByLocale($this->getLocales()['current'])->getTitle()];
        }
        return $return;
    }

    /**
     * Créer une nouvelle catégorie et la range dans l'ordre défini
     * @param int $idFaq
     * @param int $idCatOrder
     * @param string $orderPosition
     * @return void
     * @throws Exception
     */
    public function addNewCategory(int $idFaq, int $idCatOrder, string $orderPosition): void
    {
        /** @var Faq $faq */
        $faq = $this->findOneById(Faq::class, $idFaq);
        $faqFactory = new FaqFactory($this->getLocales()['locales']);
        $faq = $faqFactory->createFaqCategory($faq);

        $this->updateFaqStatistique($faq,FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES);
        $this->updateFaqStatistique($faq,FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS);

        $this->save($faq);

        $lastCat = $faq->getFaqCategories()->last();

        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $orderEntity->orderByIdByAction($idCatOrder, $lastCat->getId(), $orderPosition);
        $this->save($faq);

    }

    /**
     * Créer une nouvelle question et la range dans l'ordre défini
     * @param int $idCategory
     * @param int $idQuestionOrder
     * @param string $orderPosition
     * @return void
     * @throws Exception
     */
    public function addNewQuestion(int $idCategory, int $idQuestionOrder, string $orderPosition): void
    {
        $faqCategory = $this->findOneById(FaqCategory::class, $idCategory);
        $faqFactory = new FaqFactory($this->getLocales()['locales']);
        $faqCategory = $faqFactory->createFaqQuestion($faqCategory);

        $this->updateFaqStatistique($faqCategory->getFaq(),FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS);

        $this->save($faqCategory);

        $lastQuest = $faqCategory->getFaqQuestions()->last();
        $orderEntity = new OrderEntity($faqCategory->getFaqQuestions());
        $orderEntity->orderByIdByAction($idQuestionOrder, $lastQuest->getId(), $orderPosition);
        $this->save($faqCategory);
    }

    /**
     * Déplace la position d'une catégorie de -1 ou +1 en fonction de $orderPosition
     * @param int $idFaq
     * @param int $idCategory
     * @param string $orderPosition
     * @return void
     * @throws Exception
     */
    public function updateOrderCategory(int $idFaq, int $idCategory, string $orderPosition): void
    {
        /** @var Faq $faq */
        $faq = $this->findOneById(Faq::class, $idFaq);
        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $orderEntity->orderUpdateByAction($idCategory, $orderPosition);
        $this->save($faq);
    }

    /**
     * Déplace la position d'une question de -1 ou +1 en fonction de $orderPosition
     * @param int $idFaqCategory
     * @param int $idQuestion
     * @param string $orderPosition
     * @return void
     * @throws Exception
     */
    public function updateOrderQuestion(int $idFaqCategory, int $idQuestion, string $orderPosition): void
    {
        /** @var FaqCategory $faqCategory */
        $faqCategory = $this->findOneById(FaqCategory::class, $idFaqCategory);
        $orderEntity = new OrderEntity($faqCategory->getFaqQuestions());
        $orderEntity->orderUpdateByAction($idQuestion, $orderPosition);
        $this->save($faqCategory);
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
     */
    public function updateFaqStatistique(
        Faq    $faq,
        string $key,
        string $action = FaqConst::STATISTIQUE_ACTION_ADD,
        int    $value = 1
    ): void
    {
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

    /**
     * Supprime une catégorie et la réordonne
     * @throws Exception
     */
    public function deleteCategory(int $id): void
    {
        /** @var FaqCategory $faqCategory */
        $faqCategory = $this->findOneById(FaqCategory::class, $id);
        $nbQuestion = $faqCategory->getFaqQuestions()->count();
        $faq = $faqCategory->getFaq();

        $faq->removeFaqCategory($faqCategory);
        $this->updateFaqStatistique($faq,FaqStatistiqueKey::KEY_STAT_NB_CATEGORIES, FaqConst::STATISTIQUE_ACTION_SUB);
        $this->updateFaqStatistique($faq,FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS,
            FaqConst::STATISTIQUE_ACTION_SUB, $nbQuestion);

        $orderEntity = new OrderEntity($faq->getFaqCategories());
        $orderEntity->reOrderList();
        $this->save($faq);
    }

    /**
     * Supprime une question
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function deleteQuestion(int $id): void
    {
        /** @var FaqQuestion $faqQuestion */
        $faqQuestion = $this->findOneById(FaqQuestion::class, $id);
        $faqCategory = $faqQuestion->getFaqCategory();

        $faqCategory->removeFaqQuestion($faqQuestion);
        $this->updateFaqStatistique($faqCategory->getFaq(),FaqStatistiqueKey::KEY_STAT_NB_QUESTIONS,
            FaqConst::STATISTIQUE_ACTION_SUB);

        $orderEntity = new OrderEntity($faqCategory->getFaqQuestions());
        $orderEntity->reOrderList();
        $this->save($faqCategory);
    }
}
