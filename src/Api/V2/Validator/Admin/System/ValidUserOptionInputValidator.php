<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Refuse une valeur absente, hors de la liste de l'option ou destinée à une option non modifiable
 */

namespace App\Api\V2\Validator\Admin\System;

use App\Api\V2\Input\Admin\System\UpdateUserOptionInput;
use App\Api\V2\Resource\Admin\System\AdminUserOption;
use App\Service\Api\Admin\System\ApiAdminUserOptionService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function Symfony\Component\Translation\t;

final class ValidUserOptionInputValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ApiAdminUserOptionService $userOptionService,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($constraint instanceof ValidUserOptionInput)) {
            throw new UnexpectedTypeException($constraint, ValidUserOptionInput::class);
        }
        $previous = $this->requestStack->getCurrentRequest()?->attributes->get('previous_data');
        if (!($value instanceof UpdateUserOptionInput) || !($previous instanceof AdminUserOption)) {
            return;
        }

        [, $config] = $this->userOptionService->findConfig($previous->id) ?? [null, null];
        if (
            $value->value === null ||
            $config === null ||
            !$this->userOptionService->isValidValue($config, $value->value)
        ) {
            $message = t('user.option.error.value', domain: 'user');
            $this->context
                ->buildViolation($message->getMessage(), $message->getParameters())
                ->setTranslationDomain($message->getDomain())
                ->atPath('value')
                ->addViolation();
        }
    }
}
