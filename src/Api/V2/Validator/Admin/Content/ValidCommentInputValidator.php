<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Vérifie le texte et la raison de la modération d'un commentaire, messages du domaine comment
 */

namespace App\Api\V2\Validator\Admin\Content;

use App\Api\V2\Input\Admin\Content\UpdateCommentInput;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

final class ValidCommentInputValidator extends ConstraintValidator
{
    /**
     * Longueur maximale de la raison de la modération
     * @var int
     */
    public const int MODERATION_COMMENT_MAX_LENGTH = 1000;

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($constraint instanceof ValidCommentInput)) {
            throw new UnexpectedTypeException($constraint, ValidCommentInput::class);
        }
        if (!($value instanceof UpdateCommentInput)) {
            return;
        }

        if ($value->comment !== null && trim($value->comment) === '') {
            $this->addViolation(t('comment.error.comment_required', domain: 'comment'), 'comment');
        }
        if (
            $value->moderationComment !== null &&
            mb_strlen($value->moderationComment) > self::MODERATION_COMMENT_MAX_LENGTH
        ) {
            $this->addViolation(
                t('comment.error.moderation_comment_length', ['max' => self::MODERATION_COMMENT_MAX_LENGTH], 'comment'),
                'moderationComment',
            );
        }
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
