<?php

namespace App\Utils\Api\Content;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Utils\Content\Page\PageConst;
use App\Utils\Markdown;
use Doctrine\Common\Collections\Collection;

class ApiPageFormater
{
    private array $return;

    public function __construct(
        private readonly Page           $page,
        private readonly ApiFindPageDto $dto
    )
    {
    }

    /**
     * Convertie une page au format API
     * @return $this
     */
    public function convertPage(): ApiPageFormater
    {
        $pageTranslation = $this->page->getPageTranslationByLocale($this->dto->getLocale());

        $this->return['title'] = $pageTranslation->getTitre();
        $this->return['render'] = $this->page->getRender();
        $this->return['contents'] = $this->getPageContent($this->page->getPageContents());

        return $this;
    }

    /**
     * Retourne les pageContents d'une page
     * @param Collection $pageContents
     * @return array
     */
    private function getPageContent(Collection $pageContents): array
    {
        $return = [];
        foreach ($pageContents as $pageContent) {
            /** @var PageContent $pageContent */

            $content = $pageContent->getType();
            if($pageContent->getType() === PageConst::CONTENT_TYPE_TEXT)
            {
                $pageContentTranslation = $pageContent->getPageContentTranslationByLocale($this->dto->getLocale());
                $markdownConverter = new Markdown();
                $content = $markdownConverter->convertMarkdownToHtml($pageContentTranslation->getText());
            }

            $return[] = [
                'order' => $pageContent->getRenderBlock(),
                'content' => $content
            ];
        }
        return $return;
    }

    /**
     * Retourne une page au format API
     * @return array
     */
    public function getPageForApi(): array
    {
        return $this->return;
    }
}
