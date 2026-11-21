<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Requête préparée enveloppée par SqlRecorderMiddleware, qui garde ses valeurs pour SqlRecorder
 */

namespace App\Doctrine\Middleware;

use App\Service\Admin\Tools\SqlManager\RecordedQuery;
use App\Service\Admin\Tools\SqlManager\SqlRecorder;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

final class SqlRecorderStatement extends AbstractStatementMiddleware
{
    /**
     * @var array<int|string, mixed>
     */
    private array $params = [];

    /**
     * @var array<int|string, ParameterType>
     */
    private array $types = [];

    public function __construct(
        Statement $statement,
        private readonly SqlRecorder $recorder,
        private readonly string $sql,
    ) {
        parent::__construct($statement);
    }

    /**
     * @param int|string $param
     * @param mixed $value
     * @param ParameterType $type
     * @return void
     */
    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
        if ($this->recorder->isRecording()) {
            $this->params[$param] = $value;
            $this->types[$param] = $type;
        }
        parent::bindValue($param, $value, $type);
    }

    /**
     * @return Result
     */
    public function execute(): Result
    {
        if ($this->recorder->isRecording()) {
            $this->recorder->add(new RecordedQuery($this->sql, $this->params, $this->types));
        }
        return parent::execute();
    }
}
