<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Garde les requêtes envoyées à la base pendant l'exécution d'un callable, inactif le reste du temps
 */

namespace App\Service\Admin\Tools\SqlManager;

final class SqlRecorder
{
    /**
     * Vrai pendant record()
     * @var bool
     */
    private bool $recording = false;

    /**
     * @var RecordedQuery[]
     */
    private array $queries = [];

    /**
     * Exécute le callable en gardant les requêtes qu'il envoie à la base
     * @template T
     * @param callable(): T $callback
     * @return array{0: T, 1: RecordedQuery[]} résultat du callable et requêtes dans l'ordre d'envoi
     */
    public function record(callable $callback): array
    {
        $this->queries = [];
        $this->recording = true;
        try {
            $result = $callback();
        } finally {
            $this->recording = false;
        }
        $queries = $this->queries;
        $this->queries = [];
        return [$result, $queries];
    }

    /**
     * @return bool
     */
    public function isRecording(): bool
    {
        return $this->recording;
    }

    /**
     * Appelé par le middleware pour chaque requête envoyée pendant record()
     * @param RecordedQuery $query
     * @return void
     */
    public function add(RecordedQuery $query): void
    {
        if ($this->recording) {
            $this->queries[] = $query;
        }
    }
}
