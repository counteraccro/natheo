<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les logs de l'application
 */

namespace App\Service;

use App\Entity\Admin\User;
use App\Service\Admin\GridService;
use Exception;
use Monolog\Level;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RequestStack;
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

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, LoggerInterface $authLogger,
                                LoggerInterface     $doctrineLogLogger, Security $security, ContainerBagInterface $params, GridService $gridService)
    {
        $this->authLogger = $authLogger;
        $this->doctrineLogLogger = $doctrineLogLogger;
        $this->gridService = $gridService;
        parent::__construct($translator, $requestStack, $security, $params);
    }

    /**
     * Permet de logger l'authentification de l'admin en fonction de $success
     * @param string $user
     * @param string $ip
     * @param bool $success true log info, false log warning
     * @return void
     */
    public function logAuthAdmin(string $user, string $ip, bool $success = true): void
    {
        if ($success) {
            $msg = $this->translator->trans('log.auth.admin.success', ['user' => $user, 'ip' => $ip]);
            $level = LogLevel::INFO;
        } else {
            $msg = $this->translator->trans('log.auth.admin.error', ['user' => $user, 'ip' => $ip]);
            $level = LogLevel::WARNING;
        }
        $this->authLogger->log($level, $msg);
    }

    /**
     * Permet d'enregistrer les logs venant du listener de doctrine
     * @param string $action
     * @param string $entity
     * @param int $id
     * @return void
     */
    public function logDoctrine(string $action, string $entity, int $id): void
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        $user = 'John doe';
        $id_user = '-';
        if ($currentUser != null) {
            $user = $currentUser->getEmail();
            $id_user = $currentUser->getId();
        }

        switch ($action) {
            case self::ACTION_DOCTRINE_PERSIST :
                $msg = $this->translator->trans('log.doctrine.persit', ['entity' => $entity, 'id' => $id, 'user' => $user, 'id_user' => $id_user]);
                $this->doctrineLogLogger->notice($msg);
                break;
            case self::ACTION_DOCTRINE_REMOVE :
                $msg = $this->translator->trans('log.doctrine.remove', ['entity' => $entity, 'id' => $id, 'user' => $user, 'id_user' => $id_user]);
                $this->doctrineLogLogger->warning($msg);
                break;
            case self::ACTION_DOCTRINE_UPDATE :
                $msg = $this->translator->trans('log.doctrine.update', ['entity' => $entity, 'id' => $id, 'user' => $user, 'id_user' => $id_user]);
                $this->doctrineLogLogger->info($msg);
                break;
        }
    }

    /**
     * Retourne l'ensemble des logs (nom de fichiers) en respectant l'arborescence des logs
     * @param string|null $date
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getAllFiles(string $date = ""): array
    {
        $kernel = $this->params->get('kernel.project_dir');
        $pathLog = $kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::DIRECTORY_LOG;

        $finder = new Finder();
        if ($date !== "") {
            $date = new \DateTime($date);
            //print_r($date->format('Y-m-d'));
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
     * @param $fileName
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function loadLogFile($fileName): array
    {
        $column = [
            $this->translator->trans('log.grid.level'),
            $this->translator->trans('log.grid.date'),
            $this->translator->trans('log.grid.message'),
        ];

        $kernel = $this->params->get('kernel.project_dir');
        $pathLog = $kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::DIRECTORY_LOG;
        $finder = new Finder();
        $finder->files()->name($fileName)->in($pathLog);

        $tab = [];
        $total = 0;
        if ($finder->count() > 0 && $finder->count() === 1) {
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $file = $iterator->current();

            $content = $file->openFile();

            $i = 0;
            while (!$content->eof()) {
                if ($i > 50) {
                    break;
                }

                $line = json_decode($content->fgets(), true);
                if (is_array($line)) {
                    $tab[] = $this->formatLog($line);
                }
                $i++;
                $total++;
            }
        }

        $tabReturn = [
            'nb' => $total,
            'data' => $tab,
            'column' => $column,
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
        $date_str = $date->format('d-m-Y h:i:s');

        $class = match ( Level::fromName($tabLog['level_name'])) {
            Level::Debug => 'badge text-bg-light',
            Level::Notice, Level::Info => 'badge text-bg-info',
            Level::Warning => 'badge text-bg-warning',
            Level::Error, Level::Critical, Level::Alert, Level::Emergency => 'badge text-bg-danger',
            default => '',
        };

        return [
            $this->translator->trans('log.grid.message') => $tabLog['message'],
            $this->translator->trans('log.grid.date') => $date_str,
            $this->translator->trans('log.grid.level') => '<span class="' . $class . '">' . $tabLog['level_name'] . '</span>',
        ];
    }
}