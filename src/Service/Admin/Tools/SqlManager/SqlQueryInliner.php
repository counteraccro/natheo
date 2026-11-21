<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écrit une requête enregistrée avec ses valeurs à la place des « ? », exécutable telle quelle sur la base en cours
 */

namespace App\Service\Admin\Tools\SqlManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\SQL\Parser\Visitor;
use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;

final readonly class SqlQueryInliner
{
    public function __construct(private Connection $connection) {}

    /**
     * Requête avec ses valeurs, mise en forme sur plusieurs lignes
     * @param RecordedQuery $query
     * @return string
     */
    public function inline(RecordedQuery $query): string
    {
        $visitor = new class ($query, $this->literal(...)) implements Visitor {
            /**
             * @var string[]
             */
            public array $parts = [];

            /**
             * Position du prochain « ? », les valeurs positionnelles de DBAL commencent à 1
             * @var int
             */
            private int $position = 1;

            /**
             * @param RecordedQuery $query
             * @param \Closure(mixed, ParameterType): string $literal
             */
            public function __construct(private readonly RecordedQuery $query, private readonly \Closure $literal) {}

            public function acceptPositionalParameter(string $sql): void
            {
                $this->parts[] = $this->value($this->position++, $sql);
            }

            public function acceptNamedParameter(string $sql): void
            {
                $this->parts[] = $this->value(substr($sql, 1), $sql);
            }

            public function acceptOther(string $sql): void
            {
                $this->parts[] = $sql;
            }

            /**
             * Littéral de la valeur, le marqueur laissé tel quel s'il n'a pas de valeur
             * @param int|string $key
             * @param string $marker
             * @return string
             */
            private function value(int|string $key, string $marker): string
            {
                if (!array_key_exists($key, $this->query->params)) {
                    return $marker;
                }
                return ($this->literal)($this->query->params[$key], $this->query->types[$key] ?? ParameterType::STRING);
            }
        };

        $this->connection->getDatabasePlatform()->createSQLParser()->parse($query->sql, $visitor);
        return (new SqlFormatter(new NullHighlighter()))->format(implode('', $visitor->parts));
    }

    /**
     * Valeur écrite selon son type, avec l'échappement du pilote de la base en cours
     * @param mixed $value
     * @param ParameterType $type
     * @return string
     */
    private function literal(mixed $value, ParameterType $type): string
    {
        if ($value === null || $type === ParameterType::NULL) {
            return 'NULL';
        }
        if ($type === ParameterType::BOOLEAN || is_bool($value)) {
            return (string) $this->connection->getDatabasePlatform()->convertBooleans((bool) $value);
        }
        if ($type === ParameterType::INTEGER && is_numeric($value)) {
            return (string) (int) $value;
        }
        if (is_resource($value)) {
            $value = (string) stream_get_contents($value);
        }
        return $this->connection->quote((string) $value);
    }
}
