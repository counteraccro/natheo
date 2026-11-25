<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Lecture et écriture des FAQ, de leurs catégories et de leurs questions par l'API d'administration
 */

namespace App\Service\Api\Admin\Content;

use App\Api\V2\Exception\ConflictException;
use App\Dto\Api\Admin\AdminCollectionQuery;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqCategoryTranslation;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\Content\Faq\FaqQuestionTranslation;
use App\Entity\Admin\Content\Faq\FaqTranslation;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Factory\Admin\Content\Faq\FaqFactory;
use App\Repository\Admin\Content\Faq\FaqCategoryRepository;
use App\Repository\Admin\Content\Faq\FaqQuestionRepository;
use App\Repository\Admin\Content\Faq\FaqRepository;
use App\Repository\Admin\Content\Page\PageContentRepository;
use App\Service\Api\AppApiService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiAdminFaqService extends AppApiService
{
    /**
     * Page de la collection d'administration dans la langue de contenu (voir FaqRepository::getAdminCollection())
     * @param AdminCollectionQuery $query
     * @return Paginator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCollection(AdminCollectionQuery $query): Paginator
    {
        return $this->getFaqRepository()->getAdminCollection(
            $query,
            $this->getContentLocale(),
            $this->getDefaultLocale(),
        );
    }

    /**
     * Catégories triées de la FAQ, avec leurs traductions
     * @param Faq $faq
     * @return FaqCategory[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTreeCategories(Faq $faq): array
    {
        /** @var FaqCategoryRepository $repository */
        $repository = $this->getRepository(FaqCategory::class);
        return $repository->getAdminTreeCategories($faq);
    }

    /**
     * Questions triées de la FAQ, titre dans la langue de contenu (voir FaqQuestionRepository::getAdminTreeRows())
     * @param Faq $faq
     * @return array<int, array{id: int, categoryId: int, position: int, disabled: bool, title: ?string}>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTreeQuestionRows(Faq $faq): array
    {
        /** @var FaqQuestionRepository $repository */
        $repository = $this->getRepository(FaqQuestion::class);
        return $repository->getAdminTreeRows($faq, $this->getContentLocale(), $this->getDefaultLocale());
    }

    /**
     * @param int $id
     * @return Faq|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function find(int $id): ?Faq
    {
        return $this->getFaqRepository()->find($id);
    }

    /**
     * @param int $id
     * @return FaqCategory|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findCategory(int $id): ?FaqCategory
    {
        /** @var FaqCategory|null $category */
        $category = $this->getRepository(FaqCategory::class)->find($id);
        return $category;
    }

    /**
     * @param int $id
     * @return FaqQuestion|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findQuestion(int $id): ?FaqQuestion
    {
        /** @var FaqQuestion|null $question */
        $question = $this->getRepository(FaqQuestion::class)->find($id);
        return $question;
    }

    /**
     * Langues du site, dans l'ordre de app.supported_locales
     * @return string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getLocales(): array
    {
        return $this->getSupportedLocales();
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefaultLocale(): string
    {
        return $this->getOptionSystemService()->getDefaultLanguage();
    }

    /**
     * Nombre de pages qui affichent la FAQ
     * @param Faq $faq
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function countPages(Faq $faq): int
    {
        /** @var PageContentRepository $repository */
        $repository = $this->getRepository(PageContent::class);
        return $repository->countPagesByTypeAndTypeId(PageContentType::FAQ->value, (int) $faq->getId());
    }

    /**
     * Crée une FAQ avec une catégorie et une question aux textes par défaut
     * @param User|null $user auteur
     * @param array<string, string> $titles titre par langue du site
     * @param bool $disabled
     * @return Faq
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(?User $user, array $titles, bool $disabled = false): Faq
    {
        $faq = (new FaqFactory($this->getLocales(), $this->getTranslator()))->create()->getFaq();
        $faq->setUser($user)->setDisabled($disabled);
        $this->applyTitles($faq->getFaqTranslations()->toArray(), $titles, fn() => null);
        $this->save($faq);
        return $faq;
    }

    /**
     * Modifie une FAQ, une valeur null ou une langue absente restant inchangée
     * @param Faq $faq
     * @param array<string, string> $titles
     * @param bool|null $disabled
     * @return Faq
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(Faq $faq, array $titles = [], ?bool $disabled = null): Faq
    {
        if ($disabled !== null) {
            $faq->setDisabled($disabled);
        }
        $this->applyTitles($faq->getFaqTranslations()->toArray(), $titles, function (string $locale) use ($faq) {
            $translation = (new FaqTranslation())->setLocale($locale);
            $faq->addFaqTranslation($translation);
            return $translation;
        });
        $faq->setUpdateAt(new \DateTime());
        $this->save($faq);
        return $faq;
    }

    /**
     * Supprime une FAQ qu'aucune page n'affiche
     * @param Faq $faq
     * @return void
     * @throws ConflictException si une page affiche la FAQ
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete(Faq $faq): void
    {
        $count = $this->countPages($faq);
        if ($count > 0) {
            throw new ConflictException(
                $this->getTranslator()->trans('faq.error.delete.used', ['nb' => $count], 'faq'),
            );
        }
        $this->getEntityManager()->remove($faq);
        $this->getEntityManager()->flush();
    }

    /**
     * Crée une catégorie sans question en dernière position
     * @param Faq $faq
     * @param array<string, string> $titles titre par langue du site
     * @param bool $disabled
     * @return FaqCategory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createCategory(Faq $faq, array $titles, bool $disabled = false): FaqCategory
    {
        $category = (new FaqCategory())->setDisabled($disabled)->setRenderOrder($faq->getFaqCategories()->count() + 1);
        $faq->addFaqCategory($category);
        $this->applyTitles([], $titles, function (string $locale) use ($category) {
            $translation = (new FaqCategoryTranslation())->setLocale($locale);
            $category->addFaqCategoryTranslation($translation);
            return $translation;
        });
        $faq->setUpdateAt(new \DateTime());
        $this->save($faq);
        return $category;
    }

    /**
     * Modifie une catégorie, une valeur null ou une langue absente restant inchangée ; la désactiver désactive ses
     * questions actives
     * @param FaqCategory $category
     * @param array<string, string> $titles
     * @param bool|null $disabled
     * @param int|null $position à partir de 1
     * @return int[] identifiants des questions désactivées avec la catégorie
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateCategory(
        FaqCategory $category,
        array $titles = [],
        ?bool $disabled = null,
        ?int $position = null,
    ): array {
        $disabledQuestions = [];
        if ($disabled === true && !$category->isDisabled()) {
            foreach ($category->getFaqQuestions() as $question) {
                if (!$question->isDisabled()) {
                    $question->setDisabled(true);
                    $disabledQuestions[] = (int) $question->getId();
                }
            }
        }
        if ($disabled !== null) {
            $category->setDisabled($disabled);
        }
        $this->applyTitles($category->getFaqCategoryTranslations()->toArray(), $titles, function (string $locale) use (
            $category,
        ) {
            $translation = (new FaqCategoryTranslation())->setLocale($locale);
            $category->addFaqCategoryTranslation($translation);
            return $translation;
        });
        $faq = $category->getFaq();
        if ($position !== null && $faq !== null) {
            $this->moveTo($this->sorted($faq->getFaqCategories()->toArray()), $category, $position);
        }
        $faq?->setUpdateAt(new \DateTime());
        $this->save($category);
        return $disabledQuestions;
    }

    /**
     * Supprime une catégorie et ses questions, les suivantes remontent
     * @param FaqCategory $category
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteCategory(FaqCategory $category): void
    {
        $faq = $category->getFaq();
        $faq?->removeFaqCategory($category);
        if ($faq !== null) {
            $this->renumber($this->sorted($faq->getFaqCategories()->toArray()));
            $faq->setUpdateAt(new \DateTime());
        }
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }

    /**
     * Crée une question en dernière position de sa catégorie
     * @param FaqCategory $category
     * @param array<string, array{title: string, answer: string}> $texts titre et réponse par langue du site
     * @param bool $disabled
     * @return FaqQuestion
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createQuestion(FaqCategory $category, array $texts, bool $disabled = false): FaqQuestion
    {
        $question = (new FaqQuestion())
            ->setDisabled($disabled)
            ->setRenderOrder($category->getFaqQuestions()->count() + 1);
        $category->addFaqQuestion($question);
        $this->applyQuestionTexts($question, $texts);
        $category->getFaq()?->setUpdateAt(new \DateTime());
        $this->save($category);
        return $question;
    }

    /**
     * Modifie une question, une valeur null ou une langue absente restant inchangée
     * @param FaqQuestion $question
     * @param array<string, array{title: string, answer: string}> $texts
     * @param bool|null $disabled
     * @param FaqCategory|null $category nouvelle catégorie, de la même FAQ
     * @param int|null $position à partir de 1, dans la catégorie
     * @return FaqQuestion
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function updateQuestion(
        FaqQuestion $question,
        array $texts = [],
        ?bool $disabled = null,
        ?FaqCategory $category = null,
        ?int $position = null,
    ): FaqQuestion {
        if ($disabled !== null) {
            $question->setDisabled($disabled);
        }
        $this->applyQuestionTexts($question, $texts);

        $source = $question->getFaqCategory();
        if ($category !== null && $source !== null && $category->getId() !== $source->getId()) {
            // Sans removeFaqQuestion() : l'orphanRemoval de la catégorie d'origine supprimerait la question
            $this->renumber(
                array_filter($this->sorted($source->getFaqQuestions()->toArray()), fn($q) => $q !== $question),
            );
            $question->setFaqCategory($category);
            $question->setRenderOrder($category->getFaqQuestions()->count() + 1);
            $category->getFaqQuestions()->add($question);
            $position ??= $category->getFaqQuestions()->count();
        }
        $target = $question->getFaqCategory();
        if ($position !== null && $target !== null) {
            $this->moveTo($this->sorted($target->getFaqQuestions()->toArray()), $question, $position);
        }
        $target?->getFaq()?->setUpdateAt(new \DateTime());
        $this->save($question);
        return $question;
    }

    /**
     * Supprime une question, les suivantes remontent
     * @param FaqQuestion $question
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteQuestion(FaqQuestion $question): void
    {
        $category = $question->getFaqCategory();
        $category?->removeFaqQuestion($question);
        if ($category !== null) {
            $this->renumber($this->sorted($category->getFaqQuestions()->toArray()));
            $category->getFaq()?->setUpdateAt(new \DateTime());
        }
        $this->getEntityManager()->remove($question);
        $this->getEntityManager()->flush();
    }

    /**
     * Place $item à $position parmi $siblings (triés, $item compris) puis les renumérote à partir de 1
     * @param array<FaqCategory|FaqQuestion> $siblings
     * @param FaqCategory|FaqQuestion $item
     * @param int $position
     * @return void
     */
    private function moveTo(array $siblings, FaqCategory|FaqQuestion $item, int $position): void
    {
        $siblings = array_values(array_filter($siblings, fn($sibling) => $sibling !== $item));
        $index = max(0, min($position - 1, count($siblings)));
        array_splice($siblings, $index, 0, [$item]);
        $this->renumber($siblings);
    }

    /**
     * Tri par position puis identifiant, sur les collections en mémoire (Criteria relirait la base)
     * @template T of FaqCategory|FaqQuestion
     * @param T[] $items
     * @return T[]
     */
    private function sorted(array $items): array
    {
        usort(
            $items,
            fn($a, $b) => [$a->getRenderOrder(), $a->getId() ?? PHP_INT_MAX] <=> [
                $b->getRenderOrder(),
                $b->getId() ?? PHP_INT_MAX,
            ],
        );
        return $items;
    }

    /**
     * @param array<FaqCategory|FaqQuestion> $items triés
     * @return void
     */
    private function renumber(array $items): void
    {
        foreach (array_values($items) as $i => $item) {
            $item->setRenderOrder($i + 1);
        }
    }

    /**
     * Applique les titres des langues du site aux traductions, $create ajoutant celle d'une langue absente
     * @param array<FaqTranslation|FaqCategoryTranslation> $translations
     * @param array<string, string> $titles
     * @param callable(string): (FaqTranslation|FaqCategoryTranslation|null) $create
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function applyTitles(array $translations, array $titles, callable $create): void
    {
        $byLocale = [];
        foreach ($translations as $translation) {
            $byLocale[$translation->getLocale()] = $translation;
        }
        foreach ($this->filterLocales($titles) as $locale => $title) {
            ($byLocale[$locale] ?? $create($locale))?->setTitle(trim($title));
        }
    }

    /**
     * @param FaqQuestion $question
     * @param array<string, array{title: string, answer: string}> $texts
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function applyQuestionTexts(FaqQuestion $question, array $texts): void
    {
        foreach ($this->filterLocales($texts) as $locale => $text) {
            $translation = $question
                ->getFaqQuestionTranslations()
                ->findFirst(fn($key, FaqQuestionTranslation $t) => $t->getLocale() === $locale);
            if ($translation === null) {
                $translation = (new FaqQuestionTranslation())->setLocale($locale);
                $question->addFaqQuestionTranslation($translation);
            }
            $translation->setTitle(trim($text['title']))->setAnswer($text['answer']);
        }
    }

    /**
     * Valeurs des langues du site seulement
     * @template T
     * @param array<string, T> $values
     * @return array<string, T>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function filterLocales(array $values): array
    {
        return array_intersect_key($values, array_flip($this->getLocales()));
    }

    /**
     * @return FaqRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getFaqRepository(): FaqRepository
    {
        /** @var FaqRepository $repository */
        $repository = $this->getRepository(Faq::class);
        return $repository;
    }
}
