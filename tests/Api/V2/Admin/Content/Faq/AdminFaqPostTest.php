<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de POST /admin/faqs
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Api\V2\Resource\Admin\Content\AdminFaq;
use App\Entity\Admin\Content\Faq\Faq;

class AdminFaqPostTest extends AdminFaqTestCase
{
    /**
     * FAQ créée avec une catégorie et une question aux textes par défaut traduits, auteur et journal
     * @return void
     */
    public function testPost(): void
    {
        $headers = $this->contributorHeaders($user);

        $response = $this->requestV2(
            'POST',
            '/admin/faqs',
            body: ['translations' => $this->titles('Produit')],
            headers: $headers,
        );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaq::class);
        $content = $this->decode($response);
        $this->assertSame(['Produit', 'Produit en', 'Produit es'], array_column($content['translations'], 'title'));
        $this->assertSame($user->getLogin(), $content['author']['login']);
        $this->assertFalse($content['disabled']);
        $this->assertSame([1, 1], [$content['categoryCount'], $content['questionCount']]);
        $this->assertSame(
            $this->translator->trans('faq.default.category_title', domain: 'faq', locale: 'fr'),
            $content['categories'][0]['title'],
        );
        $this->assertSame(
            $this->translator->trans('faq.default.question_title', domain: 'faq', locale: 'fr'),
            $content['categories'][0]['questions'][0]['title'],
        );

        $faq = $this->reload(Faq::class, $content['id']);
        $answer = $faq
            ->getFaqCategories()
            ->first()
            ->getFaqQuestions()
            ->first()
            ->getFaqQuestionTranslationByLocale('en');
        $this->assertSame(
            $this->translator->trans('faq.default.question_answer', domain: 'faq', locale: 'en'),
            $answer->getAnswer(),
        );
        $this->assertSame([['create', 'AdminFaq', (string) $content['id'], 'Produit']], $this->logs());
    }

    /**
     * Titre exigé dans chaque langue, non vide, langue connue et unique
     * @return void
     */
    public function testValidation(): void
    {
        $headers = $this->contributorHeaders();

        $content = $this->assertProblem(
            $this->requestV2(
                'POST',
                '/admin/faqs',
                body: ['translations' => [['locale' => 'fr', 'title' => 'Produit']]],
                headers: $headers,
            ),
            422,
        );
        $this->assertSame('translations', $content['violations'][0]['propertyPath']);
        $this->assertStringContainsString('en, es', $content['violations'][0]['message']);

        $translations = $this->titles('Produit');
        $translations[1]['title'] = '  ';
        $translations[2]['locale'] = 'de';
        $translations[] = ['locale' => 'fr', 'title' => 'Doublon'];
        $translations[] = ['locale' => 'es', 'title' => str_repeat('a', 256)];
        $content = $this->assertProblem(
            $this->requestV2('POST', '/admin/faqs', body: ['translations' => $translations], headers: $headers),
            422,
        );
        $this->assertSame(
            ['translations[1].title', 'translations[2].locale', 'translations[3].locale', 'translations[4].title'],
            array_column($content['violations'], 'propertyPath'),
        );
        $this->assertSame([], $this->logs());
    }
}
