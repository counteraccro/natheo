<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Object de Transfère de Données pour la recherche de menu via API
 */
namespace App\Dto\Api\Menu;

use App\Utils\Content\Menu\MenuConst;
use Symfony\Component\Validator\Constraints as Assert;

class ApiFindMenuDto
{
    /**
     * Tableau de positions
     */
    private const MENU_POSITIONS = [
        MenuConst::POSITION_HEADER,
        MenuConst::POSITION_RIGHT,
        MenuConst::POSITION_FOOTER,
        MenuConst::POSITION_LEFT
    ];

    public function __construct(

        /*#[Assert\NotBlank(
            message: 'Le paramètre pageSlug ne peux pas être vide'
        )]*/
        #[Assert\Type('string')]
        public readonly string $pageSlug,

        #[Assert\Choice(choices: self::MENU_POSITIONS, message: 'Choose a position between 1 (top), 2 (right), 3 (bottom), 4 (left)')]
        public readonly int $position
    )
    {

    }
}
