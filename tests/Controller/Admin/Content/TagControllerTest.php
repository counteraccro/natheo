<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test tagController
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Tag\Tag;
use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;

class TagControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_tag_index');

        $user = $this->createUserContributeur();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('tag.index.page_title_h1', domain: 'tag'));
    }

    /**
     * Test méthode loadGridData()
     * @return void
     */
    public function testLoadGridData(): void
    {
        $userContributeur = $this->createUserContributeur();
        for ($i = 0; $i < 10; $i++) {
            $this->createTagWithTranslations($i < 3 ? $userContributeur : null);
        }

        $this->checkNoAccess('admin_tag_load_grid_data');

        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_load_grid_data', ['page' => 1, 'limit' => 5]));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);

        $this->assertEquals(10, $content['nb']);
        $this->assertCount(5, $content['data']);
        $this->assertArrayHasKey('nb', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('column', $content);
        $this->assertArrayHasKey('sql', $content);
        $this->assertArrayHasKey('translate', $content);

        // Filtre "mes tags"
        $this->client->request(
            'GET',
            $this->router->generate('admin_tag_load_grid_data', ['page' => 1, 'limit' => 20, 'filter' => 'me']),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(3, $content['nb']);
    }

    /**
     * Les libellés et couleurs sont échappés dans la grille, les boutons dépendent des droits
     * @return void
     */
    public function testLoadGridDataEscapeAndRights(): void
    {
        $user = $this->createUserContributeur();
        $tag = $this->createTag($user, ['color' => '#123456']);
        $this->createTagTranslation($tag, ['locale' => 'fr', 'label' => '<img src=x onerror=alert(1)>']);
        $otherTag = $this->createTag(null, ['color' => 'url(x)']);
        $this->createTagTranslation($otherTag, ['locale' => 'fr', 'label' => 'autre']);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_load_grid_data', ['page' => 1, 'limit' => 20]));
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $label = $this->translator->trans('tag.grid.label', domain: 'tag');
        $color = $this->translator->trans('tag.grid.color', domain: 'tag');
        $rows = [];
        foreach ($content['data'] as $row) {
            $rows[$row[$this->translator->trans('tag.grid.id', domain: 'tag')]] = $row;
        }

        $this->assertStringNotContainsString('<img', $rows[$tag->getId()][$label]);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $rows[$tag->getId()][$label]);
        $this->assertStringNotContainsString('url(x)', $rows[$otherTag->getId()][$label]);
        $this->assertStringNotContainsString('url(x)', $rows[$otherTag->getId()][$color]);

        $this->assertCount(3, $rows[$tag->getId()]['action'], 'Propriétaire : désactiver, supprimer, éditer');
        $this->assertCount(0, $rows[$otherTag->getId()]['action'], "Tag d'un autre : aucune action");
    }

    /**
     * Test méthode updateDisabled()
     * @return void
     */
    public function testUpdateDisabled(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);

        $userContributeur = $this->createUserContributeur();
        $tag = $this->createTagWithTranslations($userContributeur, ['disabled' => false]);
        $url = $this->router->generate('admin_tag_update_disabled', ['id' => $tag->getId()]);

        $this->checkNoAccess('admin_tag_update_disabled', ['id' => $tag->getId()], 'PUT');

        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('PUT', $url);
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getGridCsrfToken('update-disabled');

        $otherTag = $this->createTagWithTranslations(null, ['disabled' => false]);
        $this->client->request(
            'PUT',
            $this->router->generate('admin_tag_update_disabled', ['id' => $otherTag->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('tag.error.forbidden');

        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertTrue($this->findTag($tag->getId())->isDisabled());

        $this->client->request('PUT', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertFalse($this->findTag($tag->getId())->isDisabled());
        $this->assertNotNull($repo->find($otherTag->getId()));
    }

    /**
     * Test méthode delete()
     * @return void
     */
    public function testDelete(): void
    {
        $userContributeur = $this->createUserContributeur();
        $tag = $this->createTagWithTranslations($userContributeur, ['disabled' => false]);
        $usedTag = $this->createTagWithTranslations($userContributeur, ['disabled' => false]);
        $this->createPageTag(tag: $usedTag);
        $this->createPageTag(tag: $usedTag);
        $url = $this->router->generate('admin_tag_delete', ['id' => $tag->getId()]);

        $this->checkNoAccess('admin_tag_delete', ['id' => $tag->getId()], 'DELETE');

        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('DELETE', $url);
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getGridCsrfToken('delete');

        // Tag d'un autre contributeur
        $otherTag = $this->createTagWithTranslations();
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_tag_delete', ['id' => $otherTag->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertNotNull($this->findTag($otherTag->getId()));

        // Tag utilisé par 2 pages
        $this->client->request(
            'DELETE',
            $this->router->generate('admin_tag_delete', ['id' => $usedTag->getId()]),
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('tag.error.delete.used', ['nb' => 2]);
        $this->assertNotNull($this->findTag($usedTag->getId()));

        // Option de suppression désactivée
        $this->setAllowDelete(false);
        $this->client->request('DELETE', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseStatusCodeSame(403);
        $this->assertNotNull($this->findTag($tag->getId()));
        $this->setAllowDelete(true);

        $this->client->request('DELETE', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf]);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertNull($this->findTag($tag->getId()));
    }

    /**
     * Un tag sans propriétaire n'est géré que par un admin
     * @return void
     */
    public function testTagWithoutOwner(): void
    {
        $tag = $this->createTagWithTranslations(null, ['disabled' => false]);
        $tag->setUser(null);
        $this->persistAndFlush($tag);
        $url = $this->router->generate('admin_tag_update', ['id' => $tag->getId()]);

        $this->client->loginUser($this->createUserContributeur(), 'admin');
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(403);

        $this->client->loginUser($this->createUserAdmin(), 'admin');
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test méthode add()
     * @return void
     */
    public function testAdd(): void
    {
        $this->checkNoAccess('admin_tag_add');

        $user = $this->createUserContributeur();
        $tag = $this->createTagWithTranslations($user, ['disabled' => false]);
        $otherTag = $this->createTagWithTranslations();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_add'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('tag.add.page_title_h1', domain: 'tag'));

        $this->client->request('GET', $this->router->generate('admin_tag_update', ['id' => $tag->getId()]));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('tag.update.page_title_h1', domain: 'tag'));

        $this->client->request('GET', $this->router->generate('admin_tag_update', ['id' => $otherTag->getId()]));
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);

        $data = $this->getSaveData('test-unitaire');
        $url = $this->router->generate('admin_tag_save');

        $this->checkNoAccess('admin_tag_save', methode: 'POST');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');

        $this->client->request('POST', $url, content: json_encode($data));
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getSaveCsrfToken();
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('new', $content['etat']);

        $tags = $repo->findAll();
        $this->assertCount(1, $tags);
        $tag = $tags[0];
        $this->assertEquals($data['tag']['color'], $tag->getColor());
        $this->assertEquals($userContributeur->getId(), $tag->getUser()?->getId(), 'Propriétaire = auteur');

        // Le propriétaire et les dates envoyés par le formulaire sont ignorés
        $other = $this->createUserContributeur();
        $otherTag = $this->createTagWithTranslations($other);
        $data['tag']['id'] = $tag->getId();
        $data['tag']['user'] = ['id' => $other->getId()];
        $data['tag']['tagTranslations'][0]['label'] = 'fr-modifie';
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $tag = $this->findTag($tag->getId());
        $this->assertEquals('fr-modifie', $tag->getTagTranslationByLocale('fr')->getLabel());
        $this->assertEquals($userContributeur->getId(), $tag->getUser()?->getId());
        $this->assertCount(3, $tag->getTagTranslations());

        // Couleur invalide et libellé vide
        $bad = $data;
        $bad['tag']['color'] = 'red';
        $bad['tag']['tagTranslations'][1]['label'] = '  ';
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($bad));
        $this->assertResponseStatusCodeSame(400);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString($this->translator->trans('tag.error.color', domain: 'tag'), $content['msg']);
        $this->assertStringContainsString(
            $this->translator->trans('tag.error.label.blank', domain: 'tag'),
            $content['msg'],
        );

        // Libellé déjà utilisé par un autre tag dans la même locale
        $duplicate = $this->getSaveData('test-unitaire');
        $duplicate['tag']['tagTranslations'][0]['label'] = 'fr-modifie';
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($duplicate));
        $this->assertResponseStatusCodeSame(400);
        $this->assertCount(2, $repo->findAll(), 'Aucun tag créé');

        // Tag d'un autre contributeur
        $data['tag']['id'] = $otherTag->getId();
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseStatusCodeSame(403);
        $this->assertJsonMsg('tag.error.forbidden');

        // Tag inexistant : erreur JSON 404 au lieu d'une erreur 500
        $data['tag']['id'] = 999999;
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($data));
        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonMsg('tag.error.not_found');
    }

    /**
     * Test méthode statistique()
     * @return void
     */
    public function testStatistique(): void
    {
        $user = $this->createUserContributeur();
        $tag = $this->createTagWithTranslations($user);

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_tag_stats', ['id' => $tag->getId()]));
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', $this->router->generate('admin_tag_stats', ['id' => 999999]));
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test méthode search
     * @return void
     */
    public function testSearch(): void
    {
        $tag = $this->createTag(null, ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => $locale . ' Un label']);
        }

        $this->checkNoAccess('admin_tag_search');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request(
            'GET',
            $this->router->generate('admin_tag_search', ['locale' => 'fr', 'search' => 'fr Un label']),
        );
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $content['result']);
        $this->assertEquals($tag->getId(), array_values($content['result'])[0]['id']);

        // Une locale qui tente une injection ne remonte rien d'autre : 404
        $base = $this->router->generate('admin_tag_search');
        $this->client->request('GET', $base . '/label/' . rawurlencode("fr' OR '1'='1"));
        $this->assertResponseStatusCodeSame(404);
        $this->client->request('GET', $base . '/label/xx');
        $this->assertResponseStatusCodeSame(404);

        // Recherche vide
        $this->client->request('GET', $base);
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test méthode getTagByName()
     * @return void
     */
    public function testGetTagByName(): void
    {
        /** @var TagRepository $repo */
        $repo = $this->em->getRepository(Tag::class);
        $url = $this->router->generate('admin_tag_tag_by_name');

        $this->checkNoAccess('admin_tag_tag_by_name', methode: 'POST');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');

        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame(405);

        $body = json_encode(['label' => 'label-unitaire', 'locale' => 'fr']);
        $this->client->request('POST', $url, content: $body);
        $this->assertResponseStatusCodeSame(403, 'Action sans jeton refusée');

        $csrf = $this->getTagByNameCsrfToken();
        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: $body);
        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);

        /** @var Tag $tag */
        $tag = $repo->findOneBy(['id' => $content['tag']['id']]);
        $this->assertEquals('label-unitaire', $tag->getTagTranslationByLocale('fr')->getLabel());
        $this->assertEquals($userContributeur->getId(), $tag->getUser()?->getId(), 'Propriétaire renseigné');

        // Tag existant : relu, pas recréé
        $existing = $this->createTag(null, ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($existing, ['locale' => $locale, 'label' => $locale . ' Un label']);
        }
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['label' => 'fr Un label', 'locale' => 'fr']),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals($existing->getId(), $content['tag']['id']);

        // Locale ou libellé invalide
        foreach ([['label' => 'abc', 'locale' => 'xx'], ['label' => '   ', 'locale' => 'fr']] as $invalid) {
            $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($invalid));
            $this->assertResponseStatusCodeSame(400);
        }
    }

    /**
     * Crée un tag avec une traduction par locale
     * @param User|null $user
     * @param array $customData
     * @return Tag
     */
    private function createTagWithTranslations(?User $user = null, array $customData = []): Tag
    {
        $tag = $this->createTag($user, $customData);
        foreach ($this->locales as $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale]);
        }
        return $tag;
    }

    /**
     * Données envoyées par le formulaire de tag
     * @param string $label
     * @return array
     */
    private function getSaveData(string $label): array
    {
        $translations = [];
        foreach (['fr', 'en', 'es'] as $locale) {
            $translations[] = ['id' => null, 'tag' => null, 'locale' => $locale, 'label' => $locale . '-' . $label];
        }
        return ['tag' => ['id' => null, 'color' => '#9e2e2e', 'disabled' => false, 'tagTranslations' => $translations]];
    }

    /**
     * Retourne le jeton CSRF d'une action de la grille dont l'url contient $urlPart
     * @param string $urlPart
     * @return string
     */
    private function getGridCsrfToken(string $urlPart): string
    {
        $this->client->request('GET', $this->router->generate('admin_tag_load_grid_data', ['page' => 1, 'limit' => 50]));
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
     * Retourne le jeton CSRF de sauvegarde passé au composant Vue TagForm
     * @return string
     */
    private function getSaveCsrfToken(): string
    {
        $this->client->request('GET', $this->router->generate('admin_tag_add'));
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Content/TagForm"]')
            ->attr('data-symfony--ux-vue--vue-props-value');
        return json_decode($props, true)['csrf_token_save'];
    }

    /**
     * Retourne le jeton CSRF d'ajout de tag passé au composant Vue Page
     * @return string
     */
    private function getTagByNameCsrfToken(): string
    {
        $this->client->request('GET', $this->router->generate('admin_page_add'));
        $props = $this->client
            ->getCrawler()
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/Content/Page/Page"]')
            ->attr('data-symfony--ux-vue--vue-props-value');
        return json_decode($props, true)['csrfTokenTag'];
    }

    /**
     * Vérifie le message d'erreur JSON de la dernière réponse
     * @param string $key clé de traduction du domaine tag
     * @param array $params
     * @return void
     */
    private function assertJsonMsg(string $key, array $params = []): void
    {
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($this->translator->trans($key, $params, 'tag'), $content['msg']);
    }

    /**
     * Relit un tag en base (sans le cache de l'EntityManager du test)
     * @param int $id
     * @return Tag|null
     */
    private function findTag(int $id): ?Tag
    {
        $this->em->clear();
        return $this->em->getRepository(Tag::class)->find($id);
    }

    /**
     * Active ou désactive l'option de suppression des données
     * @param bool $allow
     * @return void
     */
    private function setAllowDelete(bool $allow): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystem::OS_ALLOW_DELETE_DATA->value, $allow ? '1' : '0');
    }
}
