<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.2
 * Données de démarrage de l'application Vue du back-office : session API, utilisateur, site, liens, options et textes
 */

namespace App\Service\Admin\App;

use App\Entity\Admin\System\User;
use App\Enum\Admin\AdminTemplate;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Admin\System\Options\OptionUser;
use App\Enum\Admin\System\User\Role;
use App\Enum\Front\Template;
use App\Service\Admin\AppAdminHandlerService;
use App\Service\Admin\AppAdminService;
use App\Service\Admin\Dev\DevInfoService;
use App\Service\Admin\System\OptionUserService;
use App\Service\Api\System\TranslationCatalogueService;
use App\Service\Api\System\User\UserSessionAccessToken;
use App\Service\Api\System\User\UserSessionService;
use App\Service\Global\User\AvatarUrl;
use Pentatrion\ViteBundle\Exception\EntrypointNotFoundException;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
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
        private readonly RoleHierarchyInterface $roleHierarchy,
        private readonly EntrypointsLookupCollection $entrypointsLookups,
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
                'roles' => $this->roleHierarchy->getReachableRoleNames($user->getRoles()),
                'role' => $this->getRoleLabel($user->getRoles()),
                'impersonating' => $impersonator === null ? null : $this->getDisplayName($impersonator),
            ],
            'site' => [
                'name' => $optionSystem->getValueByKey(OptionSystem::OS_SITE_NAME->value),
                'version' => $this->getParameterBag()->get('app.version'),
                'url' => $router->generate('front_no_local'),
                'theme' => $this->getSiteTheme(),
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
            'theme' => $this->getTheme()->value,
            'themes' => $this->getThemes(),
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
     * Thème du back-office de l'utilisateur, lu en base et non en session : l'API le modifie hors de la session PHP
     * @return AdminTemplate
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTheme(): AdminTemplate
    {
        return AdminTemplate::fromValueOrDefault(
            $this->optionUserService->getValueByKey(OptionUser::OU_ADMIN_THEME->value, false),
        );
    }

    /**
     * Thème actif du site : nom, classe et feuille de son texte (rendu du site dans l'éditeur), null s'il est inconnu
     * @return array{id: string, label: string, textClass: string, stylesheet: ?string}|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSiteTheme(): ?array
    {
        $optionSystem = $this->getOptionSystemService();
        $template = Template::tryFrom((string) $optionSystem->getValueByKey(OptionSystem::OS_THEME_FRONT_SITE->value));
        if ($template === null) {
            return null;
        }

        // Nom affiché dans la liste de l'option : natheo_horizon:Natheo-Horizon|…
        $label = $template->value;
        $config = $optionSystem->getOptionConfigByKey(OptionSystem::OS_THEME_FRONT_SITE->value);
        foreach (explode('|', (string) ($config['list_value'] ?? '')) as $item) {
            [$value, $name] = array_pad(explode(':', $item, 2), 2, '');
            if ($value === $template->value && $name !== '') {
                $label = $name;
            }
        }

        return [
            'id' => $template->value,
            'label' => $label,
            'textClass' => $template->getTextClass(),
            'stylesheet' => $this->getSiteThemeStylesheet($template),
        ];
    }

    /**
     * Feuille de texte d'un thème du site (entrée Vite front_<thème>_content), null s'il n'en a pas
     * @param Template $template
     * @return string|null
     */
    private function getSiteThemeStylesheet(Template $template): ?string
    {
        try {
            $files = $this->entrypointsLookups
                ->getEntrypointsLookup()
                ->getCSSFiles('front_' . $template->value . '_content');
        } catch (EntrypointNotFoundException) {
            return null;
        }
        return $files[0] ?? null;
    }

    /**
     * Thèmes du back-office proposés, avec leur nom traduit
     * @return array<int, array{id: string, label: string}>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getThemes(): array
    {
        $translator = $this->getTranslator();
        return array_map(
            fn(AdminTemplate $theme) => [
                'id' => $theme->value,
                'label' => $translator->trans('options_user.admin_theme.value.' . $theme->value),
            ],
            AdminTemplate::cases(),
        );
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
        $users = array_map(
            fn(array $user): array => [...$user, 'role' => $this->getRoleLabel([$user['role']])],
            $this->devInfoService->getLoginUsers(),
        );
        return [...$this->devInfoService->getInfo(), 'users' => $users];
    }

    /**
     * Nom traduit du rôle du back-office le plus élevé, le premier rôle brut à défaut
     * @param string[] $roles
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getRoleLabel(array $roles): string
    {
        $translator = $this->getTranslator();
        return match (Role::highest($roles)) {
            Role::SUPER_ADMIN => $translator->trans('admin_app.role.super_admin', domain: 'admin_app'),
            Role::ADMIN => $translator->trans('admin_app.role.admin', domain: 'admin_app'),
            Role::CONTRIBUTEUR => $translator->trans('admin_app.role.contributor', domain: 'admin_app'),
            Role::USER => $translator->trans('admin_app.role.user', domain: 'admin_app'),
            null => $roles[0] ?? '',
        };
    }
}
