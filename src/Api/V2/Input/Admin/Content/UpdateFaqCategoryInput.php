<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de modification d'une catégorie de FAQ, un champ absent restant inchangé
 */

namespace App\Api\V2\Input\Admin\Content;

use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidFaqInput]
final class UpdateFaqCategoryInput
{
    public ?bool $disabled = null;

    /**
     * Position parmi les catégories de la FAQ, à partir de 1 ; au-delà de la dernière, placée en dernier
     */
    #[Assert\Positive]
    public ?int $position = null;

    /**
     * Titres modifiés, une langue absente garde son titre
     * @var TitleTranslationInput[]|null
     */
    public ?array $translations = null;

    /**
     * Vrai quand seul le statut est modifié
     * @return bool
     */
    public function isStatusOnly(): bool
    {
        return $this->disabled !== null && $this->translations === null && $this->position === null;
    }
}
