<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class globale pour les extensions Twig
 */
namespace App\Twig\Runtime\Admin;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminAppExtension
{
    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }
}