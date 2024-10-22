<?php

namespace App\Utils\Api\Content;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Entity\Admin\Content\Page\Page;

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
    public function convertPage()
    {
        $pageTranslation = $this->page->getPageTranslationByLocale($this->dto->getLocale());

        $this->return['title'] = $pageTranslation->getTitre();
        return $this;
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
