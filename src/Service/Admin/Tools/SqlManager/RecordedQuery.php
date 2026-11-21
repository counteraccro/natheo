<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Requête préparée telle que reçue par le pilote de la base : SQL, valeurs dans l'ordre des « ? » et leurs types
 */

namespace App\Service\Admin\Tools\SqlManager;

use Doctrine\DBAL\ParameterType;

final readonly class RecordedQuery
{
    /**
     * @param string $sql
     * @param array<int|string, mixed> $params
     * @param array<int|string, ParameterType> $types
     */
    public function __construct(public string $sql, public array $params = [], public array $types = []) {}
}
