<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Données de démarrage de l'application Vue du back-office : session API, utilisateur, site, liens, options et textes
 */

namespace App\Service\Admin\App;

use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Admin\System\Options\OptionUser;
use App\Service\Admin\AppAdminHandlerService;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\Dev\DevInfoService;
use App\Service\Admin\System\OptionUserService;
use App\Service\Api\System\TranslationCatalogueService;
use App\Service\Api\System\User\UserSessionAccessToken;
use App\Service\Api\System\User\UserSessionService;
use App\Service\Global\User\AvatarUrl;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\Impersonate\ImpersonateUrlGenerator;

class AdminAppBootstrapService extends AppAdminService
{
    /**
     * Domaines de traduction envoyés avec la page, pour le premier affichage
     * @var string[]
     */
    public const array DOMAINS = ['admin_ui', 'admin_app'];

    public function __construct(
        #[AutowireLocator(AppAdminHandlerService::HANDLERS)] ContainerInterface $handlers,
        private readonly UserSessionService $userSessionService,
        private readonly OptionUserService $optionUserService,
        private readonly DevInfoService $devInfoService,
        private readonly TranslationCatalogueService $catalogueService,
        #[
            Autowire(service: 'security.impersonate_url_generator'),
        ]
        private readonly ImpersonateUrlGenerator $impersonateUrlGenerator,
    ) {
        parent::__construct($handlers);
    }

    /**
     * Données de démarrage pour l'utilisateur connecté
     * @param Request $request
     * @return array<string, mixed>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBootstrap(Request $request): array
    {
        /** @var User $user */
        $user = $this->getSecurity()->getUser();
        $impersonator = $this->getImpersonator();
        $locale = $request->getLocale();
        $router = $this->getRouter();
        $optionSystem = $this->getOptionSystemService();
        $parameters = ['_locale' => $locale];

        return [
            'api' => [
                'prefix' => $this->getParameterBag()->get('app.api_v2.prefix'),
                ...$this->formatAccessToken($this->getAccessToken($request)),
                'tokenUrl' => $router->generate('admin_app_token', $parameters),
            ],
            'locale' => $locale,
            'locales' => $this->getSupportedLocales(),
            'basePath' => rtrim($router->generate('admin_app', $parameters), '/') . '/',
            'user' => [
                'id' => $user->getId(),
                'login' => $user->getLogin(),
                'name' => $this->getDisplayName($user),
                'initials' => $this->getInitials($user),
                'email' => $user->getEmail(),
                'avatar' => AvatarUrl::file($user->getAvatar(), $this->getParameterBag()->get('app.path.avatar')),
                'roles' => $user->getRoles(),
                'impersonating' => $impersonator === null ? null : $this->getDisplayName($impersonator),
            ],
            'site' => [
                'name' => $optionSystem->getValueByKey(OptionSystem::OS_SITE_NAME->value),
                'version' => $this->getParameterBag()->get('app.version'),
                'url' => $router->generate('front_no_local'),
            ],
            'urls' => [
                'dashboard' => $router->generate('admin_dashboard_index', $parameters),
                'logout' => $router->generate('auth_logout', $parameters),
                'impersonationExit' =>
                    $impersonator === null
                        ? null
                        : $this->impersonateUrlGenerator->generateExitPath(
                            $router->generate('admin_dashboard_index', $parameters),
                        ),
                'myAccount' => $router->generate('admin_user_my_account', $parameters),
                'myOptions' => $router->generate('admin_user_my_option', $parameters),
                'search' => $router->generate('admin_search_index', $parameters),
                'notifications' => $router->generate('admin_notification_index', $parameters),
            ],
            'options' => [
                'notifications' => $optionSystem->getValueByKey(OptionSystem::OS_NOTIFICATION->value) === '1',
                'confirmLeave' => $optionSystem->getValueByKey(OptionSystem::OS_CONFIRM_LEAVE_FORM->value) === '1',
                'itemsPerPage' => (int) $this->optionUserService->getValueByKey(OptionUser::OU_NB_ELEMENT->value),
            ],
            'messages' => $this->getMessages($locale),
            'debug' => $this->getDebug(),
        ];
    }

    /**
     * Access token de la session API du back-office, renouvelé ou ouvert si besoin
     * @param Request $request
     * @return UserSessionAccessToken
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getAccessToken(Request $request): UserSessionAccessToken
    {
        /** @var User $user */
        $user = $this->getSecurity()->getUser();
        return $this->userSessionService->getBackOfficeAccessToken(
            $user,
            $this->getImpersonator(),
            $request->getSession(),
            $request->headers->get('User-Agent'),
            $request->getClientIp(),
        );
    }

    /**
     * Access token au format de la réponse JSON
     * @param UserSessionAccessToken $accessToken
     * @return array{accessToken: string, expiresAt: string}
     */
    public function formatAccessToken(UserSessionAccessToken $accessToken): array
    {
        return [
            'accessToken' => $accessToken->token,
            'expiresAt' => $accessToken->expiresAt->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * Utilisateur d'origine pendant une usurpation
     * @return User|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getImpersonator(): ?User
    {
        $token = $this->getSecurity()->getToken();
        $original = $token instanceof SwitchUserToken ? $token->getOriginalToken()->getUser() : null;
        return $original instanceof User ? $original : null;
    }

    /**
     * Prénom et nom, l'identifiant à défaut
     * @param User $user
     * @return string
     */
    private function getDisplayName(User $user): string
    {
        $name = trim(($user->getFirstname() ?? '') . ' ' . ($user->getLastname() ?? ''));
        return $name !== '' ? $name : (string) $user->getLogin();
    }

    /**
     * Initiales du prénom et du nom, première lettre de l'identifiant à défaut
     * @param User $user
     * @return string
     */
    private function getInitials(User $user): string
    {
        $initials = mb_substr($user->getFirstname() ?? '', 0, 1) . mb_substr($user->getLastname() ?? '', 0, 1);
        return mb_strtoupper($initials !== '' ? $initials : mb_substr((string) $user->getLogin(), 0, 1));
    }

    /**
     * Messages bruts (ICU non formaté) des domaines envoyés avec la page, en objet JSON même vides
     * @param string $locale
     * @return array<string, object>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getMessages(string $locale): array
    {
        $messages = [];
        foreach (self::DOMAINS as $domain) {
            $messages[$domain] = (object) $this->catalogueService->getMessages($domain, $locale);
        }
        return $messages;
    }

    /**
     * Informations de la barre de debug, en environnement dev seulement
     * @return array<string, mixed>|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDebug(): ?array
    {
        if ($this->getParameterBag()->get('kernel.environment') !== 'dev') {
            return null;
        }
        return [...$this->devInfoService->getInfo(), 'users' => $this->devInfoService->getLoginUsers()];
    }
}
