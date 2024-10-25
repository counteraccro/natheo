<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service correctement formaté les pageContents d'une page pour l'API
 */

namespace App\Service\Api\Content\Page;

use App\Utils\Content\Page\PageConst;
use App\Utils\Markdown;
use League\CommonMark\Exception\CommonMarkException;

class ApiPageContentService
{

    /**
     * Formate au format API les pageContents
     * @param array $pageContents
     * @return array
     * @throws CommonMarkException
     */
    public function getFormatContent(array $pageContents): array
    {
        foreach ($pageContents as &$content) {
            switch ($content['type']) {
                case PageConst::CONTENT_TYPE_TEXT:
                    $content['content'] = $this->formatContentText($content['content']);
            }
        }
        return $pageContents;
    }

    /**
     * @param string $text
     * @return string
     * @throws CommonMarkException
     */
    private function formatContentText(string $text): string
    {
        $markdown = new Markdown();
        return $markdown->convertMarkdownToHtml($text);
    }
}
