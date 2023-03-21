<?php

namespace App\Service;

use phpDocumentor\Reflection\Types\Boolean;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoggerService extends AppService
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $authLogger;

    public function __construct(TranslatorInterface $translator, LoggerInterface $authLogger)
    {
        $this->authLogger = $authLogger;
        parent::__construct($translator);
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
        if($success)
        {
            $msg = $this->translator->trans('log.auth.admin.success', ['user' => $user, 'ip' => $ip]);
            $level = LogLevel::INFO;
        }
        else {
            $msg = $this->translator->trans('log.auth.admin.error', ['user' => $user, 'ip' => $ip]);
            $level = LogLevel::WARNING;
        }
        $this->authLogger->log($level, $msg);
    }
}