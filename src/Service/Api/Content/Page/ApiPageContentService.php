<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service correctement formaté les pageContents d'une page pour l'API
 */

namespace App\Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Enum\Admin\System\Options\OptionUser;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Service\Global\MarkdownService;
use App\Service\Admin\System\User\PersonalData;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiPageContentService extends AppApiService
{
    /**
     * @param ApiFindPageContentDto $dto
     * @param User|null $user
     * @return array|void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface|CommonMarkException
     */
    public function getPageContentForApi(ApiFindPageContentDto $dto, ?User $user = null): ?array
    {
        /** @var PageContent|null $pageContent */
        $pageContent = $this->findOneById(PageContent::class, $dto->getId());
        if (empty($pageContent) || !$this->isPageVisible($pageContent->getPage(), $user)) {
            return [];
        }

        $pageContent = $this->getFormatContent($pageContent, $dto);
        if (empty($pageContent['content'])) {
            return [];
        }

        return $pageContent;
    }

    /**
     * Retourne un contenu de page si sa page est publiée et active
     * @param int $id
     * @return PageContent|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findPublishedPageContent(int $id): ?PageContent
    {
        /** @var PageContent|null $pageContent */
        $pageContent = $this->findOneById(PageContent::class, $id);
        if ($pageContent === null || !$this->isPageVisible($pageContent->getPage())) {
            return null;
        }
        return $pageContent;
    }

    /**
     * Indique si un bloc a un contenu à servir dans la langue demandée : texte non vide, FAQ active,
     * catégorie de listing connue
     * @param PageContent $pageContent
     * @param string $locale
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function isPageContentAvailable(PageContent $pageContent, string $locale): bool
    {
        return match (PageContentType::tryFrom((int) $pageContent->getType())) {
            PageContentType::TEXT => (string) $pageContent->getPageContentTranslationByLocale($locale)?->getText() !==
                '',
            PageContentType::FAQ => $this->findActiveFaq((int) $pageContent->getTypeId()) !== null,
            PageContentType::LISTING => PageCategory::tryFrom((int) $pageContent->getTypeId()) !== null,
            default => false,
        };
    }

    /**
     * Contenu d'un bloc calculé pour l'API : HTML d'un texte, FAQ active et ses réponses en HTML, catégorie et titre
     * d'un listing ; null si le type est inconnu ou le contenu vide dans la langue demandée
     * @param PageContent $pageContent
     * @param string $locale
     * @return array{type: PageContentType, html?: string, faq?: Faq, answers?: array<int, string>, category?: PageCategory, title?: string}|null
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getRenderedContent(PageContent $pageContent, string $locale): ?array
    {
        $type = PageContentType::tryFrom((int) $pageContent->getType());
        switch ($type) {
            case PageContentType::TEXT:
                $text = (string) $pageContent->getPageContentTranslationByLocale($locale)?->getText();
                return $text === '' ? null : ['type' => $type, 'html' => $this->renderMarkdown($text)];
            case PageContentType::FAQ:
                $faq = $this->findActiveFaq((int) $pageContent->getTypeId());
                return $faq === null
                    ? null
                    : ['type' => $type, 'faq' => $faq, 'answers' => $this->renderFaqAnswers($faq, $locale)];
            case PageContentType::LISTING:
                $category = PageCategory::tryFrom((int) $pageContent->getTypeId());
                return $category === null
                    ? null
                    : ['type' => $type, 'category' => $category, 'title' => $this->getListingTitle($category, $locale)];
        }
        return null;
    }

    /**
     * Blocs d'une page ayant un contenu à servir dans la langue demandée
     * @param Page $page
     * @param string $locale
     * @return PageContent[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAvailablePageContents(Page $page, string $locale): array
    {
        return array_values(
            array_filter(
                $page->getPageContents()->toArray(),
                fn(PageContent $pageContent) => $this->isPageContentAvailable($pageContent, $locale),
            ),
        );
    }

    /**
     * Retourne une FAQ active
     * @param int $id
     * @return Faq|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function findActiveFaq(int $id): ?Faq
    {
        /** @var Faq|null $faq */
        $faq = $this->findOneByCriteria(Faq::class, ['id' => $id, 'disabled' => false]);
        return $faq;
    }

    /**
     * Convertit un texte Markdown en HTML, liens internes résolus
     * @param string $markdown
     * @return string
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function renderMarkdown(string $markdown): string
    {
        $markdownService = new MarkdownService();
        return $markdownService->convertMarkdownToHtml($this->getMarkdownEditorService()->parseMarkdown($markdown));
    }

    /**
     * Réponses des questions actives d'une FAQ converties en HTML, indexées par id de question
     * @param Faq $faq
     * @param string $locale
     * @return array<int, string>
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function renderFaqAnswers(Faq $faq, string $locale): array
    {
        $answers = [];
        foreach ($faq->getFaqCategories() as $faqCategory) {
            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
                if ($faqCategory->isDisabled() || $faqQuestion->isDisabled()) {
                    continue;
                }
                $answer = (string) $faqQuestion->getFaqQuestionTranslationByLocale($locale)?->getAnswer();
                $answers[(int) $faqQuestion->getId()] = $this->renderMarkdown($answer);
            }
        }
        return $answers;
    }

    /**
     * Titre d'un bloc listing traduit dans la langue demandée
     * @param PageCategory $category
     * @param string $locale
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getListingTitle(PageCategory $category, string $locale): string
    {
        return $this->getTranslator()->trans(
            'page.content.listing.title',
            parameters: ['category' => $this->getPageService()->getCategoryById($category->value)],
            domain: 'page',
            locale: $locale,
        );
    }

    /**
     * Formate au format API les pageContents
     * @param PageContent $pageContent
     * @param ApiFindPageContentDto $dto
     * @return array
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFormatContent(PageContent $pageContent, ApiFindPageContentDto $dto): array
    {
        $return = ['id' => $pageContent->getId()];

        switch ($pageContent->getType()) {
            case PageContentType::TEXT->value:
                $return['content'] = $this->formatContentText(
                    $pageContent->getPageContentTranslationByLocale($dto->getLocale())->getText(),
                );
                break;
            case PageContentType::FAQ->value:
                $return['content'] = $this->formatContentFAq($pageContent->getTypeId(), $dto->getLocale());
                break;
            case PageContentType::LISTING->value:
                $return['content'] = $this->formatContentListing(
                    $pageContent->getTypeId(),
                    $dto->getLocale(),
                    $dto->getPage(),
                    $dto->getLimit(),
                );
                $pageService = $this->getPageService();
                $translator = $this->getTranslator();
                $return['title'] = $translator->trans(
                    'page.content.listing.title',
                    parameters: ['category' => $pageService->getCategoryById($pageContent->getTypeId())],
                    domain: 'page',
                    locale: $dto->getLocale(),
                );
                break;
            default:
                break;
        }

        return $return;
    }

    /**
     * Formate un texte
     * @param string $text
     * @return string
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatContentText(string $text): string
    {
        return $this->renderMarkdown($text);
    }

    /**
     * Format un listing au format API
     * @param int $typeListing
     * @param string $locale
     * @param int $currentPage
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatContentListing(int $typeListing, string $locale, int $currentPage, int $limit): array
    {
        $return = [
            'pages' => [],
            'limit' => $limit,
            'current_page' => $currentPage,
        ];

        /** @var PageRepository $pageRepository */
        $pageRepository = $this->getRepository(Page::class);
        $listePages = $pageRepository->getPagesByCategoryPaginate($currentPage, $limit, $typeListing);

        foreach ($listePages as $page) {
            /** @var Page $page */
            $render = $page
                ->getUser()
                ->getOptionUserByKey(OptionUser::OU_DEFAULT_PERSONAL_DATA_RENDER->value)
                ->getValue();
            $personalData = new PersonalData($page->getUser(), $render);

            if ($page->getPageTranslations()->count() === 0) {
                continue;
            }

            $pageTranslation = $page->getPageTranslationByLocale($locale);
            $return['pages'][] = [
                'img' => $page->getHeaderImg(),
                'title' => $pageTranslation->getTitre(),
                'slug' => $pageTranslation->getUrl(),
                'category' => $page->getCategory(),
                'author' => $personalData->getPersonalData(),
                'created' => $page->getCreatedAt()->getTimestamp(),
                'update' => $page->getUpdateAt()->getTimestamp(),
            ];
        }
        $nb = $listePages->count();
        $return['rows'] = $nb;

        return $return;
    }

    /**
     * Format une FAQ pour les API
     * @param int $idFaq
     * @param string $locale
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function formatContentFAq(int $idFaq, string $locale): array
    {
        /** @var Faq $faq */
        $faq = $this->findOneByCriteria(Faq::class, ['id' => $idFaq, 'disabled' => false]);
        if (!$faq) {
            return [];
        }

        $return = [
            'title' => $faq->getFaqTranslationByLocale($locale)->getTitle(),
            'categories' => [],
        ];

        foreach ($faq->getFaqCategories() as $faqCategory) {
            /** @var FaqCategory $category */
            if ($faqCategory->isDisabled()) {
                continue;
            }

            $category = [
                'title' => $faqCategory->getFaqCategoryTranslationByLocale($locale)->getTitle(),
                'questions' => [],
            ];

            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {
                if ($faqQuestion->isDisabled()) {
                    continue;
                }

                $category['questions'][] = [
                    'title' => $faqQuestion->getFaqQuestionTranslationByLocale($locale)->getTitle(),
                    'answer' => $this->getMarkdownEditorService()->parseMarkdown(
                        $faqQuestion->getFaqQuestionTranslationByLocale($locale)->getAnswer(),
                    ),
                ];
            }
            $return['categories'][] = $category;
        }
        return $return;
    }
}
