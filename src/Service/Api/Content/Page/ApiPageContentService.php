<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service correctement formaté les pageContents d'une page pour l'API
 */

namespace App\Service\Api\Content\Page;

use App\Dto\Api\Content\Page\ApiFindPageContentDto;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\System\User;
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
     * @throws NotFoundExceptionInterface
     */
    public function getPageContentForApi(ApiFindPageContentDto $dto, User $user = null)
    {
        $pageContent = $this->findOneById(PageContent::class, $dto->getId());
        if(empty($pageContent)) {
            return [];
        }

        return [$pageContent->getId()];
    }

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
