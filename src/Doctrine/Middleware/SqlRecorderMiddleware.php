<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Middleware DBAL qui transmet à SqlRecorder les requêtes telles que le pilote les reçoit
 */

namespace App\Doctrine\Middleware;

use App\Service\Admin\Tools\SqlManager\SqlRecorder;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsMiddleware;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;

#[AsMiddleware]
final readonly class SqlRecorderMiddleware implements Middleware
{
    public function __construct(private SqlRecorder $recorder) {}

    /**
     * @param Driver $driver
     * @return Driver
     */
    public function wrap(Driver $driver): Driver
    {
        return new SqlRecorderDriver($driver, $this->recorder);
    }
}
