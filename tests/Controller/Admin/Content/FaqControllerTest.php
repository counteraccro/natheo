<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test controller FAQ
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Faq\Faq;
use App\Entity\Admin\Content\Faq\FaqCategory;
use App\Entity\Admin\Content\Faq\FaqQuestion;
use App\Enum\Admin\Content\Faq\FaqStatistiqueKey;
use App\Enum\Admin\Content\Page\PageContentType;
use App\Repository\Admin\Content\Faq\FaqRepository;
use App\Tests\AppWebTestCase;

class FaqControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_faq_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('faq.index.title', domain: 'faq'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        $userContributeur = $this->createUserContributeur();
        for ($i = 0; $i < 5; $i++) {
            $faq = $this->createFaqAllDataDefault($i < 2 ? $userContributeur : null);
        }

        $this->checkNoAccess('admin_faq_load_grid_data');

        $this->client->loginUser($userContributeur, 'admin');
        $url = $this->router->generate('admin_faq_load_grid_data', ['page' => 1, 'limit' => 4]);
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('column', $content);
        $this->assertArrayHasKey('sql', $content);
        $this->assertArrayHasKey('translate', $content);
        $this->assertEquals(5, $content['nb']);
        $this->assertCount(4, $content['data']);

        // Recherche sur le titre dans la locale courante
        $search = $faq->getFaqTranslationByLocale('fr')->getTitle();
        $this->client->request('GET', $url, ['search' => $search]);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, $content['nb']);

        // Filtre « mes FAQ »
        $this->client->request('GET', $url, ['filter' => 'me']);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(2, $content['nb']);

        // Tris autorisés ou non : jamais d'erreur 500
        foreach (['color', 'id', 'title', 'updateAt', 'inconnu'] as $orderField) {
            $this->client->request('GET', $url, ['orderField' => $orderField, 'order' => 'ASC']);
            $this->assertResponseIsSuccessful();
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals(5, $content['nb']);
        }
    }

    /**
     * Test méthode loadGridData() : titre échappé et actions selon les droits
     * @return void
     */
    public function testLoadGridDataEscapedAndRights(): void
    {
        $contributeur = $this->createUserContributeur();
        $faqOwn = $this->createFaqAllDataDefault($contributeur);
        $faqOther = $this->createFaqAllDataDefault();
        $faqOwn->getFaqTranslationByLocale('fr')->setTitle('<img src=x onerror=alert(1)>');
        $this->persistAndFlush($faqOwn);

        $this->client->loginUser($contributeur, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_faq_load_grid_data', ['page' => 1, 'limit' => 10]),
        );
        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringNotContainsString('<img', $content);

        $titleCol = $this->translator->trans('faq.grid.title', domain: 'faq');
        $rows = [];
        foreach (json_decode($content, true)['data'] as $row) {
            $rows[$row[$this->translator->trans('faq.grid.id', domain: 'faq')]] = $row;
        }
        $this->assertEquals('&lt;img src=x onerror=alert(1)&gt;', $rows[$faqOwn->getId()][$titleCol]);

        // Sa FAQ : désactiver, supprimer, éditer ; celle d'un autre : aucune action
        $this->assertCount(3, $rows[$faqOwn->getId()]['action']);
        foreach ($rows[$faqOwn->getId()]['action'] as $action) {
            if (isset($action['msgConfirm'])) {
                $this->assertStringNotContainsString('<img', $action['msgConfirm']);
            }
        }
        $this->assertCount(0, $rows[$faqOther->getId()]['action']);
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_faq_add');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('faq.add.title', domain: 'faq'));

        $faq = $this->createFaqAllDataDefault($user);
        $this->client->request('GET', $this->router->generate('admin_faq_update', ['id' => $faq->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('faq.update.title', domain: 'faq'));
        $this->assertPageTitleContains('#' . $faq->getId());

        // FAQ d'un autre contributeur
        $faqOther = $this->createFaqAllDataDefault();
        $this->client->request('GET', $this->router->generate('admin_faq_update', ['id' => $faqOther->getId()]));
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_update', ['id' => $faqOther->getId()]));
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        $user = $this->createUserContributeur();
        $faq = $this->createFaqAllDataDefault($user);
        $faqOther = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_disabled', ['id' => $faq->getId()], methode: 'PUT');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_faq_disabled', ['id' => $faq->getId()]);

        $this->client->request('PUT', $url);
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getGridCsrfToken('disabled');
        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        $this->em->clear();
        $this->assertTrue($this->em->getRepository(Faq::class)->find($faq->getId())->isDisabled());

        $this->client->request(
            'PUT',
            $this->router->generate('admin_faq_disabled', ['id' => $faqOther->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($this->translator->trans('faq.error.forbidden', domain: 'faq'), $content['msg']);
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $user = $this->createUserContributeur();
        $faq = $this->createFaqAllDataDefault($user);
        $faqUsed = $this->createFaqAllDataDefault($user);
        $faqOther = $this->createFaqAllDataDefault();

        // FAQ utilisée dans 2 pages
        for ($i = 0; $i < 2; $i++) {
            $page = $this->createPage();
            $this->createPageContent($page, ['type' => PageContentType::FAQ->value, 'typeId' => $faqUsed->getId()]);
        }

        $this->checkNoAccess('admin_faq_delete', ['id' => $faq->getId()], methode: 'DELETE');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_faq_delete', ['id' => $faq->getId()]);

        $this->client->request('DELETE', $url);
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getGridCsrfToken('delete');

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_faq_delete', ['id' => $faqUsed->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($this->translator->trans('faq.error.delete.used', ['nb' => 2], 'faq'), $content['msg']);

        $this->client->request(
            'DELETE',
            $this->router->generate('admin_faq_delete', ['id' => $faqOther->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('DELETE', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        $this->em->clear();
        /** @var FaqRepository $faqRepo */
        $faqRepo = $this->em->getRepository(Faq::class);
        $this->assertNull($faqRepo->find($faq->getId()));
        $this->assertNotNull($faqRepo->find($faqUsed->getId()));
        $this->assertNotNull($faqRepo->find($faqOther->getId()));

        // Admin : peut supprimer la FAQ d'un autre
        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_faq_delete', ['id' => $faqOther->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $this->getGridCsrfToken('delete')],
        );
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode loadFaq()
     * @return void
     */
    public function testLoadFaq(): void
    {
        $user = $this->createUserContributeur();
        $faq = $this->createFaqAllDataDefault($user);
        $faqOther = $this->createFaqAllDataDefault();

        $this->checkNoAccess('admin_faq_load_faq', ['id' => $faq->getId()]);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_faq_load_faq', ['id' => $faq->getId()]));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($faq->getId(), $content['faq']['id']);
        $this->assertEquals($faq->getAllMaxRender(), $content['max_render_order']);

        $this->client->request('GET', $this->router->generate('admin_faq_load_faq', ['id' => $faqOther->getId()]));
        $this->assertResponseStatusCodeSame(403);

        $this->client->request('GET', $this->router->generate('admin_faq_load_faq', ['id' => 999999]));
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test méthode save() : deux sauvegardes successives conservent les ids
     * @return void
     */
    public function testSave(): void
    {
        $user = $this->createUserContributeur();
        $faq = $this->createFaqAllDataDefault($user);
        $data = $this->faqToPopulateArray($faq);
        $data['faqCategories'][0]['faqQuestions'][0]['faqQuestionTranslations'][0]['title'] = 'Question modifiée';
        $categoryIds = array_column($data['faqCategories'], 'id');
        $questionIds = array_column($data['faqCategories'][0]['faqQuestions'], 'id');

        $this->checkNoAccess('admin_faq_save', methode: 'PUT');

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_faq_save');

        $this->client->request('PUT', $url, content: json_encode(['faq' => $data]));
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getPropsCsrfToken($faq->getId(), 'save');
        for ($i = 0; $i < 2; $i++) {
            $this->client->request(
                'PUT',
                $url,
                server: ['HTTP_X-CSRF-TOKEN' => $csrf],
                content: json_encode(['faq' => $data]),
            );
            $this->assertResponseIsSuccessful();
            $content = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertTrue($content['success']);
        }

        $this->em->clear();
        $verif = $this->em->getRepository(Faq::class)->find($faq->getId());
        $this->assertEquals(
            $categoryIds,
            $verif->getSortedFaqCategories()->map(fn(FaqCategory $c) => $c->getId())->getValues(),
        );
        $category = $this->em->getRepository(FaqCategory::class)->find($categoryIds[0]);
        $this->assertEqualsCanonicalizing(
            $questionIds,
            $category->getFaqQuestions()->map(fn(FaqQuestion $q) => $q->getId())->getValues(),
        );
        $question = $this->em->getRepository(FaqQuestion::class)->find($questionIds[0]);
        $this->assertEquals(
            'Question modifiée',
            $question->getFaqQuestionTranslations()->filter(fn($t) => $t->getLocale() === 'fr')->first()->getTitle(),
        );
        $this->assertEquals('2', $verif->getFaqStatistiqueByKey(FaqStatistiqueKey::NB_CATEGORIES->value)->getValue());
        $this->assertEquals('6', $verif->getFaqStatistiqueByKey(FaqStatistiqueKey::NB_QUESTIONS->value)->getValue());

        // FAQ d'un autre contributeur
        $faqOther = $this->createFaqAllDataDefault();
        $this->client->request(
            'PUT',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['faq' => $this->faqToPopulateArray($faqOther)]),
        );
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test méthode newFaq()
     * @return void
     */
    public function testNewFaq(): void
    {
        $this->checkNoAccess('admin_faq_new_faq', methode: 'POST');

        $user = $this->createUserContributeur();
        $data = ['title' => self::getFaker()->text(50)];

        $this->client->loginUser($user, 'admin');
        $url = $this->router->generate('admin_faq_new_faq');

        $this->client->request('POST', $url, content: json_encode($data));
        $this->assertResponseStatusCodeSame(403);

        $csrf = $this->getPropsCsrfToken(null, 'new');

        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['title' => '  ']),
        );
        $this->assertResponseStatusCodeSame(400);

        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertArrayHasKey('msg', $content);
        $this->assertArrayHasKey('url_redirect', $content);

        $tmp = explode('/', $content['url_redirect']);
        $id = $tmp[count($tmp) - 1];

        /** @var FaqRepository $faqRepo */
        $faqRepo = $this->em->getRepository(Faq::class);
        $verif = $faqRepo->find($id);
        $this->assertEquals($data['title'], $verif->getFaqTranslationByLocale('fr')->getTitle());
        $this->assertEquals($user->getId(), $verif->getUser()->getId());
    }

    /**
     * Retourne le premier jeton CSRF trouvé dans les actions de la grille dont l'URL contient $urlPart
     * @param string $urlPart
     * @return string
     */
    private function getGridCsrfToken(string $urlPart): string
    {
        $this->client->request(
            'GET',
            $this->router->generate('admin_faq_load_grid_data', ['page' => 1, 'limit' => 50]),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        foreach ($content['data'] as $row) {
            foreach ($row['action'] as $action) {
                if (is_array($action) && str_contains($action['url'] ?? '', $urlPart) && isset($action['csrf'])) {
                    return $action['csrf'];
                }
            }
        }
        $this->fail('Aucun jeton CSRF trouvé dans les actions de la grille pour ' . $urlPart);
    }

    /**
     * Retourne un jeton CSRF passé au composant Vue Faq (page d'ajout ou d'édition)
     * @param int|null $id
     * @param string $key save ou new
     * @return string
     */
    private function getPropsCsrfToken(?int $id, string $key): string
    {
        $url =
            $id === null
                ? $this->router->generate('admin_faq_add')
                : $this->router->generate('admin_faq_update', ['id' => $id]);
        $this->client->request('GET', $url);
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Content/Faq/Faq"]')
            ->attr('data-symfony--ux-vue--vue-props-value');
        return json_decode($props, true)['csrf_tokens'][$key];
    }
}
