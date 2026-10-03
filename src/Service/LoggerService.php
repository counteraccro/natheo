<?php

declare(strict_types=1);

/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Service qui gère les logs de l'application
 */

namespace App\Service;

use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Utils\Utils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use SplFileObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoggerService extends AppService
{
    /**
     * Action doctrine persistance
     * @var string
     */
    const ACTION_DOCTRINE_PERSIST = 'persist';

    /**
     * Action doctrine suppression
     * @var string
     */
    const ACTION_DOCTRINE_REMOVE = 'remove';

    /**
     * Action doctrine modification
     * @var string
     */
    const ACTION_DOCTRINE_UPDATE = 'update';

    /**
     * Nom du dossier de log
     * @var string
     */
    const DIRECTORY_LOG = 'log';

    /**
     * Extension des fichiers de log
     * @var string
     */
    const LOG_EXTENSION = '.log';

    public function __construct(
        #[
            AutowireLocator([
                'entityManager' => EntityManagerInterface::class,
                'containerBag' => ContainerBagInterface::class,
                'translator' => TranslatorInterface::class,
                'security' => Security::class,
                'requestStack' => RequestStack::class,
                'authLogger' => LoggerInterface::class,
                'doctrineLogLogger' => LoggerInterface::class,
                'gridService' => GridService::class,
                'optionSystemService' => OptionSystemService::class,
                'localeAware' => LocaleAwareInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        parent::__construct($handlers);
    }

    /**
     * Retourne le path des logs
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPathLog(): string
    {
        $kernel = $this->params->get('kernel.project_dir');
        return $kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::DIRECTORY_LOG;
    }

    /**
     * Retourne le chemin absolu d'un fichier de log à partir de son chemin relatif au dossier des logs,
     * null si le fichier n'existe pas, n'est pas un .log ou sort du dossier des logs
     * @param string $fileName ex : cms/prod/auth-2026-01-01.log
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSafeLogFilePath(string $fileName): ?string
    {
        if (!str_ends_with($fileName, self::LOG_EXTENSION)) {
            return null;
        }

        $realRoot = realpath($this->getPathLog());
        $realFilePath = realpath($this->getPathLog() . DIRECTORY_SEPARATOR . $fileName);
        if (
            $realRoot === false ||
            $realFilePath === false ||
            !is_file($realFilePath) ||
            !str_starts_with($realFilePath, $realRoot . DIRECTORY_SEPARATOR)
        ) {
            return null;
        }
        return $realFilePath;
    }

    /**
     * Permet de logger l'authentification de l'admin en fonction de $success
     * @param string $user
     * @param string $ip
     * @param bool $success true log info, false log warning
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function logAuthAdmin(string $user, string $ip, bool $success = true): void
    {
        $this->runInSystemLocale(function () use ($user, $ip, $success): void {
            if ($success) {
                $msg = $this->translator->trans('log.auth.admin.success', ['user' => $user, 'ip' => $ip], 'log');
                $level = LogLevel::INFO;
            } else {
                $msg = $this->translator->trans('log.auth.admin.error', ['user' => $user, 'ip' => $ip], 'log');
                $level = LogLevel::WARNING;
            }
            $this->getAuthLogger()->log($level, $msg);
        });
    }

    /**
     * Permet de loger le switch d'utilisateur
     * @param string $user
     * @param string $userToSwitch
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function logSwitchUser(string $user, string $userToSwitch): void
    {
        $this->runInSystemLocale(function () use ($user, $userToSwitch): void {
            $msg = $this->translator->trans(
                'log.auth.admin.user.switch',
                ['user' => $user, 'userToSwitch' => $userToSwitch],
                'log',
            );
            $this->getAuthLogger()->warning($msg);
        });
    }

    /**
     * Permet d'enregistrer les logs venant du listener de doctrine
     * @param string $action
     * @param string $entity
     * @param mixed $id
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function logDoctrine(string $action, string $entity, mixed $id = -1): void
    {
        [$key, $level] = match ($action) {
            self::ACTION_DOCTRINE_PERSIST => ['log.doctrine.persist', LogLevel::NOTICE],
            self::ACTION_DOCTRINE_REMOVE => ['log.doctrine.remove', LogLevel::WARNING],
            self::ACTION_DOCTRINE_UPDATE => ['log.doctrine.update', LogLevel::INFO],
            default => [null, null],
        };
        if ($key === null) {
            return;
        }

        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        $parameters = [
            'entity' => $entity,
            'id' => $id,
            'user' => $currentUser?->getEmail() ?? 'John doe',
            'id_user' => $currentUser?->getId() ?? '-',
        ];

        $this->runInSystemLocale(function () use ($key, $level, $parameters): void {
            $this->getDoctrineLogLogger()->log($level, $this->translator->trans($key, $parameters, 'log'));
        });
    }

    /**
     * Retourne l'ensemble des logs (nom de fichiers) en respectant l'arborescence des logs
     * @param string $time all, now, yesterday ou une date
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException si $time n'est pas une date valide
     */
    public function getAllFiles(string $time = 'all'): array
    {
        $pattern = '*' . self::LOG_EXTENSION;
        if ($time !== 'all') {
            try {
                $date = new DateTimeImmutable($time);
            } catch (Exception) {
                throw new InvalidArgumentException(sprintf('Invalid log date "%s".', $time));
            }
            $pattern = '*-' . $date->format('Y-m-d') . self::LOG_EXTENSION;
        }

        $finder = new Finder();
        $finder->files()->in($this->getPathLog())->name($pattern)->sortByName();

        $return = [];
        foreach ($finder as $file) {
            // Le chemin relatif identifie le fichier : un même nom existe dans plusieurs environnements
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
            $return[] = ['type' => 'file', 'name' => $relativePath, 'path' => $relativePath];
        }
        return $return;
    }

    /**
     * Retourne sous la forme d'un tableau GRID le contenu du fichier envoyé en paramètre,
     * les lignes les plus récentes en premier
     * @param string $fileName chemin relatif au dossier des logs
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException si le fichier est introuvable ou invalide
     */
    public function loadLogFile(string $fileName, int $page, int $limit): array
    {
        $path = $this->getSafeLogFilePath($fileName);
        if ($path === null) {
            throw new RuntimeException(sprintf('Log file "%s" not found.', $fileName));
        }

        $page = max(1, $page);
        $limit = max(1, $limit);
        $columns = [
            'level' => $this->translator->trans('log.grid.level', domain: 'log'),
            'date' => $this->translator->trans('log.grid.date', domain: 'log'),
            'message' => $this->translator->trans('log.grid.message', domain: 'log'),
        ];

        $file = new SplFileObject($path);
        $total = 0;
        while (!$file->eof()) {
            if (trim($file->fgets()) !== '') {
                $total++;
            }
        }

        $end = $total - $limit * ($page - 1);
        $begin = max(0, $end - $limit);

        $tab = [];
        $index = 0;
        $file->rewind();
        while (!$file->eof() && $index < $end) {
            $line = trim($file->fgets());
            if ($line === '') {
                continue;
            }
            if ($index >= $begin) {
                $decoded = json_decode($line, true);
                if (is_array($decoded)) {
                    $tab[] = $this->formatLog($decoded, $columns);
                }
            }
            $index++;
        }

        $tabReturn = [
            'nb' => $total,
            'data' => array_reverse($tab),
            'column' => array_values($columns),
            'taille' => Utils::getSizeName((int) filesize($path)),
        ];
        return $this->getGridService()->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Permet de formater les logs pour l'affichage, le message est échappé car il peut contenir des données
     * saisies par un utilisateur (ex : email d'une tentative de connexion)
     * @param array $tabLog
     * @param array $columns libellés des colonnes indexés par level, date et message
     * @return array
     */
    private function formatLog(array $tabLog, array $columns): array
    {
        try {
            $dateStr = (new DateTimeImmutable((string) ($tabLog['datetime'] ?? '')))->format('d-m-Y H:i:s');
        } catch (Exception) {
            $dateStr = '';
        }

        $levelName = (string) ($tabLog['level_name'] ?? '');
        $class = 'badge rounded-pill';
        if (in_array($levelName, Level::NAMES, true)) {
            $class .= match (Level::fromName($levelName)) {
                Level::Debug => ' badge-primary',
                Level::Notice, Level::Info => ' badge-validated',
                Level::Warning => ' badge-pending',
                Level::Error, Level::Critical, Level::Alert, Level::Emergency => ' badge-moderated',
            };
        }

        return [
            $columns['message'] => htmlspecialchars((string) ($tabLog['message'] ?? '')),
            $columns['date'] => $dateStr,
            $columns['level'] => '<span class="' . $class . '">' . htmlspecialchars($levelName) . '</span>',
        ];
    }

    /**
     * Permet de supprimer un fichier de log
     * @param string $fileName chemin relatif au dossier des logs
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteLog(string $fileName): bool
    {
        $path = $this->getSafeLogFilePath($fileName);
        if ($path === null) {
            return false;
        }

        try {
            (new Filesystem())->remove($path);
        } catch (IOExceptionInterface) {
            return false;
        }
        return true;
    }

    /**
     * Retourne le path d'un fichier, null s'il est introuvable ou invalide
     * @param string $fileName chemin relatif au dossier des logs
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPathFile(string $fileName): ?string
    {
        return $this->getSafeLogFilePath($fileName);
    }

    /**
     * Exécute $callback avec la langue par défaut du site pour que les logs ne dépendent pas de la langue du
     * user courant, puis restaure la locale d'origine
     * @param callable $callback
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function runInSystemLocale(callable $callback): void
    {
        /** @var LocaleAwareInterface $localeAware */
        $localeAware = $this->handlers->get('localeAware');
        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->handlers->get('optionSystemService');

        $currentLocale = $localeAware->getLocale();
        $localeAware->setLocale($optionSystemService->getValueByKey(OptionSystem::OS_DEFAULT_LANGUAGE->value));
        try {
            $callback();
        } finally {
            $localeAware->setLocale($currentLocale);
        }
    }

    /**
     * @return LoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getAuthLogger(): LoggerInterface
    {
        return $this->handlers->get('authLogger');
    }

    /**
     * @return LoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDoctrineLogLogger(): LoggerInterface
    {
        return $this->handlers->get('doctrineLogLogger');
    }

    /**
     * @return GridService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getGridService(): GridService
    {
        return $this->handlers->get('gridService');
    }
}
