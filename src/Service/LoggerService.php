<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère les logs de l'application
 */

namespace App\Service;

use App\Entity\Admin\User;
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
                                LoggerInterface     $doctrineLogLogger, Security $security, ContainerBagInterface $params)
    {
        $this->authLogger = $authLogger;
        $this->doctrineLogLogger = $doctrineLogLogger;
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
     */
    public function getAllFiles(string $date = ""): array
    {
        $kernel = $this->params->get('kernel.project_dir');
        $pathLog = $kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::DIRECTORY_LOG;

        $finder = new Finder();
        if($date !== "")
        {
            $date = new \DateTime($date);
            //print_r($date->format('Y-m-d'));
            $finder->files()->in($pathLog)->name('*-'. $date->format('Y-m-d') . '.log');
        }
        else {
            $finder->files()->in($pathLog);
        }


        $return = [];
        foreach($finder as $file)
        {
            $return[] = ['type' => 'file', 'name' => $file->getRelativePathname(), 'path' => $file->getRelativePath()];
        }
        return $return;
    }
}