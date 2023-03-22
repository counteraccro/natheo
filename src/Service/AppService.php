<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service global
 */
namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppService
{
    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var RequestStack
     */
    protected RequestStack $requestStack;

    /**
     * @var Security
     */
    protected Security $security;

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, Security $security)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }
}