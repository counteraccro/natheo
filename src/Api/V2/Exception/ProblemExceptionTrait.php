<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Champs problem+json communs des exceptions HTTP de l'API v2
 */

namespace App\Api\V2\Exception;

trait ProblemExceptionTrait
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return '/errors/' . $this->getStatusCode();
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->getStatusCode();
    }

    /**
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->getMessage();
    }

    /**
     * @return string|null
     */
    public function getInstance(): ?string
    {
        return null;
    }
}
