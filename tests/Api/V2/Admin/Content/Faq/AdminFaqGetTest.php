<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de GET /admin/faqs/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Api\V2\Resource\Admin\Content\AdminFaq;

class AdminFaqGetTest extends AdminFaqTestCase
{
    /**
     * FAQ avec ses titres, ses catégories triées et le titre de leurs questions, sans les réponses
     * @return void
     */
    public function testGet(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit');
        $second = $this->createTitledCategory($faq, 'Livraison', 2, true);
        $first = $this->createTitledCategory($faq, 'Achat', 1);
        $this->createTitledQuestion($first, 'Q2', 2, true);
        $this->createTitledQuestion($first, 'Q1', 1);
        $this->createTitledQuestion($second, 'Q3', 1);

        $response = $this->requestV2('GET', '/admin/faqs/' . $faq->getId(), headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaq::class);
        $this->assertNotNull($response->headers->get('ETag'));
        $content = $this->decode($response);
        $this->assertSame(['Produit', 'Produit en', 'Produit es'], array_column($content['translations'], 'title'));
        $this->assertSame([2, 3], [$content['categoryCount'], $content['questionCount']]);
        $this->assertSame(['Achat', 'Livraison'], array_column($content['categories'], 'title'));
        $this->assertSame('/api/v2/admin/faq_categories/' . $first->getId(), $content['categories'][0]['@id']);
        $this->assertTrue($content['categories'][1]['disabled']);
        $this->assertSame(
            ['Achat', 'Achat en', 'Achat es'],
            array_column($content['categories'][0]['translations'], 'title'),
        );
        $questions = $content['categories'][0]['questions'];
        $this->assertSame(['Q1', 'Q2'], array_column($questions, 'title'));
        $this->assertSame([1, 2], array_column($questions, 'position'));
        $this->assertTrue($questions[1]['disabled']);
        $this->assertArrayNotHasKey('translations', $questions[0]);
        $this->assertArrayNotHasKey('permissions', $content['categories'][0]);

        $content = $this->decode(
            $this->requestV2('GET', '/admin/faqs/' . $faq->getId() . '?locale=es', headers: $headers),
        );
        $this->assertSame('Q1 es', $content['categories'][0]['questions'][0]['title']);
    }

    /**
     * 404 pour une FAQ inconnue, lecture permise à tout contributeur, refusée sans session back-office ou sans le rôle du module
     * @return void
     */
    public function testAccess(): void
    {
        $faq = $this->createTitledFaq(null, 'Produit');
        $headers = $this->contributorHeaders();

        $this->assertProblem($this->requestV2('GET', '/admin/faqs/999999', headers: $headers), 404);
        $content = $this->decode($this->requestV2('GET', '/admin/faqs/' . $faq->getId(), headers: $headers));
        $this->assertFalse($content['permissions']['edit']);
        $this->assertProblem($this->requestV2('GET', '/admin/faqs/' . $faq->getId()), 403);
        $user = $this->createUser(['disabled' => false, 'anonymous' => false, 'roles' => ['ROLE_USER']]);
        $this->assertProblem(
            $this->requestV2('GET', '/admin/faqs/' . $faq->getId(), headers: $this->adminSessionHeaders($user)),
            403,
        );
    }
}
