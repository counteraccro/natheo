<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de PATCH /admin/faqs/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Api\V2\Resource\Admin\Content\AdminFaq;

class AdminFaqPatchTest extends AdminFaqTestCase
{
    /**
     * Modification partielle des titres, statut tracé disable / enable
     * @return void
     */
    public function testPatch(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit');
        $path = '/admin/faqs/' . $faq->getId();
        $id = (string) $faq->getId();

        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['translations' => [['locale' => 'en', 'title' => 'Product']]],
            headers: $headers,
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaq::class);
        $this->assertSame(
            ['Produit', 'Product', 'Produit es'],
            array_column($this->decode($response)['translations'], 'title'),
        );

        $this->assertTrue(
            $this->decode($this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers))['disabled'],
        );
        $this->assertFalse(
            $this->decode($this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $headers))['disabled'],
        );
        $this->assertSame(
            [
                ['update', 'AdminFaq', $id, 'Produit'],
                ['disable', 'AdminFaq', $id, 'Produit'],
                ['enable', 'AdminFaq', $id, 'Produit'],
            ],
            $this->logs(),
        );

        $content = $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['translations' => [['locale' => 'fr', 'title' => '']]],
                headers: $headers,
            ),
            422,
        );
        $this->assertSame('translations[0].title', $content['violations'][0]['propertyPath']);
    }

    /**
     * Un contributeur ne modifie pas la FAQ d'un autre, même sans effet, un administrateur modifie tout
     * @return void
     */
    public function testRights(): void
    {
        $path = '/admin/faqs/' . $this->createTitledFaq(null, 'Produit')->getId();
        $headers = $this->contributorHeaders();

        $content = $this->assertProblem(
            $this->requestV2('PATCH', $path, body: ['translations' => $this->titles('X')], headers: $headers),
            403,
        );
        $this->assertSame($this->translator->trans('faq.error.forbidden', domain: 'faq'), $content['detail']);
        $this->assertProblem($this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers), 403);
        $this->assertProblem($this->requestV2('PATCH', $path, body: [], headers: $headers), 403);
        $this->assertProblem($this->requestV2('PATCH', '/admin/faqs/999999', body: [], headers: $headers), 404);
        $this->assertEquals(
            200,
            $this->requestV2(
                'PATCH',
                $path,
                body: ['disabled' => true],
                headers: $this->adminHeaders(),
            )->getStatusCode(),
        );
    }

    /**
     * If-Match périmé : 412 ; une écriture dans l'arbre ne change pas la version de la FAQ
     * @return void
     */
    public function testConcurrency(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1']]);
        $path = '/admin/faqs/' . $faq->getId();
        $etag = $this->requestV2('GET', $path, headers: $headers)->headers->get('ETag');

        $categoryId = $faq->getFaqCategories()->first()->getId();
        $this->requestV2(
            'PATCH',
            '/admin/faq_categories/' . $categoryId,
            body: ['translations' => [['locale' => 'fr', 'title' => 'Paiement']]],
            headers: $headers,
        );
        $this->assertSame($etag, $this->requestV2('GET', $path, headers: $headers)->headers->get('ETag'));

        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['translations' => [['locale' => 'fr', 'title' => 'Produits']]],
            headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['disabled' => true],
                headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
            ),
            412,
        );
    }
}
