<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Types d'éléments de la recherche globale, dans l'ordre des onglets
 */

namespace App\Enum\Admin\Global;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Menu\Menu;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Module\CoreModuleId;

enum GlobalSearchEntity: string
{
    case PAGE = 'page';

    case MENU = 'menu';

    case FAQ = 'faq';

    case TAG = 'tag';

    case USER = 'user';

    /**
     * Classe de l'entité recherchée
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return match ($this) {
            self::PAGE => Page::class,
            self::MENU => Menu::class,
            self::FAQ => Faq::class,
            self::TAG => Tag::class,
            self::USER => User::class,
        };
    }

    /**
     * Module du back-office que l'utilisateur doit voir pour rechercher ce type
     * @return string
     */
    public function getModule(): string
    {
        return match ($this) {
            self::PAGE => CoreModuleId::PAGE->value,
            self::MENU => CoreModuleId::MENU->value,
            self::FAQ => CoreModuleId::FAQ->value,
            self::TAG => CoreModuleId::TAG->value,
            self::USER => CoreModuleId::USER->value,
        };
    }
}
