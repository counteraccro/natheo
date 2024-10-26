<?php

namespace App\Utils\Api\Content;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Page\PageContent;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Utils\Content\Page\PageConst;
use App\Utils\Content\Page\PageStatistiqueKey;
use App\Utils\Markdown;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\PersonalData;
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
        $this->return['author'] = $this->getAuthor($this->page->getUser());
        $this->return['created'] = $this->page->getCreatedAt()->getTimestamp();
        $this->return['update'] = $this->page->getUpdateAt()->getTimestamp();
        $this->return['tags'] = $this->getTags($this->page->getTags());
        $this->return['statistiques'] = $this->getStatistiques();
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

            $type = $pageContent->getType();
            $content = '';
            if($pageContent->getType() === PageConst::CONTENT_TYPE_TEXT)
            {
                $pageContentTranslation = $pageContent->getPageContentTranslationByLocale($this->dto->getLocale());
                $content = $pageContentTranslation->getText();
            }

            $return[] = [
                'order' => $pageContent->getRenderBlock(),
                'content' => $content,
                'type' => $type
            ];
        }
        return $return;
    }

    /**
     * Retourne l'auteur correctement formaté en fonction de ses options
     * @param User $user
     * @return string
     */
    private function getAuthor(User $user): string
    {
        $render = $user->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue();

        $personalData = new PersonalData($user, $render);
        return $personalData->getPersonalData();
    }

    /**
     * Retourne un tableau de tag
     * @param Collection $tags
     * @return array
     */
    private function getTags(Collection $tags): array
    {
        $return = [];
        foreach ($tags as $tag) {
            /** @var Tag $tag */
            $return[] = [
                'label' => $tag->getTagTranslationByLocale($this->dto->getLocale())->getLabel(),
                'color' => $tag->getColor(),
                'url' => 'TODO A FAIRE'
            ];
        }

        return $return;
    }

    /**
     * Retourne les statistiques de la page
     * @return array
     */
    private function getStatistiques(): array
    {
        return  [
            PageStatistiqueKey::KEY_PAGE_NB_READ => $this->page->getPageStatistiqueByKey(PageStatistiqueKey::KEY_PAGE_NB_READ)->getValue()
            ];

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
