<?php

namespace App\Controller\Admin;

use App\Service\Admin\System\OptionUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppAdminController extends AbstractController
{
    protected OptionUserService $optionUserService;

    public function __construct(OptionUserService $optionUserService)
    {
        $this->optionUserService = $optionUserService;
    }
}
