<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de création d'une question de FAQ, ajoutée en dernière position de sa catégorie
 */

namespace App\Api\V2\Input\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidFaqInput]
final class CreateFaqQuestionInput
{
    /**
     * Catégorie de la question, par son IRI (/api/v2/admin/faq_categories/{id})
     */
    #[ApiProperty(required: true)]
    #[Assert\NotNull]
    public ?AdminFaqCategory $category = null;

    public bool $disabled = false;

    /**
     * Titre et réponse de chaque langue du site
     * @var FaqQuestionTranslationInput[]
     */
    #[ApiProperty(required: true)]
    public array $translations = [];
}
