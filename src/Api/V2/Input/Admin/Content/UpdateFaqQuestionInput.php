<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de modification d'une question de FAQ, un champ absent restant inchangé
 */

namespace App\Api\V2\Input\Admin\Content;

use App\Api\V2\Input\Admin\StatusInputInterface;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidFaqInput]
final class UpdateFaqQuestionInput implements StatusInputInterface
{
    public ?bool $disabled = null;

    /**
     * Catégorie de la même FAQ vers laquelle déplacer la question, par son IRI
     */
    public ?AdminFaqCategory $category = null;

    /**
     * Position dans la catégorie, à partir de 1 ; au-delà de la dernière, placée en dernier
     */
    #[Assert\Positive]
    public ?int $position = null;

    /**
     * Titres et réponses modifiés, une langue absente reste inchangée
     * @var FaqQuestionTranslationInput[]|null
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
        return $this->disabled !== null &&
            $this->translations === null &&
            $this->position === null &&
            $this->category === null;
    }
}
