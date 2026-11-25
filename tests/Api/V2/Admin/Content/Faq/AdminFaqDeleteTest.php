<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de DELETE /admin/faqs/{id}
 */

namespace App\Tests\Api\V2\Admin\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqQuestion;

class AdminFaqDeleteTest extends AdminFaqTestCase
{
    /**
     * Suppression de la FAQ et de son arbre tracée, FAQ affichée par une page refusée en 409
     * @return void
     */
    public function testDelete(): void
    {
        $this->setAllowDelete(true);
        $headers = $this->contributorHeaders($user);
        $faq = $this->createTitledFaq($user, 'Produit', ['Achat' => ['Q1']]);
        $questionId = $faq->getFaqCategories()->first()->getFaqQuestions()->first()->getId();
        $used = $this->createTitledFaq($user, 'Support');
        $this->usePage($used);
        $id = $faq->getId();

        $this->assertEquals(204, $this->requestV2('DELETE', '/admin/faqs/' . $id, headers: $headers)->getStatusCode());
        $this->assertNull($this->reload(Faq::class, $id));
        $this->assertNull($this->reload(FaqQuestion::class, $questionId));
        $this->assertSame([['delete', 'AdminFaq', (string) $id, 'Produit']], $this->logs());

        $content = $this->assertProblem(
            $this->requestV2('DELETE', '/admin/faqs/' . $used->getId(), headers: $headers),
            409,
        );
        $this->assertSame($this->translator->trans('faq.error.delete.used', ['nb' => 1], 'faq'), $content['detail']);
        $this->assertCount(1, $this->logs());
    }

    /**
     * FAQ d'un autre, suppression désactivée par l'option système, If-Match périmé
     * @return void
     */
    public function testRefused(): void
    {
        $this->setAllowDelete(true);
        $headers = $this->contributorHeaders($user);
        $other = $this->createTitledFaq(null, 'Produit');
        $mine = $this->createTitledFaq($user, 'Support');

        $this->assertProblem($this->requestV2('DELETE', '/admin/faqs/' . $other->getId(), headers: $headers), 403);
        $this->assertProblem(
            $this->requestV2(
                'DELETE',
                '/admin/faqs/' . $mine->getId(),
                headers: ['HTTP_IF_MATCH' => '"perime"', ...$headers],
            ),
            412,
        );
        $this->setAllowDelete(false);
        $this->assertProblem(
            $this->requestV2('DELETE', '/admin/faqs/' . $mine->getId(), headers: $this->adminHeaders()),
            403,
        );
        $this->assertSame([], $this->logs());
    }
}
