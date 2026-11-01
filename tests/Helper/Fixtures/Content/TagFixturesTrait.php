<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixture création de tag
 */
namespace App\Tests\Helper\Fixtures\Content;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;

trait TagFixturesTrait
{
    use FakerTrait;

    /**
     * Créer un tag
     * @param User|null $user propriétaire, un contributeur est créé si null
     * @param array $customData
     * @param bool $persist
     * @return Tag
     */
    public function createTag(?User $user = null, array $customData = [], bool $persist = true): Tag
    {
        if ($user === null) {
            $user = $this->createUserContributeur();
        }

        $data = [
            'user' => $user,
            'color' => self::getFaker()->hexColor(),
            'disabled' => self::getFaker()->boolean(),
        ];

        $tag = $this->initEntity(Tag::class, array_merge($data, $customData));

        if ($persist) {
            $this->persistAndFlush($tag);
        }
        return $tag;
    }

    /**
     * Créer un tag translation
     * @param Tag|null $tag
     * @param array $customData
     * @param bool $persist
     * @return TagTranslation
     */
    public function createTagTranslation(?Tag $tag = null, array $customData = [], bool $persist = true): TagTranslation
    {
        $data = [
            'locale' => self::getFaker()->languageCode(),
            'label' => self::getFaker()->text(),
            'tag' => $tag ?: $this->createTag(),
        ];

        $tagTranslation = $this->initEntity(TagTranslation::class, array_merge($data, $customData));
        $tag->addTagTranslation($tagTranslation);

        if ($persist) {
            $this->persistAndFlush($tagTranslation);
        }
        return $tagTranslation;
    }
}
