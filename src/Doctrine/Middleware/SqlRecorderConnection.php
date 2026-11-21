<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Connexion enveloppée par SqlRecorderMiddleware
 */

namespace App\Doctrine\Middleware;

use App\Service\Admin\Tools\SqlManager\RecordedQuery;
use App\Service\Admin\Tools\SqlManager\SqlRecorder;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;

final class SqlRecorderConnection extends AbstractConnectionMiddleware
{
    public function __construct(Connection $connection, private readonly SqlRecorder $recorder)
    {
        parent::__construct($connection);
    }

    /**
     * @param string $sql
     * @return Statement
     */
    public function prepare(string $sql): Statement
    {
        return new SqlRecorderStatement(parent::prepare($sql), $this->recorder, $sql);
    }

    /**
     * @param string $sql
     * @return Result
     */
    public function query(string $sql): Result
    {
        if ($this->recorder->isRecording()) {
            $this->recorder->add(new RecordedQuery($sql));
        }
        return parent::query($sql);
    }
}
