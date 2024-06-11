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
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\AbstractManagerRegistry;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DumpSqlHandler
{

    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DumpSql $dumpSql): void
    {
        $filesystem = new Filesystem();

        //$dumpSql->getOption());

        $abstractPlatform = $this->entityManager->getConnection()->getDatabasePlatform();
        $schema = $this->entityManager->getConnection()->createSchemaManager();
        $tables = $schema->introspectSchema()->getTables();

        foreach ($tables as $table) {
            echo $table->getName() . " columns:\n\n";
            foreach ($table->getColumns() as $column) {
                echo ' - ' . $column->getName() . "\n";
            }
        }
        $sql = $abstractPlatform->getCreateTablesSQL($tables);
        var_dump($sql);


        /*try {
            $filesystem->mkdir(DatabaseManagerConst::ROOT_FOLDER_NAME);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }*/

        $filesystem->appendToFile(DatabaseManagerConst::ROOT_FOLDER_NAME . 'logs.txt', 'Email sent to user@example.com', true);



        $user = $this->notificationService->findOneById(User::class, $dumpSql->getUserId());
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_DUMP_SQL, ['file' => 'demo', 'url' => "#"]);
    }
}
