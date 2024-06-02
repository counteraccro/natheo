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
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DumpSqlHandler
{

    public function __construct(
        private NotificationService $notificationService,
    ) {
    }

    public function __invoke(DumpSql $dumpSql): void
    {
        $filesystem = new Filesystem();


        try {
            $filesystem->mkdir(DatabaseManagerConst::ROOT_FOLDER_NAME);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }

        $filesystem->appendToFile(DatabaseManagerConst::ROOT_FOLDER_NAME . 'logs.txt', 'Email sent to user@example.com', true);

        $user = $this->notificationService->findOneById(User::class, $dumpSql->getUserId());
        $this->notificationService->add($user, NotificationKey::NOTIFICATION_DUMP_SQL, ['file' => 'demo', 'url' => "#"]);
    }
}
