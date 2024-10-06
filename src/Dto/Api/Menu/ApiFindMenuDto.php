<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour la recherche de menu via API
 */
namespace App\Dto\Api\Menu;

use Symfony\Component\Validator\Constraints as Assert;

class ApiFindMenuDto
{
    public function __construct(

        #[Assert\NotBlank]
        public readonly string $pageSlug
    )
    {

    }
}
