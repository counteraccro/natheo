<?php

namespace App\Controller\Admin;

use App\Service\Admin\System\OptionUserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppAdminController extends AbstractController
{
    /**
     * @var OptionUserService
     */
    protected OptionUserService $optionUserService;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param OptionUserService $optionUserService
     * @param LoggerInterface $logger
     */
    public function __construct(OptionUserService $optionUserService, LoggerInterface $logger)
    {
        $this->optionUserService = $optionUserService;
        $this->logger = $logger;
    }
}
