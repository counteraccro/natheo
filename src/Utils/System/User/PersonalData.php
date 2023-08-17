<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Génère l'affichage des données personnelles de l'utilisateur on fonction de ses préférences
 */

namespace App\Utils\System\User;

use App\Entity\Admin\System\User;

class PersonalData
{
    /**
     * Valeur possible de l'option OU_DEFAULT_PERSONAL_DATA_RENDER
     * anonyme
     * @var string
     */
    const OU_PERSONAL_DATA_RENDER_ANONYME = 'anonyme';

    /**
     * Valeur possible de l'option OU_DEFAULT_PERSONAL_DATA_RENDER
     * email
     * @var string
     */
    const OU_PERSONAL_DATA_RENDER_EMAIL = 'email';

    /**
     * Valeur possible de l'option OU_DEFAULT_PERSONAL_DATA_RENDER
     * login
     * @var string
     */
    const OU_PERSONAL_DATA_RENDER_LOGIN = 'login';

    /**
     * Valeur possible de l'option OU_DEFAULT_PERSONAL_DATA_RENDER
     * name
     * @var string
     */
    const OU_PERSONAL_DATA_RENDER_NAME = 'name';

    /**
     * Tableau des options de rendu
     * @var array|string[]
     */
    private array $optionsRender = [
        self::OU_PERSONAL_DATA_RENDER_ANONYME,
        self::OU_PERSONAL_DATA_RENDER_EMAIL,
        self::OU_PERSONAL_DATA_RENDER_LOGIN,
        self::OU_PERSONAL_DATA_RENDER_NAME
    ];

    /**
     * @var User
     */
    private User $user;

    /**
     * @var string
     */
    private string $optionRender = '';

    /**
     * Construct
     * @param User $user
     * @param string $optionRender
     */
    public function __construct(User $user, string $optionRender = self::OU_PERSONAL_DATA_RENDER_ANONYME)
    {
        $this->user = $user;
        $this->optionRender = $optionRender;
    }

    /**
     * Retourne les données personnelles de l'utilisateur en fonction de ses préférences
     * @return string|null
     */
    public function getPersonalData(): ?string
    {
        $this->checkOptionRender();
        return match ($this->optionRender) {
            self::OU_PERSONAL_DATA_RENDER_EMAIL => $this->getEmail(),
            self::OU_PERSONAL_DATA_RENDER_LOGIN => $this->getLogin(),
            self::OU_PERSONAL_DATA_RENDER_NAME => $this->getName(),
            default => 'Anonyme',
        };
    }

    /**
     * Retourne l'adresse email du user
     * @return string|null
     */
    private function getEmail(): ?string
    {
        return $this->user->getEmail();
    }

    /**
     * Retourne le login du user s'il existe
     * @return string|null
     */
    private function getLogin()
    {
        if(empty($this->user->getLogin()))
        {
            return $this->getEmail();
        }
        return $this->user->getLogin();
    }

    /**
     * Retourne le nom et prénom ou nom ou prénom du user s'il existe
     * @return string|null
     */
    private function getName()
    {
        if(empty($this->user->getFirstname()) && empty($this->user->getLastname()))
        {
            return $this->getLogin();
        }
        return $this->user->getLastname() . ' ' . $this->user->getFirstname();
    }

    /**
     * Vérification si l'option optionRender existe bien
     * SI ce n'est pas le cas, la valeur anonyme est prise par défaut
     * @return void
     */
    private function checkOptionRender()
    {
        if (!in_array($this->optionRender, $this->optionsRender)) {
            $this->optionRender = self::OU_PERSONAL_DATA_RENDER_ANONYME;
        }
    }
}
