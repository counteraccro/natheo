<?php

/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service qui gère les logs de l'application
 */

namespace App\Service;

use App\Entity\Admin\System\User;
use App\Service\Admin\GridService;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Admin\System\OptionUserService;
use App\Utils\System\Options\OptionSystemKey;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoggerService extends AppService
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $authLogger;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $doctrineLogLogger;

    /**
     * @var GridService
     */
    private GridService $gridService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @var OptionUserService
     */
    private OptionUserService $optionUserService;

    /**
     * @var LocaleAwareInterface
     */
    private LocaleAwareInterface $localeAware;

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


    public function __construct(#[AutowireLocator([
        'entityManager' => EntityManagerInterface::class,
        'containerBag' => ContainerBagInterface::class,
        'translator' => TranslatorInterface::class,
        'security' => Security::class,
        'requestStack' => RequestStack::class,
        'authLogger' => LoggerInterface::class,
        'doctrineLogLogger' => LoggerInterface::class,
        'gridService' => GridService::class,
        'optionSystemService' => OptionSystemService::class,
        'optionUserService' => OptionUserService::class,
        'localeAware' => LocaleAwareInterface::class
    ])] private readonly ContainerInterface $handlers)
    {
        $this->authLogger = $this->handlers->get('authLogger');
        $this->doctrineLogLogger = $this->handlers->get('doctrineLogLogger');
        $this->gridService = $this->handlers->get('gridService');
        $this->optionSystemService = $this->handlers->get('optionSystemService');
        $this->optionUserService = $this->handlers->get('optionUserService');
        $this->localeAware = $this->handlers->get('localeAware');
        parent::__construct($handlers);
    }

    /**
     * Retourne le path des logs
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getPathLog() :string
    {
        $kernel = $this->params->get('kernel.project_dir');
        return $kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::DIRECTORY_LOG;
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

        // On force le changement de langue pour éviter d'enregistrer les logs dans la langue du user courant
        $this->switchDefaultLocale('system');
        if ($success) {
            $msg = $this->translator->trans('log.auth.admin.success', ['user' => $user, 'ip' => $ip], 'log');
            $level = LogLevel::INFO;
        } else {
            $msg = $this->translator->trans('log.auth.admin.error', ['user' => $user, 'ip' => $ip], 'log');
            $level = LogLevel::WARNING;
        }
        $this->authLogger->log($level, $msg);
        $this->switchDefaultLocale();
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
        $this->switchDefaultLocale('system');
        $msg = $this->translator->trans(
            'log.auth.admin.user.switch',
            ['user' => $user, 'userToSwitch' => $userToSwitch],
            'log'
        );
        $level = LogLevel::WARNING;
        $this->authLogger->log($level, $msg);
    }

    /**
     * Permet d'enregistrer les logs venant du listener de doctrine
     * @param string $action
     * @param string $entity
     * @param int $id
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function logDoctrine(string $action, string $entity, mixed $id = -1): void
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        $user = 'John doe';
        $idUser = '-';
        if ($currentUser != null) {
            $user = $currentUser->getEmail();
            $idUser = $currentUser->getId();
        }

        // On force le changement de langue pour éviter d'enregistrer les logs dans la langue du user courant
        $this->switchDefaultLocale('system');
        switch ($action) {
            case self::ACTION_DOCTRINE_PERSIST :
                $msg = $this->translator->trans('log.doctrine.persit', ['entity' => $entity, 'id' => $id, 'user' =>
                    $user, 'id_user' => $idUser], 'log'
                );
                $this->doctrineLogLogger->notice($msg);
                $typeOption = 'user';
                break;
            case self::ACTION_DOCTRINE_REMOVE :
                $msg = $this->translator->trans('log.doctrine.remove', ['entity' => $entity, 'id' => $id, 'user' =>
                    $user, 'id_user' => $idUser], 'log'
                );
                $this->doctrineLogLogger->warning($msg);
                $typeOption = 'system';
                break;
            case self::ACTION_DOCTRINE_UPDATE :
                $msg = $this->translator->trans('log.doctrine.update', ['entity' => $entity, 'id' => $id, 'user' =>
                    $user, 'id_user' => $idUser], 'log'
                );
                $this->doctrineLogLogger->info($msg);
                $typeOption = 'user';
                break;
            default:
                $msg = '';
                $typeOption = 'user';
        }
        $this->switchDefaultLocale($typeOption);
    }

    /**
     * Retourne l'ensemble des logs (nom de fichiers) en respectant l'arborescence des logs
     * @param string $date
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getAllFiles(string $date = ""): array
    {
        $pathLog = $this->getPathLog();

        $finder = new Finder();
        if ($date !== "all") {
            $date = new \DateTime($date);
            $finder->files()->in($pathLog)->name('*-' . $date->format('Y-m-d') . '.log');
        } else {
            $finder->files()->in($pathLog);
        }


        $return = [];
        foreach ($finder as $file) {
            $return[] = ['type' => 'file', 'name' => $file->getRelativePathname(), 'path' => $file->getFilename()];
        }
        return $return;
    }

    /**
     * Retourne sous la forme d'un tableau GRID le contenu du fichier envoyé en paramètre
     * @param string $fileName
     * @param int $page
     * @param int $limit
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function loadLogFile(string $fileName, int $page, int $limit): array
    {
        $column = [
            $this->translator->trans('log.grid.level', domain: 'log'),
            $this->translator->trans('log.grid.date', domain: 'log'),
            $this->translator->trans('log.grid.message', domain: 'log'),
        ];

        $pathLog = $this->getPathLog();
        $finder = new Finder();
        $finder->files()->name($fileName)->in($pathLog);

        $tab = [];
        $total = 0;
        $taille = 0;
        if ($finder->hasResults() && $finder->count() === 1) {
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $file = $iterator->current();

            $total = substr_count($file->getContents(), "\n");
            $taille = $file->getSize();
            $content = $file->openFile();

            $i = ($limit * $page) - $limit;
            $begin = $i;
            $nb = 0;
            while (!$content->eof()) {

                if ($i >= $limit * $page || $nb === $total) {
                    break;
                }

                if ($nb >= $begin) {
                    $line = json_decode($content->fgets(), true);
                    if (is_array($line)) {
                        $tab[] = $this->formatLog($line);
                    }
                    $i++;
                } else {
                    $content->fgets();
                }
                $nb++;
            }
        }

        $tabReturn = [
            'nb' => $total,
            'data' => array_reverse($tab),
            'column' => $column,
            'taille' => Utils::getSizeName($taille),
        ];
        return $this->gridService->addAllDataRequiredGrid($tabReturn);
    }

    /**
     * Permet de formater les logs pour l'affichage
     * @param array $tabLog
     * @return array
     * @throws Exception
     */
    private function formatLog(array $tabLog): array
    {

        $date = new \DateTime($tabLog['datetime']);
        $dateStr = $date->format('d-m-Y h:i:s');

        $class = match (Level::fromName($tabLog['level_name'])) {
            Level::Debug => 'badge text-bg-light',
            Level::Notice, Level::Info => 'badge text-bg-info',
            Level::Warning => 'badge text-bg-warning',
            Level::Error, Level::Critical, Level::Alert, Level::Emergency => 'badge text-bg-danger',
            default => '',
        };

        return [
            $this->translator->trans('log.grid.message', domain: 'log') => $tabLog['message'],
            $this->translator->trans('log.grid.date', domain: 'log') => $dateStr,
            $this->translator->trans('log.grid.level', domain: 'log') => '<span class="' . $class . '">'
                . $tabLog['level_name'] . '</span>',
        ];
    }

    /**
     * Permet de supprimer un fichier de log
     * @param string $fileName
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function deleteLog(string $fileName): bool
    {
        $pathLog = $this->getPathLog();
        $finder = new Finder();

        $finder->files()->name($fileName)->in($pathLog);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $filesystem = new Filesystem();
                $filesystem->remove($file);
            }
            return true;
        }
        return false;
    }

    /**
     * Retourne le path d'un fichier
     * @param string $fileName
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPathFile(string $fileName) :string
    {
        $pathLog = $this->getPathLog();
        $finder = new Finder();

        $finder->files()->name($fileName)->in($pathLog);

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
               return $file->getPathname();
            }
        }
        return '';
    }

    /**
     * Permet de forcer la locale si besoin pour enregistrer les logs uniquement dans la langue par défaut du site et
     * non la langue du user courant
     * @param string $typeOption user ou system
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function switchDefaultLocale(string $typeOption = 'user'): void
    {
        $locale = match ($typeOption) {
            'user' => $this->optionUserService->getValueByKey(OptionUserKey::OU_DEFAULT_LANGUAGE, false),
            default => $this->optionSystemService->getValueByKey(OptionSystemKey::OS_DEFAULT_LANGUAGE),
        };
        $this->localeAware->setLocale($locale);
    }
}
