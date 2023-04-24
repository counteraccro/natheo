<?php

namespace App\Utils\Mail;

use App\Service\Admin\MailService;

class KeyWord
{
    /**
     * Tableau contenant l'ensemble des keyWords de tous les emails
     * @var array|array[]
     */
    private array $tabKeyWord = [
        MailService::KEY_MAIL_CHANGE_PASSWORD => [
            'user.email' => '$user->getEmail()',
            'user.login' => '$user->getLogin()',
            'user.firstname' => '$user->getFirstname()',
            'user.lastname' => '$user->getLastname()',
            'global.url' => '',
            'global.url_change_psw' => ''
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
}
