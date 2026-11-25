<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Test TagController
 */

namespace App\Tests\Controller\Admin\Content;

use App\Entity\Admin\Content\Tag\Tag;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Enum\Front\Template;
use App\Repository\Admin\Content\Tag\TagRepository;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;

class TagControllerTest extends AppWebTestCase
{
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

        // Même libellé avec une autre casse : relu
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['label' => 'FR UN LABEL', 'locale' => 'fr']),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($existing->getId(), $content['tag']['id']);

        // Tag désactivé : refusé
        $disabled = $this->createTag(null, ['disabled' => true]);
        $this->createTagTranslation($disabled, ['locale' => 'fr', 'label' => 'tag-desactive']);
        $this->client->request(
            'POST',
            $url,
            server: ['HTTP_X-CSRF-TOKEN' => $csrf],
            content: json_encode(['label' => 'tag-desactive', 'locale' => 'fr']),
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($this->translator->trans('tag.page.error.tag.disabled', domain: 'tag'), $content['msg']);

        // Locale ou libellé invalide
        foreach ([['label' => 'abc', 'locale' => 'xx'], ['label' => '   ', 'locale' => 'fr']] as $invalid) {
            $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $csrf], content: json_encode($invalid));
            $this->assertResponseStatusCodeSame(400);
        }
    }

    /**
     * Test méthode preview() : aperçu au rendu du thème actif dans chaque langue, sans les scripts du site
     * @return void
     */
    public function testPreview(): void
    {
        $this->checkNoAccess('admin_tag_preview', ['locale' => 'fr']);
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(
            OptionSystem::OS_FRONT_SCRIPT_TOP->value,
            '<script>window.scriptSite = true;</script>',
        );

        $this->client->loginUser($this->createUserContributeur(), 'admin');
        foreach ($this->locales as $locale) {
            $this->client->request('GET', $this->router->generate('admin_tag_preview', ['locale' => $locale]));
            $this->assertResponseIsSuccessful();
            $this->assertResponseHeaderSame('X-Robots-Tag', 'noindex');
            $content = (string) $this->client->getResponse()->getContent();
            $this->assertStringNotContainsString('scriptSite', $content);
            $this->assertSelectorExists('html[lang="' . $locale . '"][data-front-theme="natheo_horizon"]');
            $this->assertStringContainsString('Front/NatheoHorizon/TagPreview', $content);
        }

        $optionSystemService->saveValueByKee(OptionSystem::OS_THEME_FRONT_SITE->value, Template::NATHEO_STORM->value);
        $this->client->request('GET', $this->router->generate('admin_tag_preview', ['locale' => 'fr']));
        $this->assertStringContainsString(
            'Front/StormFront/TagPreview',
            (string) $this->client->getResponse()->getContent(),
        );
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
}
