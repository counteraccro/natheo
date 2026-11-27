<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de ApiAdminTagService
 */

namespace App\Tests\Service\Api\Admin\Content;

use App\Api\V2\Exception\ConflictException;
use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\Content\Tag\TagTranslation;
use App\Service\Api\Admin\Content\ApiAdminTagService;
use App\Tests\AppWebTestCase;

class ApiAdminTagServiceTest extends AppWebTestCase
{
    private ApiAdminTagService $tagService;

    public function setUp(): void
    {
        parent::setUp();
        $this->tagService = $this->container->get(ApiAdminTagService::class);
    }

    /**
     * Libellé trouvé sans tenir compte de la casse, hors tag exclu
     * @return void
     */
    public function testFindByLabel(): void
    {
        $tag = $this->createTag();
        $this->createTagTranslation($tag, ['locale' => 'fr', 'label' => 'Été']);

        $this->assertSame($tag->getId(), $this->tagService->findByLabel('fr', ' éTÉ ')?->getId());
        $this->assertNull($this->tagService->findByLabel('en', 'Été'));
        $this->assertNull($this->tagService->findByLabel('fr', 'Été', $tag->getId()));
    }

    /**
     * Libellé libre suffixé par la langue, numéroté quand il est déjà pris, sans dépasser 255 caractères
     * @return void
     */
    public function testFreeLabel(): void
    {
        $this->assertSame('PHP (en)', $this->tagService->getFreeLabel('en', 'PHP'));
        $this->createTagTranslation($this->createTag(), ['locale' => 'en', 'label' => 'php (EN)']);
        $this->assertSame('PHP (en 2)', $this->tagService->getFreeLabel('en', 'PHP'));
        $this->assertSame(255, mb_strlen($this->tagService->getFreeLabel('en', str_repeat('é', 255))));
    }

    /**
     * Création dans toutes les langues, couleur tirée au hasard, langue inconnue ignorée
     * @return void
     */
    public function testCreateAndUpdate(): void
    {
        $user = $this->createUserContributeur();
        $tag = $this->tagService->create($user, ['es' => ' Hola ', 'de' => 'Hallo']);

        $labels = $tag->getTagTranslations()->map(fn(TagTranslation $t) => $t->getLocale() . ':' . $t->getLabel());
        $this->assertSame(['fr:Hola (fr)', 'en:Hola (en)', 'es:Hola'], array_values($labels->toArray()));
        $this->assertMatchesRegularExpression(Tag::COLOR_PATTERN, (string) $tag->getColor());
        $this->assertSame($user->getId(), $tag->getUser()?->getId());

        $this->tagService->update($tag, ['fr' => 'Bonjour'], disabled: true);
        $this->assertSame('Bonjour', $tag->getTagTranslationByLocale('fr')?->getLabel());
        $this->assertSame('Hola', $tag->getTagTranslationByLocale('es')?->getLabel());
        $this->assertTrue($tag->isDisabled());

        $this->expectException(\InvalidArgumentException::class);
        $this->tagService->create($user, ['de' => 'Hallo']);
    }

    /**
     * Un tag rattaché à une page n'est pas supprimé
     * @return void
     */
    public function testDelete(): void
    {
        $tag = $this->createTag();
        $this->createPageTag(tag: $tag);
        try {
            $this->tagService->delete($tag);
            $this->fail('ConflictException attendue');
        } catch (ConflictException) {
            $this->assertSame(1, $this->tagService->countPages($tag));
        }

        $free = $this->createTag();
        $id = $free->getId();
        $this->tagService->delete($free);
        $this->assertNull($this->tagService->find($id));
    }
}
