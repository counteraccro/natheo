<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de modification d'une FAQ, un champ absent restant inchangé
 */

namespace App\Api\V2\Input\Admin\Content;

use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Input\Admin\StatusInputInterface;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;

#[ValidFaqInput]
final class UpdateFaqInput implements StatusInputInterface
{
    public ?bool $disabled = null;

    /**
     * Titres modifiés, une langue absente garde son titre
     * @var TitleTranslationInput[]|null
     */
    public ?array $translations = null;

    /**
     * @return bool|null
     */
    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    /**
     * Vrai quand seul le statut est modifié
     * @return bool
     */
    public function isStatusOnly(): bool
    {
        return $this->disabled !== null && $this->translations === null;
    }
}
