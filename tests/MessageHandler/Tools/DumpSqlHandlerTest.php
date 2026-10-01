<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Tools;

use App\Entity\Admin\Notification;
use App\Entity\Admin\System\ApiToken;
use App\Enum\Admin\Tools\DatabaseManager\DatabaseManagerData;
use App\Message\Tools\DumpSql;
use App\MessageHandler\Tools\DumpSqlHandler;
use App\Repository\Admin\NotificationRepository;
use App\Tests\AppWebTestCase;
use Symfony\Component\Filesystem\Filesystem;

class DumpSqlHandlerTest extends AppWebTestCase
{
    /**
     * @var DumpSqlHandler
     */
    private DumpSqlHandler $dumpSqlHandler;

    /**
     * @var Filesystem
     */
    private Filesystem $fileSystem;

    public function setUp(): void
    {
        parent::setUp();
        $this->dumpSqlHandler = $this->container->get(DumpSqlHandler::class);
        $this->fileSystem = new Filesystem();
    }

    public function tearDown(): void
    {
        // Dossier propre à l'environnement de test, il peut être vidé sans risque
        (new Filesystem())->remove($this->getDumpDirectory());
        parent::tearDown();
    }

    /**
     * Test méthode __invoke()
     * @return void
     */
    public function testInvoke(): void
    {
        $userAdm = $this->createUserSuperAdmin();
        for ($i = 0; $i < 10; $i++) {
            $this->createApiToken();
        }

        $this->dumpSqlHandler->__invoke(
            new DumpSql(
                [
                    'filename' => 'test',
                    'all' => false,
                    'tables' => ['api_token', 'user'],
                    'data' => 'data_table',
                ],
                $userAdm->getId(),
            ),
        );

        $parameters = $this->getNotificationParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('test.sql', $parameters[0]['file']);
        $this->assertStringContainsString('/download-dump-file/test.sql', $parameters[0]['url']);
        $this->assertStringStartsWith('/admin/fr/', $parameters[0]['url']);

        $platform = $this->em->getConnection()->getDatabasePlatform();
        $content = $this->fileSystem->readFile($this->getPath('test.sql'));
        $this->assertStringContainsString('CREATE TABLE api_token', $content);
        $this->assertStringContainsString('CREATE TABLE user', $content);
        $this->assertStringContainsString('INSERT INTO ' . $platform->quoteSingleIdentifier('api_token'), $content);
        $this->assertStringContainsString('INSERT INTO ' . $platform->quoteSingleIdentifier('user'), $content);
    }

    /**
     * Test que le lien de la notification utilise la langue de l'utilisateur
     * @return void
     */
    public function testInvokeNotificationLocale(): void
    {
        $userAdm = $this->createUserSuperAdmin();

        $this->dumpSqlHandler->__invoke(
            new DumpSql(
                ['filename' => 'test-locale', 'all' => false, 'tables' => ['api_token'], 'data' => 'table'],
                $userAdm->getId(),
                'en',
            ),
        );

        $this->assertStringStartsWith('/admin/en/', $this->getNotificationParameters()[0]['url']);
    }

    /**
     * Test que les données du dump sont correctement échappées et peuvent être réimportées
     * @return void
     */
    public function testInvokeDataCanBeRestored(): void
    {
        $userAdm = $this->createUserSuperAdmin();
        $this->createApiToken(['name' => "Token de l'API", 'comment' => null]);
        $this->createApiToken(['name' => 'Token "double" \\ antislash']);

        $connection = $this->em->getConnection();
        $expected = $connection->fetchAllAssociative('SELECT * FROM api_token ORDER BY id');

        $this->dumpSqlHandler->__invoke(
            new DumpSql(
                [
                    'filename' => 'test-restore',
                    'all' => false,
                    'tables' => ['api_token'],
                    'data' => 'data',
                ],
                $userAdm->getId(),
            ),
        );

        $content = $this->fileSystem->readFile($this->getPath('test-restore.sql'));
        $this->assertStringContainsString('NULL', $content);
        $this->assertStringNotContainsString('CREATE TABLE', $content);

        $connection->executeStatement('DELETE FROM api_token');
        foreach (explode("\n", $content) as $line) {
            if ($line !== '' && !str_starts_with($line, '/*')) {
                $connection->executeStatement($line);
            }
        }

        $this->assertEquals($expected, $connection->fetchAllAssociative('SELECT * FROM api_token ORDER BY id'));
        $this->em->clear();
        $this->assertCount(2, $this->em->getRepository(ApiToken::class)->findAll());
    }

    /**
     * Test que les grosses tables sont exportées en plusieurs requêtes INSERT et restent restaurables
     * @return void
     */
    public function testInvokeLargeTableIsSplitInBatches(): void
    {
        $userAdm = $this->createUserSuperAdmin();
        $connection = $this->em->getConnection();
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $values = [];
        for ($i = 1; $i <= 1200; $i++) {
            $values[] = "('token-$i', 'value-$i', '[]', 0, '$now', '$now')";
        }
        $connection->executeStatement(
            'INSERT INTO api_token (name, token, roles, disabled, created_at, update_at) VALUES ' .
                implode(', ', $values),
        );
        $expected = $connection->fetchAllAssociative('SELECT * FROM api_token ORDER BY id');

        $this->dumpSqlHandler->__invoke(
            new DumpSql(
                ['filename' => 'test-large', 'all' => false, 'tables' => ['api_token'], 'data' => 'data'],
                $userAdm->getId(),
            ),
        );

        $content = $this->fileSystem->readFile($this->getPath('test-large.sql'));
        $this->assertEquals(3, substr_count($content, 'INSERT INTO'));

        $connection->executeStatement('DELETE FROM api_token');
        foreach (explode("\n", $content) as $line) {
            if ($line !== '' && !str_starts_with($line, '/*')) {
                $connection->executeStatement($line);
            }
        }
        $this->assertEquals($expected, $connection->fetchAllAssociative('SELECT * FROM api_token ORDER BY id'));
    }

    /**
     * Test que les clés étrangères sont créées après l'insertion des données
     * @return void
     */
    public function testInvokeForeignKeysAfterData(): void
    {
        $userAdm = $this->createUserSuperAdmin();
        $this->createNotification($userAdm);

        $this->dumpSqlHandler->__invoke(
            new DumpSql(
                ['filename' => 'test-fk', 'all' => false, 'tables' => ['user', 'notification'], 'data' => 'data_table'],
                $userAdm->getId(),
            ),
        );

        $content = $this->fileSystem->readFile($this->getPath('test-fk.sql'));
        $nbForeignKeys = count(
            $this->em
                ->getConnection()
                ->createSchemaManager()
                ->introspectTableForeignKeyConstraintsByUnquotedName('notification'),
        );
        $this->assertGreaterThan(0, $nbForeignKeys);
        $this->assertEquals($nbForeignKeys, substr_count($content, 'FOREIGN KEY'));
        $this->assertGreaterThan(strrpos($content, 'INSERT INTO'), strpos($content, 'FOREIGN KEY'));
        $this->assertLessThan(strpos($content, 'INSERT INTO'), strpos($content, 'CREATE TABLE notification'));
    }

    /**
     * Test qu'un dump existant n'est jamais écrasé
     * @return void
     */
    public function testInvokeDoesNotOverwriteExistingDump(): void
    {
        $userAdm = $this->createUserSuperAdmin();
        $longName = str_repeat('a', 100);
        $this->fileSystem->dumpFile($this->getPath('test.sql'), 'ancien dump');
        $this->fileSystem->dumpFile($this->getPath($longName . '.sql'), 'ancien dump');

        foreach (['test', 'test', $longName] as $name) {
            $this->dumpSqlHandler->__invoke(
                new DumpSql(
                    ['filename' => $name, 'all' => false, 'tables' => ['api_token'], 'data' => 'table'],
                    $userAdm->getId(),
                ),
            );
        }

        $files = array_column($this->getNotificationParameters(), 'file');
        $this->assertEquals(['test-2.sql', 'test-3.sql', str_repeat('a', 98) . '-2.sql'], $files);
        $this->assertEquals('ancien dump', $this->fileSystem->readFile($this->getPath('test.sql')));
        foreach ($files as $file) {
            $this->assertTrue(DatabaseManagerData::isValidFileName($file));
            $this->assertFileExists($this->getPath($file));
        }
    }

    /**
     * Test qu'un nom de fichier invalide est remplacé par le nom par défaut
     * @return void
     */
    public function testInvokeInvalidFilename(): void
    {
        $userAdm = $this->createUserSuperAdmin();

        $this->dumpSqlHandler->__invoke(
            new DumpSql(
                [
                    'filename' => '../../public/evil',
                    'all' => false,
                    'tables' => ['api_token'],
                    'data' => 'table',
                ],
                $userAdm->getId(),
            ),
        );

        $parameters = $this->getNotificationParameters();
        $this->assertCount(1, $parameters);
        $this->assertStringStartsWith(DatabaseManagerData::FILE_NAME_DUMP->value, $parameters[0]['file']);
        $this->assertFileExists($this->getPath($parameters[0]['file']));
        $this->assertFileDoesNotExist(self::$kernel->getProjectDir() . '/public/evil.sql');
    }

    /**
     * Retourne les paramètres de l'ensemble des notifications
     * @return array
     */
    private function getNotificationParameters(): array
    {
        /** @var NotificationRepository $repoNotification */
        $repoNotification = $this->em->getRepository(Notification::class);

        return array_map(
            fn(Notification $notification) => json_decode($notification->getParameters(), true),
            $repoNotification->findAll(),
        );
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getPath(string $fileName): string
    {
        return $this->getDumpDirectory() . $fileName;
    }

    /**
     * Retourne le dossier de stockage des dumps
     * @return string
     */
    private function getDumpDirectory(): string
    {
        return static::getContainer()->getParameter('app.dump_directory');
    }
}
