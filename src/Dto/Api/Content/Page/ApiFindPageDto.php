<?php

namespace App\Dto\Api\Content\Page;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ApiFindPageDto
{
    public function __construct(

        /**
         * slug de la page
         * @var string
         */
        #[Assert\Type(type: 'string', message: 'The pageSlug parameter must be a string')]
        #[Assert\NotNull(message: 'The pageSlug parameter cannot be empty')]
        private string $slug,
    ) {}

    public function getSlug(): string {
        return $this->slug;
    }
}
