<?php
/**
 * Gestion des mots clés des emails
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\System\Mail;

use App\Entity\Admin\System\User;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KeyWord
{
    /**
     * Clé email de l'user
     * @var string
     */
    private const USER_EMAIL = 'user.email';

    /**
     * Clé login de l'user
     * @var string
     */
    private const USER_LOGIN = 'user.login';

    /**
     * Clé prénom de l'user
     * @var string
     */
    private const USER_FIRSTNAME = 'user.firstname';

    /**
     * Clé nom de l'user
     * @var string
     */
    private const USER_LASTNAME = 'user.lastname';

    /**
     * Clé du login de l'admin qui réalise l'action
     * @var string
     */
    private const ADMIN_LOGIN_ACTION = 'admin.login';

    /**
     * Clé nom du site
     * @var string
     */
    private const GLOBAL_SITE_NAME = 'global.site_name';

    /**
     * Clé url index site
     * @var string
     */
    private const GLOBAL_URL = 'global.url';

    /**
     * Clé temps validité lien
     * @var string
     */
    private const URL_TIME = 'url.time';

    /**
     * Clé url changement du mot de passe
     * @var string
     */
    private const GLOBAL_URL_CHANGE_PASSWORD = 'global.url_change_psw';

    /**
     * Clé pour les mots clés globaux
     * @var string
     */
    private const KEY_GLOBAL = 'global';

    /**
     * Clé pour les mots clés globaux
     * @var string
     */
    public const KEY_SEARCH = 'search';

    /**
     * Clé pour les mots clés globaux
     * @var string
     */
    public const KEY_REPLACE = 'replace';

    /**
     * Tableau contenant l'ensemble des keyWords de tous les emails
     * @var array|array[]
     */
    private array $tabKeyWord = [
        self::KEY_GLOBAL => [
            self::USER_EMAIL => '',
            self::USER_LOGIN => '',
            self::USER_FIRSTNAME => '',
            self::USER_LASTNAME => '',
            self::GLOBAL_URL => '',
            self::GLOBAL_SITE_NAME => '',
        ],
        MailKey::MAIL_CHANGE_PASSWORD => [
            self::GLOBAL_URL_CHANGE_PASSWORD => '',
        ],
        MailKey::MAIL_RESET_PASSWORD => [
            self::GLOBAL_URL_CHANGE_PASSWORD => '',
            self::ADMIN_LOGIN_ACTION => '',
            self::URL_TIME => '',
        ],
        MailKey::MAIL_ACCOUNT_ADM_DISABLE => [
            self::ADMIN_LOGIN_ACTION => '',
        ],
        MailKey::MAIL_ACCOUNT_ADM_ENABLE => [
            self::ADMIN_LOGIN_ACTION => '',
        ],
        MailKey::MAIL_CREATE_ACCOUNT_ADM => [
            self::GLOBAL_URL_CHANGE_PASSWORD => '',
            self::ADMIN_LOGIN_ACTION => '',
            self::URL_TIME => '',
        ],
        MailKey::MAIL_SELF_DISABLED_ACCOUNT => [
        ],
        MailKey::MAIL_SELF_DELETE_ACCOUNT => [
        ],
        MailKey::MAIL_SELF_ANONYMOUS_ACCOUNT => [
        ]
    ];

    /**
     * Tableau courant contenant les keyWord d'un email
     * @var array
     */
    private array $currentTabKeyWord;

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->currentTabKeyWord = $this->tabKeyWord[self::KEY_GLOBAL];
        if (isset($this->tabKeyWord[$key])) {
            $this->currentTabKeyWord = array_merge($this->currentTabKeyWord, $this->tabKeyWord[$key]);
        }

    }

    /**
     * Convertie en string les clés du tableau courant de keyWord
     * de la cle d'un email
     * @return string
     */
    public function getStringTabKeyWord(): string
    {
        return implode('|', array_keys($this->currentTabKeyWord));
    }

    /**
     * Renvoi sous la forme d'un tableau les keyWords + leurs valeurs associés
     * @param User $user
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    private function getGlobalKeyWord(User $user, OptionSystemService $optionSystemService): array
    {
        $url = $optionSystemService->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $siteName = $optionSystemService->getValueByKey(OptionSystemKey::OS_SITE_NAME);

        return [
            '[[' . self::USER_LOGIN . ']]' => $user->getLogin(),
            '[[' . self::USER_EMAIL . ']]' => $user->getEmail(),
            '[[' . self::USER_FIRSTNAME . ']]' => $user->getFirstname(),
            '[[' . self::USER_LASTNAME . ']]' => $user->getLastname(),
            '[[' . self::GLOBAL_URL . ']]' => $url,
            '[[' . self::GLOBAL_SITE_NAME . ']]' => $siteName,
        ];
    }

    /**
     * Format le retour sous la forme d'un tableau avec 2 clés search et replace
     * @param array $return
     * @return array
     */
    private function formatReturnValue(array $return): array
    {
        return [
            self::KEY_SEARCH => array_keys($return),
            self::KEY_REPLACE => array_values($return)
        ];
    }

    /**
     * Renvoi sous la forme d'un tableau le keyWord ADMIN_LOGIN_ACTION
     * @param User $user
     * @return array
     */
    private function getAdminLoginAction(User $user)
    {
        return [
            '[[' . self::ADMIN_LOGIN_ACTION . ']]' => $user->getLogin(),
        ];
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * change password
     * @param User $user
     * @param string $urlChangePassword
     * @param OptionSystemService $optionSystemService
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMailChangePassword(
        User                $user,
        string              $urlChangePassword,
        OptionSystemService $optionSystemService
    ): array
    {
        $url = $optionSystemService->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);

        $tab = $this->getGlobalKeyWord($user, $optionSystemService);
        $tab2 = [
            '[[' . self::GLOBAL_URL_CHANGE_PASSWORD . ']]' => $url . $urlChangePassword,
        ];
        $tab = array_merge($tab, $tab2);

        return $this->formatReturnValue($tab);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * disabled account admin
     * @param User $user
     * @param User $admin
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailAccountAdmDisabled(
        User                $user,
        User                $admin,
        OptionSystemService $optionSystemService
    ): array
    {
        $tab = $this->getGlobalKeyWord($user, $optionSystemService);
        $tab2 = $this->getAdminLoginAction($admin);
        $tab = array_merge($tab, $tab2);
        return $this->formatReturnValue($tab);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * enable account admin
     * @param User $user
     * @param User $admin
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailAccountAdmEnabled(
        User                $user,
        User                $admin,
        OptionSystemService $optionSystemService
    ): array
    {
        return $this->getTabMailAccountAdmDisabled($user, $admin, $optionSystemService);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * create new account adm
     * @param User $user
     * @param User $admin
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailCreateAccountAdm(
        User                $user,
        User                $admin,
        string              $url,
        OptionSystemService $optionSystemService
    ): array
    {
        return $this->getTabMailResetPassword($user, $admin, $url, $optionSystemService);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * self disabled
     * @param User $user
     * @param User $admin
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailSelfDisabled(
        User                $user,
        OptionSystemService $optionSystemService
    ): array
    {
        $tab = $this->getGlobalKeyWord($user, $optionSystemService);
        return $this->formatReturnValue($tab);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * self delete
     * @param User $user
     * @param User $admin
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailSelfDelete(
        User                $user,
        OptionSystemService $optionSystemService
    ): array
    {
        return $this->getTabMailSelfDisabled($user, $optionSystemService);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * self anonymous
     * @param User $user
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailSelfAnonymous(
        User                $user,
        OptionSystemService $optionSystemService
    ): array
    {
        return $this->getTabMailSelfDisabled($user, $optionSystemService);
    }

    /**
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email reset de mot de passe
     * @param User $user
     * @param User $admin
     * @param UrlGeneratorInterface $router
     * @param OptionSystemService $optionSystemService
     * @return array
     */
    public function getTabMailResetPassword(
        User                $user,
        User                $admin,
        string              $urlPath,
        OptionSystemService $optionSystemService
    ): array
    {
        $url = $optionSystemService->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $minutes = $optionSystemService->getValueByKey(OptionSystemKey::OS_MAIL_RESET_PASSWORD_TIME);

        $tab = $this->getGlobalKeyWord($user, $optionSystemService);
        $tab2 = [
            '[[' . self::GLOBAL_URL_CHANGE_PASSWORD . ']]' => $url . $urlPath,
            '[[' . self::URL_TIME . ']]' => $minutes,
        ];

        $tab3 = $this->getAdminLoginAction($admin);
        $tab = array_merge($tab, $tab2, $tab3);
        return $this->formatReturnValue($tab);
    }
}
