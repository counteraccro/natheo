<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fréquence de mise à jour de la date de dernière utilisation d'un token
 */

namespace App\Entity\Trait;

trait LastUsedAtTrait
{
    /**
     * Intervalle minimal entre deux mises à jour de la date de dernière utilisation, en secondes
     * @var int
     */
    public const int LAST_USED_AT_INTERVAL = 60;

    /**
     * @return \DateTimeInterface|null
     */
    abstract public function getLastUsedAt(): ?\DateTimeInterface;

    /**
     * Retourne true si la date de dernière utilisation est absente ou date d'au moins un intervalle,
     * pour éviter une écriture en base à chaque appel
     * @param \DateTimeInterface $now
     * @return bool
     */
    public function isLastUsedAtOutdated(\DateTimeInterface $now): bool
    {
        $lastUsedAt = $this->getLastUsedAt();
        return $lastUsedAt === null ||
            $now->getTimestamp() - $lastUsedAt->getTimestamp() >= self::LAST_USED_AT_INTERVAL;
    }
}
