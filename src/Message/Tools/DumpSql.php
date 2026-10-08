<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Message qui permet de stocker les options pour le dump SQL
 */

namespace App\Message\Tools;

class DumpSql
{
    /**
     * @param array $options
     * @param int $userId
     * @param string|null $locale langue de l'utilisateur au moment de la demande, pour le lien de la notification
     */
    public function __construct(
        private readonly array $options,
        private readonly int $userId,
        private readonly ?string $locale = null,
    ) {}

    /**
     * Retourne les options
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Retourne l'id du user
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Retourne la langue de l'utilisateur
     * @return string|null
     */
    public function getLocale(): ?string
    {
        // Un message mis en file avant l'ajout de la langue est désérialisé sans cette propriété
        return $this->locale ?? null;
    }
}
