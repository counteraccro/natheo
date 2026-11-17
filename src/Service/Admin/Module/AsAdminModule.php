<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Déclare un module du back-office auprès de AdminModuleRegistry
 */

namespace App\Service\Admin\Module;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsAdminModule extends AutoconfigureTag
{
    /**
     * Tag des services de module
     * @var string
     */
    public const string TAG = 'natheo.admin_module';

    public function __construct()
    {
        parent::__construct(self::TAG);
    }
}
