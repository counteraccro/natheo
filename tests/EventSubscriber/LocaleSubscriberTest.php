<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests du choix de la langue de l'interface par LocaleSubscriber
 */

namespace App\Tests\EventSubscriber;

use App\EventListener\ApiLocaleListener;
use App\EventSubscriber\LocaleSubscriber;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Service\Global\Database\DataBase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\LocaleSwitcher;

class LocaleSubscriberTest extends TestCase
{
    /**
     * @var OptionUserService&Stub
     */
    private OptionUserService&Stub $optionUserService;

    /**
     * @var LocaleSwitcher&MockObject
     */
    private LocaleSwitcher&MockObject $localeSwitcher;

    /**
     * @var Security&Stub
     */
    private Security&Stub $security;

    /**
     * @var OptionSystemService&Stub
     */
    private OptionSystemService&Stub $optionSystemService;

    /**
     * @var DataBase&Stub
     */
    private DataBase&Stub $dataBase;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->optionUserService = $this->createStub(OptionUserService::class);
        $this->localeSwitcher = $this->createMock(LocaleSwitcher::class);
        $this->security = $this->createStub(Security::class);
        $this->optionSystemService = $this->createStub(OptionSystemService::class);
        $this->optionSystemService->method('getDefaultLanguage')->willReturn('en');
        $this->dataBase = $this->createStub(DataBase::class);
        $this->dataBase->method('check')->willReturn(['success' => true]);
        $this->dataBase->method('isTableExiste')->willReturn(true);
    }

    /**
     * Utilisateur connecté avec une langue choisie : sa langue est appliquée
     * @return void
     */
    public function testUserLanguage(): void
    {
        $this->security->method('isGranted')->willReturn(true);
        $this->optionUserService->method('getValueByKey')->willReturn('es');
        $this->localeSwitcher->expects($this->once())->method('setLocale')->with('es');

        $this->createSubscriber()->onKernelController($this->createEvent(new Request()));
    }

    /**
     * Utilisateur connecté sans langue choisie : langue par défaut du site
     * @return void
     */
    public function testEmptyUserLanguageFallsBackToSiteDefault(): void
    {
        $this->security->method('isGranted')->willReturn(true);
        $this->optionUserService->method('getValueByKey')->willReturn('');
        $this->localeSwitcher->expects($this->once())->method('setLocale')->with('en');

        $this->createSubscriber()->onKernelController($this->createEvent(new Request()));
    }

    /**
     * Visiteur anonyme : langue par défaut du site, sans lire les options utilisateur
     * @return void
     */
    public function testAnonymousUsesSiteDefault(): void
    {
        $this->security->method('isGranted')->willReturn(false);
        $this->optionUserService = $this->createMock(OptionUserService::class);
        $this->optionUserService->expects($this->never())->method('getValueByKey');
        $this->localeSwitcher->expects($this->once())->method('setLocale')->with('en');

        $this->createSubscriber()->onKernelController($this->createEvent(new Request()));
    }

    /**
     * Base absente : français pendant l'installation
     * @return void
     */
    public function testNotInstalledUsesFrench(): void
    {
        $this->dataBase = $this->createStub(DataBase::class);
        $this->dataBase->method('check')->willReturn(['success' => false]);
        $this->localeSwitcher->expects($this->once())->method('setLocale')->with('fr');

        $this->createSubscriber()->onKernelController($this->createEvent(new Request()));
    }

    /**
     * Requête de l'API v2 : langue déjà fixée, rien n'est modifié
     * @return void
     */
    public function testApiV2RequestIsIgnored(): void
    {
        $request = new Request();
        $request->attributes->set(ApiLocaleListener::ATTRIBUTE, 'es');
        $this->localeSwitcher->expects($this->never())->method('setLocale');

        $this->createSubscriber()->onKernelController($this->createEvent($request));
    }

    /**
     * @return LocaleSubscriber
     */
    private function createSubscriber(): LocaleSubscriber
    {
        return new LocaleSubscriber(
            $this->optionUserService,
            $this->localeSwitcher,
            $this->security,
            $this->optionSystemService,
            $this->dataBase,
        );
    }

    /**
     * @param Request $request
     * @return ControllerEvent
     */
    private function createEvent(Request $request): ControllerEvent
    {
        return new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            static fn() => null,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }
}
