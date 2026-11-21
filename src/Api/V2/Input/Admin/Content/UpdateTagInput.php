<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de modification d'un tag, un champ absent restant inchangé
 */

namespace App\Api\V2\Input\Admin\Content;

use App\Api\V2\Input\Admin\Global\LabelTranslationInput;
use App\Api\V2\Validator\Admin\Content\ValidTagInput;

#[ValidTagInput]
final class UpdateTagInput
{
    /**
     * Couleur #rrggbb
     */
    public ?string $color = null;

    public ?bool $disabled = null;

    /**
     * Libellés modifiés, une langue absente garde son libellé
     * @var LabelTranslationInput[]|null
     */
    public ?array $translations = null;

    /**
     * Vrai quand seul le statut est modifié
     * @return bool
     */
    public function isStatusOnly(): bool
    {
        return $this->disabled !== null && $this->color === null && $this->translations === null;
    }
}
