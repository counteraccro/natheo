<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;

class AdminAppService
{

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}