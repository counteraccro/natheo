<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Gestionnaire de Message qui permet de créer un dump SQL en fonction des options
 */
namespace App\MessageHandler\Tools;

use App\Message\Tools\DumpSql;
use App\Utils\Content\Media\MediaFolderConst;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DumpSqlHandler
{
    public function __invoke(DumpSql $dumpSql): void
    {
        $filesystem = new Filesystem();


        try {
            $filesystem->mkdir(DatabaseManagerConst::ROOT_FOLDER_NAME);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }

        $filesystem->appendToFile(DatabaseManagerConst::ROOT_FOLDER_NAME . 'logs.txt', 'Email sent to user@example.com', true);
        var_dump($dumpSql->getOption());
    }
}
