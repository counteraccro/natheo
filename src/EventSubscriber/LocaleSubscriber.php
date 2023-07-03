<?php

namespace App\EventSubscriber;

use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\LocaleSwitcher;

class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    /**
     * @var LocaleSwitcher
     */
    private LocaleSwitcher $localeSwitcher;

    /**
     * @var Security
     */
    private Security $security;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @param OptionUserService $optionUserService
     * @param LocaleSwitcher $localeSwitcher
     * @param Security $security
     * @param OptionSystemService $optionSystemService
     */
    public function __construct(OptionUserService $optionUserService, LocaleSwitcher $localeSwitcher,
                                Security          $security, OptionSystemService $optionSystemService
    )
    {
        $this->optionUserService = $optionUserService;
        $this->localeSwitcher = $localeSwitcher;
        $this->security = $security;
        $this->optionSystemService = $optionSystemService;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if ($this->security->isGranted('ROLE_USER')) {
            $locales = $this->optionUserService->getValueByKey(OptionUserKey::OU_DEFAULT_LANGUAGE);
        } else {
            $locales = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
        }
        $this->localeSwitcher->setLocale($locales);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
