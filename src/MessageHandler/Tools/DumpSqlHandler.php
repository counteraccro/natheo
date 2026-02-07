<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Gestionnaire de Message qui permet de créer un dump SQL en fonction des options
 */

namespace App\MessageHandler\Tools;

use App\Entity\Admin\System\User;
use App\Enum\Admin\Global\Notification\Notification;
use App\Message\Tools\DumpSql;
use App\Service\Admin\NotificationService;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DumpSqlHandler
{
    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface $parameterBag,
        private KernelInterface $kernel,
    ) {}

    /**
     * @param DumpSql $dumpSql
     * @return void
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(DumpSql $dumpSql): void
    {
        $filesystem = new Filesystem();
        $options = $dumpSql->getOptions();

        $fileName =
            DatabaseManagerConst::FILE_NAME_DUMP . date('d-m-Y-H-i-s') . DatabaseManagerConst::FILE_DUMP_EXTENSION;
        if ($options['filename'] !== null) {
            $fileName = $options['filename'] . DatabaseManagerConst::FILE_DUMP_EXTENSION;
        }

        $path = $this->kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . $fileName;
        $url = '/' . DatabaseManagerConst::FOLDER_NAME . '/' . $fileName;

        $tables = $this->getListeTable($options);

        if ($options['data'] === 'table' || $options['data'] === 'table_data') {
            $abstractPlatform = $this->entityManager->getConnection()->getDatabasePlatform();
            $tableQuery = $abstractPlatform->getCreateTablesSQL($tables);
            foreach ($tableQuery as $query) {
                $filesystem->appendToFile($path, $query . "\n");
            }
        }

        $filesystem->appendToFile($path, "/* DATA GENERATION */\n");
        if ($options['data'] === 'data' || $options['data'] === 'table_data') {
            foreach ($tables as $table) {
                $query = $this->generateInsertQuery($table);
                $filesystem->appendToFile($path, $query . "\n");
            }
        }

        $user = $this->notificationService->findOneById(User::class, $dumpSql->getUserId());
        $this->notificationService->add($user, Notification::DUMP_SQL->value, [
            'file' => $fileName,
            'url' => $url,
        ]);
    }

    /**
     * Retourne la liste des tables pour la sauvegarde en fonction des options
     * @param array $options
     * @return array|Table[]
     * @throws Exception
     */
    private function getListeTable(array $options): array
    {
        $schemaParam = $this->parameterBag->get('app.default_database_schema');
        $schema = $this->entityManager->getConnection()->createSchemaManager();
        $tablesTmp = $schema->introspectSchema()->getTables();

        $tables = [];
        if (!$options['all']) {
            foreach ($tablesTmp as $table) {
                foreach ($options['tables'] as $tDump) {
                    if (
                        $schemaParam . $tDump === $table->getObjectName()->toString() ||
                        $tDump === $table->getObjectName()->toString()
                    ) {
                        $tables[] = $table;
                    }
                }
            }
        } else {
            $tables = $tablesTmp;
        }

        return $tables;
    }

    /**
     * Génère une requête SQL de type insert pour la table
     * @param Table $table
     * @return string|null
     * @throws Exception
     */
    private function generateInsertQuery(Table $table): ?string
    {
        $query = 'SELECT * from ' . $table->getObjectName()->toString();
        $result = $this->entityManager->getConnection()->prepare($query)->executeQuery()->fetchAllAssociative();

        if (empty($result)) {
            return null;
        }

        $query = 'INSERT INTO ' . $table->getObjectName()->toString() . ' (';
        $keys = array_keys($result[0]);
        foreach ($keys as $key) {
            $query .= $key . ', ';
        }
        $query = substr($query, 0, -2) . ') VALUES ';

        foreach ($result as $row) {
            $query .= '(';
            foreach ($row as $value) {
                if (is_int($value)) {
                    $query .= $value . ', ';
                } elseif (is_bool($value)) {
                    if ($value) {
                        $query .= 'true, ';
                    } else {
                        $query .= 'false, ';
                    }
                } else {
                    $query .= "'" . $value . "', ";
                }
            }
            $query = substr($query, 0, -2) . '),';
        }
        return substr($query, 0, -1) . ';';
    }
}
