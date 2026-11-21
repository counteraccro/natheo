<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de POST /admin/tags
 */

namespace App\Tests\Api\V2\Admin\Content\Tag;

use App\Api\V2\Resource\Admin\Content\AdminTag;
use App\Entity\Admin\Content\Tag\Tag;

class AdminTagPostTest extends AdminTagTestCase
{
    /**
     * Création avec toutes les langues, auteur de la session, journal
     * @return void
     */
    public function testPost(): void
    {
        $headers = $this->contributorHeaders($user);

        $response = $this->requestV2(
            'POST',
            '/admin/tags',
            body: [
                'color' => '#4f46e5',
                'disabled' => true,
                'translations' => [
                    ['locale' => 'en', 'label' => ' Symfony en '],
                    ['locale' => 'fr', 'label' => 'Symfony'],
                    ['locale' => 'es', 'label' => 'Symfony es'],
                ],
            ],
            headers: $headers,
        );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminTag::class);
        $content = $this->decode($response);
        $this->assertSame(
            ['Symfony', '#4f46e5', true, $user->getId()],
            [$content['label'], $content['color'], $content['disabled'], $content['author']['id']],
        );
        $this->assertSame(['Symfony', 'Symfony en', 'Symfony es'], array_column($content['translations'], 'label'));
        $this->assertSame([['create', (string) $content['id'], 'Symfony']], $this->logs());
    }

    /**
     * Une langue absente reçoit un libellé libre, une couleur absente est tirée au hasard
     * @return void
     */
    public function testPostOneLocale(): void
    {
        $headers = $this->contributorHeaders();
        $this->createLabelledTag(null, 'Autre', []);
        $this->createTagTranslation($this->createTag(null, ['color' => '#000000']), [
            'locale' => 'en',
            'label' => 'PHP (en)',
        ]);

        $content = $this->decode(
            $this->requestV2(
                'POST',
                '/admin/tags',
                body: [
                    'translations' => [['locale' => 'fr', 'label' => 'PHP']],
                ],
                headers: $headers,
            ),
        );
        $this->assertSame(['PHP', 'PHP (en 2)', 'PHP (es)'], array_column($content['translations'], 'label'));
        $this->assertMatchesRegularExpression(Tag::COLOR_PATTERN, $content['color']);
        $this->assertFalse($content['disabled']);
    }

    /**
     * Couleur, libellés vides, trop longs, en double à la casse près, langue inconnue ou répétée
     * @return void
     */
    public function testValidation(): void
    {
        $headers = $this->contributorHeaders();
        $this->createLabelledTag(null, 'Symfony');

        $content = $this->assertProblem(
            $this->requestV2(
                'POST',
                '/admin/tags',
                body: [
                    'color' => 'rouge',
                    'translations' => [
                        ['locale' => 'fr', 'label' => 'SYMFONY'],
                        ['locale' => 'en', 'label' => '  '],
                        ['locale' => 'es', 'label' => str_repeat('a', 256)],
                        ['locale' => 'de', 'label' => 'Symfony'],
                        ['locale' => 'fr', 'label' => 'Autre'],
                    ],
                ],
                headers: $headers,
            ),
            422,
        );
        $this->assertSame(
            [
                'color' => $this->translator->trans('tag.error.color', domain: 'tag'),
                'translations[0].label' => $this->translator->trans(
                    'tag.error.duplicate_label',
                    ['label' => 'SYMFONY'],
                    'tag',
                ),
                'translations[1].label' => $this->translator->trans('tag.error.label.blank', domain: 'tag'),
                'translations[2].label' => $this->translator->trans('tag.error.label.length', domain: 'tag'),
                'translations[3].locale' => $this->translator->trans('tag.error.locale', ['locale' => 'de'], 'tag'),
                'translations[4].locale' => $this->translator->trans(
                    'tag.error.locale_duplicate',
                    ['locale' => 'fr'],
                    'tag',
                ),
            ],
            array_column($content['violations'], 'message', 'propertyPath'),
        );

        $content = $this->assertProblem(
            $this->requestV2('POST', '/admin/tags', body: ['translations' => []], headers: $headers),
            422,
        );
        $this->assertSame('translations', $content['violations'][0]['propertyPath']);
        $this->assertSame([], $this->logs());
    }
}
