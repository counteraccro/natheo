<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Enum\Admin\System\Options\OptionSystem;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Twig\Environment;

#[AsEventListener(event: 'kernel.request', priority: 0)]
class TwigGlobalListener
{
    public function __construct(private readonly Environment $twig) {}

    public function __invoke(): void
    {
        $this->twig->addGlobal('os_site_name', OptionSystem::OS_SITE_NAME->value);
        $this->twig->addGlobal('os_confirm_leave_form', OptionSystem::OS_CONFIRM_LEAVE_FORM->value);
        $this->twig->addGlobal('os_adresse_site', OptionSystem::OS_ADRESSE_SITE->value);
        $this->twig->addGlobal('os_notification', OptionSystem::OS_NOTIFICATION->value);
    }
}
