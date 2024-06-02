<?php
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
     */
    public function __construct(
        private array $options,
        private int   $userId
    )
    {
    }

    /**
     * Retourne les options
     * @return array
     */
    public function getOption(): array
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

}
