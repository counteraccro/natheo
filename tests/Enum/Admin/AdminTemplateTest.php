<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'enum AdminTemplate
 */

namespace App\Tests\Enum\Admin;

use App\Enum\Admin\AdminTemplate;
use App\Enum\Admin\System\Options\OptionUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class AdminTemplateTest extends TestCase
{
    /**
     * Les valeurs de l'option utilisateur sont exactement les cas de l'enum, défaut compris
     * @return void
     */
    public function testOptionUserListMatchesEnum(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__, 3) . '/config/cms/options_user.yaml');
        $option = null;
        foreach ($config['options_user'] as $category) {
            $option ??= $category['options'][OptionUser::OU_ADMIN_THEME->value] ?? null;
        }
        $this->assertNotNull($option);

        $values = array_map(fn(string $item) => explode(':', $item)[0], explode('|', $option['list_value']));
        $this->assertSame(array_column(AdminTemplate::cases(), 'value'), $values);
        $this->assertSame(AdminTemplate::default()->value, $option['default']);
    }

    /**
     * Une valeur inconnue ou absente donne le thème par défaut
     * @return void
     */
    public function testFromValueOrDefault(): void
    {
        $this->assertSame(AdminTemplate::NATHEO_ATELIER, AdminTemplate::fromValueOrDefault('natheo_atelier'));
        $this->assertSame(AdminTemplate::NATHEO_STUDIO, AdminTemplate::fromValueOrDefault('inconnu'));
        $this->assertSame(AdminTemplate::NATHEO_STUDIO, AdminTemplate::fromValueOrDefault(null));
    }
}
