<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Valide ValidTagInput, messages du domaine tag rattachés au champ en erreur
 */

namespace App\Api\V2\Validator\Admin\Content;

use App\Api\V2\Input\Admin\Content\CreateTagInput;
use App\Api\V2\Input\Admin\Content\UpdateTagInput;
use App\Api\V2\Input\Admin\Global\LabelTranslationInput;
use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Entity\Admin\Content\Tag\Tag;
use App\Service\Api\Admin\Content\ApiAdminTagService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

final class ValidTagInputValidator extends ConstraintValidator
{
    /**
     * Longueur maximale d'un libellé
     * @var int
     */
    private const int LABEL_MAX_LENGTH = 255;

    public function __construct(
        private readonly ApiAdminTagService $tagService,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($constraint instanceof ValidTagInput)) {
            throw new UnexpectedTypeException($constraint, ValidTagInput::class);
        }
        if (!($value instanceof CreateTagInput) && !($value instanceof UpdateTagInput)) {
            return;
        }

        if ($value->color !== null && preg_match(Tag::COLOR_PATTERN, $value->color) !== 1) {
            $this->addViolation(t('tag.error.color', domain: 'tag'), 'color');
        }
        if ($value instanceof CreateTagInput && $value->translations === []) {
            $this->addViolation(t('tag.error.translations_required', domain: 'tag'), 'translations');
        }
        $this->validateTranslations($value->translations ?? []);
    }

    /**
     * @param LabelTranslationInput[] $translations
     * @return void
     */
    private function validateTranslations(array $translations): void
    {
        $locales = $this->tagService->getLocales();
        $excludedTagId = $this->getEditedTagId();
        $seen = [];
        foreach (array_values($translations) as $i => $translation) {
            $path = 'translations[' . $i . ']';
            if (!in_array($translation->locale, $locales, true)) {
                $this->addViolation(
                    t('tag.error.locale', ['{locale}' => $translation->locale], 'tag'),
                    $path . '.locale',
                );
                continue;
            }
            if (isset($seen[$translation->locale])) {
                $this->addViolation(
                    t('tag.error.locale_duplicate', ['{locale}' => $translation->locale], 'tag'),
                    $path . '.locale',
                );
                continue;
            }
            $seen[$translation->locale] = true;

            $label = trim($translation->label);
            if ($label === '') {
                $this->addViolation(t('tag.error.label.blank', domain: 'tag'), $path . '.label');
            } elseif (mb_strlen($label) > self::LABEL_MAX_LENGTH) {
                $this->addViolation(t('tag.error.label.length', domain: 'tag'), $path . '.label');
            } elseif ($this->tagService->findByLabel($translation->locale, $label, $excludedTagId) !== null) {
                $this->addViolation(
                    t('tag.error.duplicate_label', ['{label}' => $label], 'tag'),
                    $path . '.label',
                );
            }
        }
    }

    /**
     * Identifiant du tag modifié, lu par le provider avant la désérialisation de l'entrée
     * @return int|null
     */
    private function getEditedTagId(): ?int
    {
        $previous = $this->requestStack->getCurrentRequest()?->attributes->get('previous_data');
        return $previous instanceof AdminTag ? $previous->id : null;
    }

    /**
     * @param TranslatableMessage $message
     * @param string $path
     * @return void
     */
    private function addViolation(TranslatableMessage $message, string $path): void
    {
        $this->context
            ->buildViolation($message->getMessage(), $message->getParameters())
            ->setTranslationDomain($message->getDomain())
            ->atPath($path)
            ->addViolation();
    }
}
