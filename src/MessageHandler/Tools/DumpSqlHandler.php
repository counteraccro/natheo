<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Gestionnaire de Message qui permet de créer un dump SQL en fonction des options
 */

namespace App\MessageHandler\Tools;

use App\Entity\Admin\System\User;
use App\Message\Tools\DumpSql;
use App\Repository\Admin\System\UserRepository;
use App\Service\Admin\NotificationService;
use App\Utils\Notification\NotificationKey;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\AbstractManagerRegistry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DumpSqlHandler
{

    public function __construct(
        private NotificationService    $notificationService,
        private EntityManagerInterface $entityManager,
        private ParameterBagInterface  $parameterBag,
    )
    {
    }

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
        $fileName = DatabaseManagerConst::FILE_NAME_DUMP . date('d-m-Y-H-i-s') . DatabaseManagerConst::FILE_DUMP_EXTENSION;
        $path = DatabaseManagerConst::ROOT_FOLDER_NAME . $fileName;
        $url = '/dump/' . $fileName;

        $tables = $this->getListeTable($options);

        if($options['data'] === 'table' || $options['data'] === 'table_data')
        {
            $abstractPlatform = $this->entityManager->getConnection()->getDatabasePlatform();
            $tableQuery = $abstractPlatform->getCreateTablesSQL($tables);
            foreach($tableQuery as $query)
            {
                $filesystem->appendToFile($path, $query . "\n");
            }
        }

        if($options['data'] === 'data' || $options['data'] === 'table_data')
        {
            // A rmeplir
        }

        $user = $this->notificationService->findOneById(User::class, $dumpSql->getUserId());
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_DUMP_SQL, ['file' => $fileName, 'url' => $url]);
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
                    if ($schemaParam . $tDump === $table->getName()) {
                        $tables[] = $table;
                    }
                }
            }
        } else {
            $tables = $tablesTmp;
        }

        return $tables;
    }
}
