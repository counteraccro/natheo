<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service des informations générales exposées par l'API
 */

namespace App\Service\Api\System;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiInfoService extends AppApiService
{
    /**
     * Version de l'API servie par API Platform
     * @var string
     */
    public const string API_VERSION = 'v2';

    /**
     * Retourne les informations générales de l'installation
     * @return array{siteName: string, natheoVersion: string, apiVersion: string}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfo(): array
    {
        return [
            'siteName' => (string) $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_SITE_NAME->value),
            'natheoVersion' => (string) $this->getParameterBag()->get('app.version'),
            'apiVersion' => self::API_VERSION,
        ];
    }
}
