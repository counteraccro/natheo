<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Déclare des entrées de menu sans module (dossiers) auprès de AdminMenuRegistry
 */

namespace App\Service\Admin\Module;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsAdminMenu extends AutoconfigureTag
{
    /**
     * Tag des services qui fournissent des entrées de menu sans module
     * @var string
     */
    public const string TAG = 'natheo.admin_menu';

    public function __construct()
    {
        parent::__construct(self::TAG);
    }
}
