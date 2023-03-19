<?php

namespace App\EventSubscriber;

use App\Entity\Admin\OptionUser;
use App\Service\Admin\OptionSystemService;
use App\Service\Admin\OptionUserService;
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
     * @param OptionUserService $optionUserService
     * @param LocaleSwitcher $localeSwitcher
     */
    public function __construct(OptionUserService $optionUserService, LocaleSwitcher $localeSwitcher)
    {
        $this->optionUserService = $optionUserService;
        $this->localeSwitcher = $localeSwitcher;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        /** @var OptionUser $option */
       $option = $this->optionUserService->getByKey(OptionUserService::OU_DEFAULT_LANGUAGE);
       $this->localeSwitcher->setLocale($option->getValue());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
