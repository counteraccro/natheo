<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de modification d'une FAQ, un champ absent restant inchangé
 */

namespace App\Api\V2\Input\Admin\Content;

use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;

#[ValidFaqInput]
final class UpdateFaqInput
{
    public ?bool $disabled = null;

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
        return $this->disabled !== null && $this->translations === null;
    }
}
