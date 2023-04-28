<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppFormType extends AbstractType
{

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}
