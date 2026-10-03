<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * test service Logger
 */

namespace App\Tests\Service;

use App\Service\LoggerService;
use App\Tests\AppWebTestCase;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\LocaleAwareInterface;

class LoggerServiceTest extends AppWebTestCase
{
    /**
     * @var LoggerService
     */
    private LoggerService $loggerService;

    public function setUp(): void
    {
        parent::setUp();
        $this->loggerService = $this->container->get(LoggerService::class);
    }

    /**
     * test méthode logAuthAdmin()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogAuthAdmin(): void
    {
        $user = $this->createUser();

        $logFile = $this->getAuthLogFile();
        $this->loggerService->deleteLog($logFile);
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];

        $this->assertStringContainsString($user->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString('ip-test', $data[0]['Message']);
        $this->assertStringContainsString(strtoupper(LogLevel::INFO), $data[0]['Niveau']);

        $result = $this->loggerService->deleteLog($logFile);
        $this->assertTrue($result);
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', false);
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];
        $this->assertStringContainsString($user->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString('ip-test', $data[0]['Message']);
        $this->assertStringContainsString(strtoupper(LogLevel::WARNING), $data[0]['Niveau']);
    }

    /**
     * Le message d'un log est échappé, il peut contenir une saisie utilisateur (email d'une connexion ratée)
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogAuthAdminEscapeMessage(): void
    {
        $this->loggerService->logAuthAdmin('<img src=x onerror=alert(1)>', 'ip-test', false);
        $result = $this->loggerService->loadLogFile($this->getAuthLogFile(), 1, 1);
        $message = $result['data'][0]['Message'];

        $this->assertStringNotContainsString('<img', $message);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $message);
    }

    /**
     * Test méthode logSwitchUser()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogSwitchUser(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $logFile = $this->getAuthLogFile();
        $this->loggerService->deleteLog($logFile);
        $this->loggerService->logSwitchUser($user1->getLogin(), $user2->getLogin());
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $data = $result['data'];
        $this->assertStringContainsString($user1->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString($user2->getLogin(), $data[0]['Message']);
        $this->assertStringContainsString(strtoupper(LogLevel::WARNING), $data[0]['Niveau']);
    }

    /**
     * La locale courante est restaurée après l'écriture d'un log dans la langue du site
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogRestoreLocale(): void
    {
        $localeAware = $this->container->get(LocaleAwareInterface::class);

        foreach (['en', 'es'] as $locale) {
            $localeAware->setLocale($locale);
            $this->loggerService->logSwitchUser('user', 'userToSwitch');
            $this->assertSame($locale, $localeAware->getLocale());

            $this->loggerService->logAuthAdmin('user', 'ip-test');
            $this->assertSame($locale, $localeAware->getLocale());

            $this->loggerService->logDoctrine(LoggerService::ACTION_DOCTRINE_REMOVE, 'Entity', 1);
            $this->assertSame($locale, $localeAware->getLocale());
        }
    }

    /**
     * Test méthode logDoctrine()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLogDoctrine(): void
    {
        $logFile = $this->getLogDirectory() . 'doctrine-' . date('Y-m-d') . '.log';
        $this->loggerService->deleteLog($logFile);

        $this->loggerService->logDoctrine(LoggerService::ACTION_DOCTRINE_PERSIST, 'EntityPersist', 1);
        $this->loggerService->logDoctrine(LoggerService::ACTION_DOCTRINE_UPDATE, 'EntityUpdate', 2);
        $this->loggerService->logDoctrine(LoggerService::ACTION_DOCTRINE_REMOVE, 'EntityRemove', 3);
        $this->loggerService->logDoctrine('unknown', 'EntityUnknown', 4);

        $result = $this->loggerService->loadLogFile($logFile, 1, 10);
        $this->assertEquals(3, $result['nb']);
        $this->assertStringContainsString('EntityRemove', $result['data'][0]['Message']);
        $this->assertStringContainsString(strtoupper(LogLevel::WARNING), $result['data'][0]['Niveau']);
        $this->assertStringContainsString('EntityUpdate', $result['data'][1]['Message']);
        $this->assertStringContainsString(strtoupper(LogLevel::INFO), $result['data'][1]['Niveau']);
        $this->assertStringContainsString('EntityPersist', $result['data'][2]['Message']);
        $this->assertStringContainsString(strtoupper(LogLevel::NOTICE), $result['data'][2]['Niveau']);
        $this->assertStringContainsString('John doe', $result['data'][0]['Message']);
    }

    /**
     * Test méthode getAllFiles()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetAllFiles(): void
    {
        $this->loggerService->logAuthAdmin('user', 'ip-test');

        $result = $this->loggerService->getAllFiles();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $data = $result[0];
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('path', $data);

        foreach ([date('Y-m-d'), 'now'] as $time) {
            $result = $this->loggerService->getAllFiles($time);
            $this->assertContains($this->getAuthLogFile(), array_column($result, 'path'));
        }

        $result = $this->loggerService->getAllFiles('2000-01-01');
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        $this->expectException(InvalidArgumentException::class);
        $this->loggerService->getAllFiles('date-invalide');
    }

    /**
     * test méthode loadLogFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadLogFile(): void
    {
        $user = $this->createUser();

        $logFile = $this->getAuthLogFile();
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->loadLogFile($logFile, 1, 1);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('nb', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertArrayHasKey('column', $result);
        $this->assertArrayHasKey('taille', $result);
        $this->assertArrayHasKey('urlSaveSql', $result);
        $this->assertArrayHasKey('listLimit', $result);
        $this->assertIsArray($result['listLimit']);
        $this->assertNotEmpty($result['listLimit']);
        $this->assertArrayHasKey('translate', $result);
        $this->assertIsArray($result['translate']);
        $this->assertNotEmpty($result['translate']);
    }

    /**
     * Chaque ligne n'apparaît que sur une seule page, la plus récente en premier
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadLogFilePagination(): void
    {
        $logFile = $this->createFakeLogFile(25);

        $messages = [];
        foreach ([1 => 10, 2 => 10, 3 => 5] as $page => $nbExpected) {
            $result = $this->loggerService->loadLogFile($logFile, $page, 10);
            $this->assertEquals(25, $result['nb']);
            $this->assertCount($nbExpected, $result['data']);
            $messages = array_merge($messages, array_column($result['data'], 'Message'));
        }

        $this->assertEquals(array_map(fn(int $i) => 'message ' . $i, range(24, 0)), $messages);

        $result = $this->loggerService->loadLogFile($logFile, 4, 10);
        $this->assertEmpty($result['data']);

        (new Filesystem())->remove($this->getLogRootPath() . $logFile);
    }

    /**
     * Une ligne non JSON ou au niveau inconnu ne bloque pas la lecture du fichier
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadLogFileInvalidLine(): void
    {
        $logFile = $this->getLogDirectory() . 'invalid-test.log';
        $content =
            "ligne non json\n" .
            json_encode(['message' => 'niveau inconnu', 'level_name' => 'FOO', 'datetime' => 'now']) .
            "\n\n";
        (new Filesystem())->dumpFile($this->getLogRootPath() . $logFile, $content);

        $result = $this->loggerService->loadLogFile($logFile, 1, 10);
        $this->assertEquals(2, $result['nb']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('niveau inconnu', $result['data'][0]['Message']);

        (new Filesystem())->remove($this->getLogRootPath() . $logFile);
    }

    /**
     * Un fichier introuvable ou hors du dossier des logs lève une exception
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testLoadLogFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->loggerService->loadLogFile('toto.log', 1, 10);
    }

    /**
     * test méthode deleteLog()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDeleteLog(): void
    {
        $user = $this->createUser();

        $logFile = $this->getAuthLogFile();
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->deleteLog($logFile);
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->getLogRootPath() . $logFile);

        $result = $this->loggerService->deleteLog('toto.log');
        $this->assertFalse($result);
    }

    /**
     * Les noms de fichiers ne sont pas interprétés comme des motifs et ne sortent pas du dossier des logs
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testDeleteLogUnsafeFileName(): void
    {
        $this->loggerService->logAuthAdmin('user', 'ip-test');

        $outsideFile = $this->getLogRootPath() . '../outside-test.log';
        (new Filesystem())->dumpFile($outsideFile, 'test');

        $fileNames = ['*', '*.log', '/.*/', 'auth-' . date('Y-m-d') . '.log', '../outside-test.log', '../../.env', ''];
        foreach ($fileNames as $fileName) {
            $this->assertFalse($this->loggerService->deleteLog($fileName), $fileName);
        }

        $this->assertFileExists($outsideFile);
        $this->assertFileExists($this->getLogRootPath() . $this->getAuthLogFile());
        (new Filesystem())->remove($outsideFile);
    }

    /**
     * Test méthode getPathFile()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetPathFile(): void
    {
        $user = $this->createUser();

        $logFile = $this->getAuthLogFile();
        $this->loggerService->logAuthAdmin($user->getLogin(), 'ip-test', true);
        $result = $this->loggerService->getPathFile($logFile);
        $this->assertIsString($result);
        $this->assertFileExists($result);

        foreach (['toto.log', '*', '../../composer.json'] as $fileName) {
            $this->assertNull($this->loggerService->getPathFile($fileName), $fileName);
        }
    }

    /**
     * Crée un faux fichier de log de $nb lignes au format JSON de monolog
     * @param int $nb
     * @return string chemin relatif au dossier des logs
     */
    private function createFakeLogFile(int $nb): string
    {
        $content = '';
        for ($i = 0; $i < $nb; $i++) {
            $content .= json_encode(['message' => 'message ' . $i, 'level_name' => 'INFO', 'datetime' => 'now']) . "\n";
        }

        $logFile = $this->getLogDirectory() . 'pagination-test.log';
        (new Filesystem())->dumpFile($this->getLogRootPath() . $logFile, $content);
        return $logFile;
    }

    /**
     * Retourne le chemin relatif du log d'authentification du jour
     * @return string
     */
    private function getAuthLogFile(): string
    {
        return $this->getLogDirectory() . 'auth-' . date('Y-m-d') . '.log';
    }

    /**
     * Retourne le dossier des logs du CMS pour l'environnement courant, relatif au dossier des logs
     * @return string
     */
    private function getLogDirectory(): string
    {
        return 'cms/' . self::$kernel->getEnvironment() . '/';
    }

    /**
     * Retourne le chemin absolu du dossier des logs
     * @return string
     */
    private function getLogRootPath(): string
    {
        return self::$kernel->getProjectDir() . '/var/' . LoggerService::DIRECTORY_LOG . '/';
    }
}
