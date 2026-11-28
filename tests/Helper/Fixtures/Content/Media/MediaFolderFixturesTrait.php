<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixture pour les dossiers de la médiathèque
 */

namespace App\Tests\Helper\Fixtures\Content\Media;

use App\Entity\Admin\Content\Media\MediaFolder;
use App\Entity\Admin\System\User;
use App\Tests\Helper\FakerTrait;

trait MediaFolderFixturesTrait
{
    use FakerTrait;

    /**
     * Création d'un dossier, son chemin d'identifiants calculé après l'enregistrement
     * @param MediaFolder|null $parent
     * @param User|null $user propriétaire, aucun si null
     * @param array $customData
     * @return MediaFolder
     */
    public function createMediaFolder(
        ?MediaFolder $parent = null,
        ?User $user = null,
        array $customData = [],
    ): MediaFolder {
        $data = [
            'name' => self::getFaker()->slug(2),
            'parent' => $parent,
            'user' => $user,
        ];

        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->initEntity(MediaFolder::class, array_merge($data, $customData));
        $this->persistAndFlush($mediaFolder);
        $mediaFolder->setTreePath($mediaFolder->buildTreePath());
        $this->em->flush();
        return $mediaFolder;
    }
}
