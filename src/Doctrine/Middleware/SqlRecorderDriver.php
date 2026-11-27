<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Pilote enveloppé par SqlRecorderMiddleware
 */

namespace App\Doctrine\Middleware;

use App\Service\Admin\Tools\SqlManager\SqlRecorder;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

final class SqlRecorderDriver extends AbstractDriverMiddleware
{
    public function __construct(Driver $driver, private readonly SqlRecorder $recorder)
    {
        parent::__construct($driver);
    }

    /**
     * @param array $params
     * @return Connection
     */
    public function connect(array $params): Connection
    {
        return new SqlRecorderConnection(parent::connect($params), $this->recorder);
    }
}
