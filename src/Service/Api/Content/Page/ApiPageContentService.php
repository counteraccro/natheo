<?php
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
use App\Repository\Admin\Content\Page\PageRepository;
use App\Service\Api\AppApiService;
use App\Utils\Content\Page\PageConst;
use App\Utils\Markdown;
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
    public function getPageContentForApi(ApiFindPageContentDto $dto, User $user = null): ?array
    {
        $pageContent = $this->findOneById(PageContent::class, $dto->getId());
        if (empty($pageContent)) {
            return [];
        }

        $pageContent = $this->getFormatContent($pageContent, $dto);
        if (empty($pageContent['content'])) {
            return [];
        }

        return $pageContent;
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
        $return = [];

        switch ($pageContent->getType()) {
            case PageConst::CONTENT_TYPE_TEXT:
                $return['content'] = $this->formatContentText($pageContent->getPageContentTranslationByLocale($dto->getLocale())->getText());
                break;
            case PageConst::CONTENT_TYPE_FAQ:
                $return['content'] = $this->formatContentFAq($pageContent->getTypeId(), $dto->getLocale());
                break;
            case PageConst::CONTENT_TYPE_LISTING:
                $return['content'] = $this->formatContentListing($pageContent->getTypeId(), $dto->getLocale(), $dto->getPage(), $dto->getLimit());
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
     */
    private function formatContentText(string $text): string
    {
        $markdown = new Markdown();
        return $markdown->convertMarkdownToHtml($text);
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

        foreach($listePages as $page)
        {
            /** @var Page $page */

            $pageTranslation = $page->getPageTranslationByLocale($locale);
            $return['pages'][] = [
                'title' => $pageTranslation->getTitre(),
                'slug' => $pageTranslation->getUrl(),
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
        $faq = $this->findOneById(Faq::class, $idFaq);

        $return = [
            'title' => $faq->getFaqTranslationByLocale($locale)->getTitle(),
            'categories' => []
        ];

        foreach ($faq->getFaqCategories() as $faqCategory) {

            /** @var FaqCategory $category */
            if ($faqCategory->isDisabled()) {
                continue;
            }

            $category = [
                'title' => $faqCategory->getFaqCategoryTranslationByLocale($locale)->getTitle(),
                'questions' => []
            ];

            foreach ($faqCategory->getFaqQuestions() as $faqQuestion) {

                if ($faqQuestion->isDisabled()) {
                    continue;
                }

                $category['questions'][] = [
                    'title' => $faqQuestion->getFaqQuestionTranslationByLocale($locale)->getTitle(),
                    'answer' => $faqQuestion->getFaqQuestionTranslationByLocale($locale)->getAnswer()
                ];
            }
            $return['categories'][] = $category;
        }
        return $return;
    }
}
