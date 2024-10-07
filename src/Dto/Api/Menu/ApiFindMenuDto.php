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

        /*#[Assert\NotBlank(
            message: 'Le paramètre pageSlug ne peux pas être vide'
        )]*/
        #[Assert\Type('string')]
        public readonly string $pageSlug = 'default'
    )
    {

    }
}
