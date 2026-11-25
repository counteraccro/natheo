<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de création d'une catégorie de FAQ, ajoutée en dernière position
 */

namespace App\Api\V2\Input\Admin\Content;

use ApiPlatform\Metadata\ApiProperty;
use App\Api\V2\Input\Admin\Global\TitleTranslationInput;
use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Api\V2\Validator\Admin\Content\ValidFaqInput;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidFaqInput]
final class CreateFaqCategoryInput
{
    /**
     * FAQ de la catégorie, par son IRI (/api/v2/admin/faqs/{id})
     */
    #[ApiProperty(required: true)]
    #[Assert\NotNull]
    public ?AdminFaq $faq = null;

    public bool $disabled = false;

    /**
     * Titre de chaque langue du site
     * @var TitleTranslationInput[]
     */
    #[ApiProperty(required: true)]
    public array $translations = [];
}
