<?php
/**
 * Gestion des mots clés des emails
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Mail;

use App\Entity\Admin\User;
use App\Service\Admin\MailService;
use App\Service\Admin\OptionSystemService;
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
     * Clé url changement du mot de passe
     * @var string
     */
    private const GLOBAL_URL_CHANGE_PASSWORD = 'global.url_change_psw';

    /**
     * Tableau contenant l'ensemble des keyWords de tous les emails
     * @var array|array[]
     */
    private array $tabKeyWord = [
        MailService::KEY_MAIL_CHANGE_PASSWORD => [
            self::USER_EMAIL => '',
            self::USER_LOGIN => '',
            self::USER_FIRSTNAME => '',
            self::USER_LASTNAME => '',
            self::GLOBAL_URL => '',
            self::GLOBAL_URL_CHANGE_PASSWORD => '',
            self::GLOBAL_SITE_NAME => ''
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
        $this->currentTabKeyWord = $this->tabKeyWord[$key];
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
     * Renvoi le tableau de keyWord avec les valeurs correspondantes pour l'email
     * change password
     * @param User $user
     * @param UrlGeneratorInterface $router
     * @return array
     */
    public function getTabKeyWordMailChangePassword(User                $user, UrlGeneratorInterface $router,
                                                    OptionSystemService $optionSystemService): array
    {
        $url = $optionSystemService->getValueByKey(OptionSystemService::OS_ADRESSE_SITE);
        $siteName = $optionSystemService->getValueByKey(OptionSystemService::OS_SITE_NAME);

        $tab = [
            '[[' . self::USER_LOGIN . ']]' => $user->getLogin(),
            '[[' . self::USER_EMAIL . ']]' => $user->getEmail(),
            '[[' . self::USER_FIRSTNAME . ']]' => $user->getFirstname(),
            '[[' . self::USER_LASTNAME . ']]' => $user->getLastname(),
            '[[' . self::GLOBAL_URL . ']]' => $url . $router->generate('index_index'),
            '[[' . self::GLOBAL_URL_CHANGE_PASSWORD . ']]' => $url . $router->generate('index_index'),
            '[[' . self::GLOBAL_SITE_NAME . ']]' => $siteName,
        ];

        return [
            'search' => array_keys($tab),
            'replace' => array_values($tab)
        ];
    }
}
