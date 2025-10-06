<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Force la locale en fonction des options du user
 */
namespace App\EventSubscriber;

use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Utils\Global\Database\DataBase;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use Doctrine\DBAL\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @var DataBase
     */
    private DataBase $dataBase;

    /**
     * @param OptionUserService $optionUserService
     * @param LocaleSwitcher $localeSwitcher
     * @param Security $security
     * @param OptionSystemService $optionSystemService
     * @param DataBase $dataBase
     */
    public function __construct(
        OptionUserService $optionUserService,
        LocaleSwitcher $localeSwitcher,
        Security $security,
        OptionSystemService $optionSystemService,
        DataBase $dataBase,
    ) {
        $this->optionUserService = $optionUserService;
        $this->localeSwitcher = $localeSwitcher;
        $this->security = $security;
        $this->optionSystemService = $optionSystemService;
        $this->dataBase = $dataBase;
    }

    /**
     * @param ControllerEvent $event
     * @return void
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function onKernelController(ControllerEvent $event): void
    {
        // Lors de l'installation si pas de bdd pour éviter les crashs
        if (!$this->dataBase->isConnected() || !$this->dataBase->isTableExiste()) {
            $this->localeSwitcher->setLocale('fr');
            return;
        }

        if ($this->security->isGranted('ROLE_USER')) {
            $locales = $this->optionUserService->getValueByKey(OptionUserKey::OU_DEFAULT_LANGUAGE);
        } else {
            $locales = $this->optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE);
        }

        // TODO à faire de façon plus propre
        if ($locales === null) {
            $locales = 'fr';
        }

        $this->localeSwitcher->setLocale($locales);
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
