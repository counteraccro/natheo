<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppFormType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected AuthorizationCheckerInterface $authorizationChecker;

    /**
     * @param TranslatorInterface $translator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
    }
}
