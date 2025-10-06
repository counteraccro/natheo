<?php

namespace App\Tests\MessageHandler\Tools;

use App\Entity\Admin\Notification;
use App\Message\Tools\DumpSql;
use App\MessageHandler\Tools\DumpSqlHandler;
use App\Repository\Admin\NotificationRepository;
use App\Service\Admin\Tools\DatabaseManagerService;
use App\Tests\AppWebTestCase;
use App\Utils\Tools\DatabaseManager\DatabaseManagerConst;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;

class DumpSqlHandlerTest extends AppWebTestCase
{
    /**
     * @var DumpSqlHandler
     */
    private DumpSqlHandler $dumpSqlHandler;

    /**
     * @var DatabaseManagerService
     */
    private DatabaseManagerService $databaseManagerService;

    /**
     * @var Filesystem
     */
    private Filesystem $fileSystem;

    public function setUp(): void
    {
        parent::setUp();
        $this->dumpSqlHandler = $this->container->get(DumpSqlHandler::class);
        $this->databaseManagerService = $this->container->get(DatabaseManagerService::class);
        $this->fileSystem = new Filesystem();
    }

    /**
     * Test méthode __invoke()
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testInvoke(): void
    {
        $userAdm = $this->createUserSuperAdmin();
        for ($i = 0; $i < 10; $i++) {
            $this->createApiToken();
        }

        $data = [
            'options' => [
                'all' => false,
                'tables' => ['api_token', 'user'],
                'data' => 'table_data',
            ],
        ];

        $dumpSql = new DumpSql($data['options'], $userAdm->getId());
        $this->dumpSqlHandler->__invoke($dumpSql);

        $result = $this->databaseManagerService->getAllDump();

        /** @var NotificationRepository $repoNotification */
        $repoNotification = $this->em->getRepository(Notification::class);
        $notifications = $repoNotification->findAll();
        $this->assertCount(1, $notifications);
        $this->assertIsString($notifications[0]->getParameters());
        $tab = json_decode($notifications[0]->getParameters(), true);
        $this->assertIsArray($tab);
        $this->assertArrayHasKey('file', $tab);

        $file = '';
        foreach ($result as $item) {
            if ($item['name'] === $tab['file']) {
                $file = $tab['file'];
                break;
            }
        }
        $this->assertNotEmpty($file);
        $content = $this->readFile($file);
        $this->assertStringContainsString('CREATE TABLE api_token', $content);
        $this->assertStringContainsString('CREATE TABLE user', $content);
        $this->assertStringContainsString('INSERT INTO api_token', $content);
        $this->assertStringContainsString('INSERT INTO user ', $content);
        $this->deleteFile($file);
    }

    /**
     * @param string $fileName
     * @return void
     */
    private function deleteFile(string $fileName): void
    {
        $this->fileSystem->remove(self::$kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . $fileName);
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function readFile(string $fileName): string
    {
        return $this->fileSystem->readFile(
            self::$kernel->getProjectDir() . DatabaseManagerConst::ROOT_FOLDER_NAME . $fileName,
        );
    }
}
