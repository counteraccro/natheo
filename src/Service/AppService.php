<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service global
 */
namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class AppService
{
    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
}