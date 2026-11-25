<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des tests des ressources AdminFaq, AdminFaqCategory et AdminFaqQuestion : FAQ avec leur arbre, textes dans
 * chaque langue du site
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Entity\Admin\System\ActionLog;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\Api\V2\AppApiV2TestCase;

abstract class AdminFaqTestCase extends AppApiV2TestCase
{
    /**
     * FAQ active « <title> » en français et « <title> en|es » ailleurs, avec ses catégories et leurs questions
     * @param User|null $user
     * @param string $title
     * @param array<string, string[]> $tree titre de catégorie => titres de ses questions
     * @param array $customData
     * @return Faq
     */
    protected function createTitledFaq(?User $user, string $title, array $tree = [], array $customData = []): Faq
    {
        $faq = $this->createFaq($user, array_merge(['disabled' => false], $customData));
        foreach (['fr', 'en', 'es'] as $locale) {
            $this->createFaqTranslation($faq, ['locale' => $locale, 'title' => $this->localized($title, $locale)]);
        }
        $position = 1;
        foreach ($tree as $categoryTitle => $questions) {
            $category = $this->createTitledCategory($faq, $categoryTitle, $position++);
            foreach (array_values($questions) as $i => $questionTitle) {
                $this->createTitledQuestion($category, $questionTitle, $i + 1);
            }
        }
        return $faq;
    }

    /**
     * @param Faq $faq
     * @param string $title
     * @param int $position
     * @param bool $disabled
     * @return FaqCategory
     */
    protected function createTitledCategory(Faq $faq, string $title, int $position, bool $disabled = false): FaqCategory
    {
        $category = $this->createFaqCategory($faq, ['renderOrder' => $position, 'disabled' => $disabled]);
        foreach (['fr', 'en', 'es'] as $locale) {
            $this->createFaqCategoryTranslation($category, [
                'locale' => $locale,
                'title' => $this->localized($title, $locale),
            ]);
        }
        return $category;
    }

    /**
     * @param FaqCategory $category
     * @param string $title
     * @param int $position
     * @param bool $disabled
     * @return FaqQuestion
     */
    protected function createTitledQuestion(
        FaqCategory $category,
        string $title,
        int $position,
        bool $disabled = false,
    ): FaqQuestion {
        $question = $this->createFaqQuestion($category, ['renderOrder' => $position, 'disabled' => $disabled]);
        foreach (['fr', 'en', 'es'] as $locale) {
            $this->createFaqQuestionTranslation($question, [
                'locale' => $locale,
                'title' => $this->localized($title, $locale),
                'answer' => 'Réponse ' . $this->localized($title, $locale),
            ]);
        }
        return $question;
    }

    /**
     * Bloc de page qui affiche la FAQ
     * @param Faq $faq
     * @return void
     */
    protected function usePage(Faq $faq): void
    {
        $this->createPageContent(customData: ['type' => PageContentType::FAQ->value, 'typeId' => $faq->getId()]);
    }

    /**
     * Titre de chaque langue du site
     * @param string $title
     * @return array<int, array{locale: string, title: string}>
     */
    protected function titles(string $title): array
    {
        return array_map(fn(string $locale) => ['locale' => $locale, 'title' => $this->localized($title, $locale)], [
            'fr',
            'en',
            'es',
        ]);
    }

    /**
     * En-têtes d'une session back-office d'un nouveau contributeur
     * @param User|null $user rempli avec le contributeur créé
     * @return array<string, string>
     */
    protected function contributorHeaders(?User &$user = null): array
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        return $this->adminSessionHeaders($user);
    }

    /**
     * En-têtes d'une session back-office d'un nouvel administrateur
     * @param User|null $user
     * @return array<string, string>
     */
    protected function adminHeaders(?User &$user = null): array
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        return $this->adminSessionHeaders($user);
    }

    /**
     * @param bool $allow
     * @return void
     */
    protected function setAllowDelete(bool $allow): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, $allow ? '1' : '0');
    }

    /**
     * Entrées du journal du module, de la plus ancienne à la plus récente
     * @return array<int, array{0: string|null, 1: string|null, 2: string|null, 3: string|null}>
     */
    protected function logs(): array
    {
        return array_map(
            fn(ActionLog $log) => [$log->getAction(), $log->getResource(), $log->getResourceId(), $log->getLabel()],
            $this->em->getRepository(ActionLog::class)->findBy(['module' => 'faq'], ['id' => 'ASC']),
        );
    }

    /**
     * Rechargement depuis la base, sans le cache de l'EntityManager
     * @template T of object
     * @param class-string<T> $class
     * @param int|null $id
     * @return T|null
     */
    protected function reload(string $class, ?int $id): ?object
    {
        $this->em->clear();
        return $this->em->getRepository($class)->find($id);
    }

    /**
     * @param string $text
     * @param string $locale
     * @return string
     */
    private function localized(string $text, string $locale): string
    {
        return $locale === 'fr' ? $text : $text . ' ' . $locale;
    }
}
