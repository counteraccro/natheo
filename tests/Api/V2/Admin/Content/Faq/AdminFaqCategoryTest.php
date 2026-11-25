<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des opérations de /admin/faq_categories
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Api\V2\Resource\Admin\Content\AdminFaqCategory;
use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;

class AdminFaqCategoryTest extends AdminFaqTestCase
{
    /**
     * Catégorie avec l'IRI de sa FAQ, ses titres et les droits de la FAQ
     * @return void
     */
    public function testGet(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1']]);
        $category = $faq->getFaqCategories()->first();

        $response = $this->requestV2('GET', '/admin/faq_categories/' . $category->getId(), headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaqCategory::class);
        $content = $this->decode($response);
        $this->assertSame('/api/v2/admin/faqs/' . $faq->getId(), $content['faq']);
        $this->assertSame(['Achat', 'Achat en', 'Achat es'], array_column($content['translations'], 'title'));
        $this->assertSame(1, $content['position']);
        $this->assertTrue($content['permissions']['edit']);
        $this->assertArrayNotHasKey('questions', $content);
        $this->assertProblem($this->requestV2('GET', '/admin/faq_categories/999999', headers: $headers), 404);
    }

    /**
     * Ajout en dernière position sans question, date de la FAQ mise à jour, droits de la FAQ, FAQ inconnue
     * @return void
     */
    public function testPost(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1']]);
        $this->em
            ->getConnection()
            ->executeStatement('UPDATE faq SET update_at = :date WHERE id = :id', [
                'date' => '2020-01-01 00:00:00',
                'id' => $faq->getId(),
            ]);
        $body = ['faq' => '/api/v2/admin/faqs/' . $faq->getId(), 'translations' => $this->titles('Livraison')];

        $response = $this->requestV2('POST', '/admin/faq_categories', body: $body, headers: $headers);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaqCategory::class);
        $content = $this->decode($response);
        $this->assertSame([2, 'Livraison'], [$content['position'], $content['title']]);
        $this->assertCount(0, $this->reload(FaqCategory::class, $content['id'])->getFaqQuestions());
        $this->assertGreaterThan(
            '2020-01-01',
            $this->reload(Faq::class, $faq->getId())->getUpdateAt()->format('Y-m-d'),
        );
        $this->assertSame([['create', 'AdminFaqCategory', (string) $content['id'], 'Livraison']], $this->logs());

        $other = $this->createTitledFaq(null, 'Support');
        $body['faq'] = '/api/v2/admin/faqs/' . $other->getId();
        $this->assertProblem($this->requestV2('POST', '/admin/faq_categories', body: $body, headers: $headers), 403);
        $body['faq'] = '/api/v2/admin/faqs/999999';
        $this->assertProblem($this->requestV2('POST', '/admin/faq_categories', body: $body, headers: $headers), 404);
        $body['faq'] = '/api/v2/admin/faqs/' . $faq->getId();
        $body['translations'] = [['locale' => 'fr', 'title' => 'Seul']];
        $content = $this->assertProblem(
            $this->requestV2('POST', '/admin/faq_categories', body: $body, headers: $headers),
            422,
        );
        $this->assertSame('translations', $content['violations'][0]['propertyPath']);
    }

    /**
     * Déplacement : les autres catégories sont renumérotées, position au-delà de la dernière placée en dernier
     * @return void
     */
    public function testPosition(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['A' => [], 'B' => [], 'C' => []]);
        [$a, , $c] = $faq->getFaqCategories()->toArray();

        $this->requestV2('PATCH', '/admin/faq_categories/' . $c->getId(), body: ['position' => 1], headers: $headers);
        $this->assertSame(['C', 'A', 'B'], $this->categoryTitles($faq));
        $response = $this->requestV2(
            'PATCH',
            '/admin/faq_categories/' . $c->getId(),
            body: ['position' => 9],
            headers: $headers,
        );
        $this->assertSame(3, $this->decode($response)['position']);
        $this->assertSame(['A', 'B', 'C'], $this->categoryTitles($faq));
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                '/admin/faq_categories/' . $a->getId(),
                body: ['position' => 0],
                headers: $headers,
            ),
            422,
        );
        $id = (string) $c->getId();
        $this->assertSame(
            [['update', 'AdminFaqCategory', $id, 'C'], ['update', 'AdminFaqCategory', $id, 'C']],
            $this->logs(),
        );
    }

    /**
     * Désactiver une catégorie désactive ses questions actives (une ligne de journal chacune), l'activer les laisse
     * désactivées
     * @return void
     */
    public function testStatus(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit');
        $category = $this->createTitledCategory($faq, 'Achat', 1);
        $active = $this->createTitledQuestion($category, 'Q1', 1);
        $this->createTitledQuestion($category, 'Q2', 2, true);
        $path = '/admin/faq_categories/' . $category->getId();
        $id = (string) $category->getId();

        $this->assertTrue(
            $this->decode($this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers))['disabled'],
        );
        $this->assertTrue($this->reload(FaqQuestion::class, $active->getId())->isDisabled());
        $this->assertFalse(
            $this->decode($this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $headers))['disabled'],
        );
        $this->assertTrue($this->reload(FaqQuestion::class, $active->getId())->isDisabled());
        $this->assertSame(
            [
                ['disable', 'AdminFaqQuestion', (string) $active->getId(), null],
                ['disable', 'AdminFaqCategory', $id, 'Achat'],
                ['enable', 'AdminFaqCategory', $id, 'Achat'],
            ],
            $this->logs(),
        );
    }

    /**
     * Droits de la FAQ : modification, désactivation, suppression ; 412 sur une version périmée
     * @return void
     */
    public function testRightsAndConcurrency(): void
    {
        $this->setAllowDelete(true);
        $faq = $this->createTitledFaq(null, 'Produit', ['Achat' => ['Q1']]);
        $path = '/admin/faq_categories/' . $faq->getFaqCategories()->first()->getId();
        $headers = $this->contributorHeaders();

        $this->assertProblem($this->requestV2('PATCH', $path, body: ['position' => 1], headers: $headers), 403);
        $this->assertProblem($this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers), 403);
        $this->assertProblem($this->requestV2('DELETE', $path, headers: $headers), 403);

        $admin = $this->adminHeaders();
        $etag = $this->requestV2('GET', $path, headers: $admin)->headers->get('ETag');
        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['disabled' => true],
            headers: ['HTTP_IF_MATCH' => $etag, ...$admin],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['disabled' => false],
                headers: ['HTTP_IF_MATCH' => $etag, ...$admin],
            ),
            412,
        );
    }

    /**
     * Suppression avec ses questions, les catégories suivantes remontent ; la dernière catégorie peut être supprimée
     * @return void
     */
    public function testDelete(): void
    {
        $this->setAllowDelete(true);
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['A' => ['Q1'], 'B' => [], 'C' => []]);
        [$a, $b, $c] = $faq->getFaqCategories()->toArray();
        $questionId = $a->getFaqQuestions()->first()->getId();

        $this->assertEquals(
            204,
            $this->requestV2('DELETE', '/admin/faq_categories/' . $a->getId(), headers: $headers)->getStatusCode(),
        );
        $this->assertNull($this->reload(FaqQuestion::class, $questionId));
        $this->assertSame(['B', 'C'], $this->categoryTitles($faq));
        $this->assertSame(
            [1, 2],
            array_map(fn(FaqCategory $category) => $category->getRenderOrder(), $this->sortedCategories($faq)),
        );
        $this->assertSame([['delete', 'AdminFaqCategory', (string) $a->getId(), 'A']], $this->logs());

        $this->requestV2('DELETE', '/admin/faq_categories/' . $b->getId(), headers: $headers);
        $this->assertEquals(
            204,
            $this->requestV2('DELETE', '/admin/faq_categories/' . $c->getId(), headers: $headers)->getStatusCode(),
        );
        $this->assertSame([], $this->categoryTitles($faq));
    }

    /**
     * Titres français des catégories, dans l'ordre
     * @param Faq $faq
     * @return string[]
     */
    private function categoryTitles(Faq $faq): array
    {
        return array_map(
            fn(FaqCategory $category) => $category->getFaqCategoryTranslationByLocale('fr')->getTitle(),
            $this->sortedCategories($faq),
        );
    }

    /**
     * @param Faq $faq
     * @return FaqCategory[]
     */
    private function sortedCategories(Faq $faq): array
    {
        $this->em->clear();
        return $this->em->getRepository(FaqCategory::class)->findBy(['faq' => $faq->getId()], ['renderOrder' => 'ASC']);
    }
}
