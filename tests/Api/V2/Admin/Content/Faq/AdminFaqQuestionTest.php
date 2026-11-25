<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des opérations de /admin/faq_questions
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Api\V2\Resource\Admin\Content\AdminFaqQuestion;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;

class AdminFaqQuestionTest extends AdminFaqTestCase
{
    /**
     * Question avec l'IRI de sa FAQ et de sa catégorie, titre et réponse de chaque langue
     * @return void
     */
    public function testGet(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1']]);
        $category = $faq->getFaqCategories()->first();
        $question = $category->getFaqQuestions()->first();

        $response = $this->requestV2('GET', '/admin/faq_questions/' . $question->getId(), headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaqQuestion::class);
        $content = $this->decode($response);
        $this->assertSame('/api/v2/admin/faqs/' . $faq->getId(), $content['faq']);
        $this->assertSame('/api/v2/admin/faq_categories/' . $category->getId(), $content['category']);
        $this->assertSame(
            ['Réponse Q1', 'Réponse Q1 en', 'Réponse Q1 es'],
            array_column($content['translations'], 'answer'),
        );
        $this->assertProblem($this->requestV2('GET', '/admin/faq_questions/999999', headers: $headers), 404);
    }

    /**
     * Ajout en dernière position de la catégorie, titre et réponse exigés, droits de la FAQ
     * @return void
     */
    public function testPost(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1']]);
        $category = $faq->getFaqCategories()->first();
        $body = [
            'category' => '/api/v2/admin/faq_categories/' . $category->getId(),
            'translations' => array_map(
                fn(array $t) => [...$t, 'answer' => '**Oui** ' . $t['locale']],
                $this->titles('Q2'),
            ),
        ];

        $response = $this->requestV2('POST', '/admin/faq_questions', body: $body, headers: $headers);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminFaqQuestion::class);
        $content = $this->decode($response);
        $this->assertSame(
            [2, 'Q2', '**Oui** fr'],
            [$content['position'], $content['title'], $content['translations'][0]['answer']],
        );
        $this->assertSame([['create', 'AdminFaqQuestion', (string) $content['id'], 'Q2']], $this->logs());

        $body['translations'][1]['answer'] = ' ';
        $content = $this->assertProblem(
            $this->requestV2('POST', '/admin/faq_questions', body: $body, headers: $headers),
            422,
        );
        $this->assertSame('translations[1].answer', $content['violations'][0]['propertyPath']);

        $body['translations'][1]['answer'] = 'Answer';
        $other = $this->createTitledFaq(null, 'Support', ['Aide' => []]);
        $body['category'] = '/api/v2/admin/faq_categories/' . $other->getFaqCategories()->first()->getId();
        $this->assertProblem($this->requestV2('POST', '/admin/faq_questions', body: $body, headers: $headers), 403);
    }

    /**
     * Déplacement dans la catégorie puis vers une autre catégorie de la même FAQ : les deux sont renumérotées ;
     * catégorie d'une autre FAQ refusée
     * @return void
     */
    public function testMove(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['A' => ['Q1', 'Q2', 'Q3'], 'B' => ['Q4']]);
        $otherCategoryId = $this->createTitledFaq($user, 'Support', ['Aide' => []])
            ->getFaqCategories()
            ->first()
            ->getId();
        [$a, $b] = $faq->getFaqCategories()->toArray();
        [$q1, $q2] = $a->getFaqQuestions()->toArray();

        $this->requestV2('PATCH', '/admin/faq_questions/' . $q1->getId(), body: ['position' => 3], headers: $headers);
        $this->assertSame(['Q2', 'Q3', 'Q1'], $this->questionTitles($a));

        $response = $this->requestV2(
            'PATCH',
            '/admin/faq_questions/' . $q2->getId(),
            body: ['category' => '/api/v2/admin/faq_categories/' . $b->getId(), 'position' => 1],
            headers: $headers,
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('/api/v2/admin/faq_categories/' . $b->getId(), $this->decode($response)['category']);
        $this->assertSame(['Q3', 'Q1'], $this->questionTitles($a));
        $this->assertSame([1, 2], $this->questionPositions($a));
        $this->assertSame(['Q2', 'Q4'], $this->questionTitles($b));

        $this->requestV2(
            'PATCH',
            '/admin/faq_questions/' . $q1->getId(),
            body: ['category' => '/api/v2/admin/faq_categories/' . $b->getId()],
            headers: $headers,
        );
        $this->assertSame(['Q2', 'Q4', 'Q1'], $this->questionTitles($b));

        $content = $this->assertProblem(
            $this->requestV2(
                'PATCH',
                '/admin/faq_questions/' . $q1->getId(),
                body: ['category' => '/api/v2/admin/faq_categories/' . $otherCategoryId],
                headers: $headers,
            ),
            422,
        );
        $this->assertSame('category', $content['violations'][0]['propertyPath']);
    }

    /**
     * Textes modifiés langue par langue, statut tracé, droits de désactivation de la FAQ, 412
     * @return void
     */
    public function testUpdate(): void
    {
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['A' => ['Q1']]);
        $question = $faq->getFaqCategories()->first()->getFaqQuestions()->first();
        $path = '/admin/faq_questions/' . $question->getId();
        $id = (string) $question->getId();

        $etag = $this->requestV2('GET', $path, headers: $headers)->headers->get('ETag');
        $response = $this->requestV2(
            'PATCH',
            $path,
            body: ['translations' => [['locale' => 'en', 'title' => 'Question', 'answer' => 'Answer']]],
            headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame(['Q1', 'Question', 'Q1 es'], array_column($this->decode($response)['translations'], 'title'));
        $this->assertProblem(
            $this->requestV2(
                'PATCH',
                $path,
                body: ['disabled' => true],
                headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
            ),
            412,
        );
        $this->requestV2('PATCH', $path, body: ['disabled' => true], headers: $headers);
        $this->assertSame(
            [['update', 'AdminFaqQuestion', $id, 'Q1'], ['disable', 'AdminFaqQuestion', $id, 'Q1']],
            $this->logs(),
        );

        $this->assertProblem(
            $this->requestV2('PATCH', $path, body: ['disabled' => false], headers: $this->contributorHeaders()),
            403,
        );
    }

    /**
     * Suppression, les questions suivantes remontent ; la dernière question d'une catégorie peut être supprimée
     * @return void
     */
    public function testDelete(): void
    {
        $this->setAllowDelete(true);
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['A' => ['Q1', 'Q2']]);
        $category = $faq->getFaqCategories()->first();
        [$q1, $q2] = $category->getFaqQuestions()->toArray();

        $this->assertEquals(
            204,
            $this->requestV2('DELETE', '/admin/faq_questions/' . $q1->getId(), headers: $headers)->getStatusCode(),
        );
        $this->assertSame(['Q2'], $this->questionTitles($category));
        $this->assertSame([1], $this->questionPositions($category));
        $this->assertEquals(
            204,
            $this->requestV2('DELETE', '/admin/faq_questions/' . $q2->getId(), headers: $headers)->getStatusCode(),
        );
        $this->assertSame([], $this->questionTitles($category));
        $this->assertProblem(
            $this->requestV2('DELETE', '/admin/faq_questions/' . $q2->getId(), headers: $headers),
            404,
        );
        $this->assertSame(['delete', 'AdminFaqQuestion', (string) $q1->getId(), 'Q1'], $this->logs()[0]);
    }

    /**
     * @param FaqCategory $category
     * @return string[]
     */
    private function questionTitles(FaqCategory $category): array
    {
        return array_map(
            fn(FaqQuestion $question) => $question->getFaqQuestionTranslationByLocale('fr')->getTitle(),
            $this->sortedQuestions($category),
        );
    }

    /**
     * @param FaqCategory $category
     * @return int[]
     */
    private function questionPositions(FaqCategory $category): array
    {
        return array_map(fn(FaqQuestion $question) => $question->getRenderOrder(), $this->sortedQuestions($category));
    }

    /**
     * @param FaqCategory $category
     * @return FaqQuestion[]
     */
    private function sortedQuestions(FaqCategory $category): array
    {
        $this->em->clear();
        return $this->em
            ->getRepository(FaqQuestion::class)
            ->findBy(['faqCategory' => $category->getId()], ['renderOrder' => 'ASC']);
    }
}
