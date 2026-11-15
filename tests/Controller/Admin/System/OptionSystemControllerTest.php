<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test OptionSystemController
 */

namespace App\Tests\Controller\Admin\System;

use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\Content\Page\PageValidationStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OptionSystemControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index()
     * @return void
     */
    public function testIndex(): void
    {
        $this->checkNoAccess('admin_option-system_change');

        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $this->client->request('GET', $this->router->generate('admin_option-system_change'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains(
            'h1',
            $this->translator->trans('option_system.index.title', domain: 'option_system'),
        );
    }

    /**
     * Test que les valeurs des options sont échappées dans le formulaire
     * @return void
     */
    public function testIndexEscapeValue(): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystem::OS_FRONT_SCRIPT_TOP->value, '</textarea><script>alert(1)</script>');

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $this->client->request('GET', $this->router->generate('admin_option-system_change'));
        $this->assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        $this->assertStringNotContainsString('</textarea><script>alert(1)</script>', $content);
        $this->assertStringContainsString('&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;', $content);
    }

    /**
     * Test qu'une option traduisible affiche un champ échappé par langue
     * @return void
     */
    public function testIndexTranslatable(): void
    {
        $key = OptionSystem::OS_FRONT_FOOTER_TEXTE->value;
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee($key, json_encode(['fr' => 'Texte FR', 'es' => '<b>ES</b>']));

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $crawler = $this->client->request('GET', $this->router->generate('admin_option-system_change'));
        $this->assertResponseIsSuccessful();

        $this->assertCount(0, $crawler->filter('#' . $key));
        $this->assertSelectorTextSame('#' . $key . '-fr', 'Texte FR');
        $this->assertSelectorTextSame('#' . $key . '-en', '');
        $this->assertSelectorTextSame('#' . $key . '-es', '<b>ES</b>');
        $this->assertEquals('es', $crawler->filter('#' . $key . '-es')->attr('data-locale'));
        $this->assertEquals($key, $crawler->filter('#' . $key . '-es')->attr('data-key'));
        $this->assertCount(1, $crawler->filter('#fallback-' . $key . '-en'));

        $label = OptionSystem::OS_FRONT_CONSENT_ACCEPT_LABEL->value;
        $this->assertEquals('input', $crawler->filter('#' . $label . '-en')->nodeName());
        $this->assertEquals('1', $crawler->filter('#' . $label . '-en')->attr('data-fallback-label'));
    }

    /**
     * Test que l'onglet multilingue affiche le sélecteur de langue, seuls les champs de la langue par défaut visibles
     * @return void
     */
    public function testIndexMultilingualTab(): void
    {
        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $crawler = $this->client->request('GET', $this->router->generate('admin_option-system_change'));
        $this->assertResponseIsSuccessful();

        $panel = $crawler->filter('[data-option-multilingual]');
        $this->assertCount(1, $panel);
        $this->assertEquals('fr', $panel->attr('data-default-locale'));
        $this->assertStringContainsString('Français', (string) $panel->attr('data-msg-fallback-default'));

        $buttons = $panel->filter('[data-option-locale-button]');
        $this->assertEquals(
            ['fr', 'en', 'es'],
            $buttons->each(fn($button) => $button->attr('data-option-locale-button')),
        );
        $this->assertEquals('true', $buttons->first()->attr('aria-pressed'));

        $key = OptionSystem::OS_FRONT_CONSENT_TEXT->value;
        $this->assertCount(1, $panel->filter('#' . $key . '-fr'));
        $this->assertCount(0, $panel->filter('div.hidden[data-option-locale="fr"] #' . $key . '-fr'));
        $this->assertCount(1, $panel->filter('div.hidden[data-option-locale="en"] #' . $key . '-en'));
        $this->assertCount(1, $panel->filter('#' . OptionSystem::OS_FRONT_CONSENT_ENABLED->value));
        $this->assertCount(1, $panel->filter('#' . OptionSystem::OS_FRONT_FOOTER_TEXTE->value . '-es'));
    }

    /**
     * Test méthode update()
     * @return void
     */
    public function testUpdate(): void
    {
        $this->checkNoAccess('admin_option-system_ajax_update', methode: 'POST');
        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getCsrfToken()];

        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);

        $content = $this->postUpdate(['key' => OptionSystem::OS_THEME_SITE->value, 'value' => 'green'], $server);
        $this->assertResponseIsSuccessful();
        $this->assertTrue($content['success']);
        $this->assertEquals('green', $optionSystemService->getValueByKey(OptionSystem::OS_THEME_SITE->value));

        $content = $this->postUpdate(
            ['key' => OptionSystem::OS_THEME_SITE->value, 'value' => 'blue'],
            [
                'HTTP_X-CSRF-TOKEN' => 'jeton-invalide',
            ],
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertFalse($content['success']);
        $this->assertEquals('green', $optionSystemService->getValueByKey(OptionSystem::OS_THEME_SITE->value));

        $content = $this->postUpdate(['key' => OptionSystem::OS_THEME_SITE->value], $server);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertFalse($content['success']);

        $invalidData = [
            ['key' => OptionSystem::OS_THEME_SITE->value, 'value' => 'theme-inexistant'],
            ['key' => OptionSystem::OS_MEDIA_PATH->value, 'value' => '../../..'],
            ['key' => OptionSystem::OS_MAIL_FROM->value, 'value' => 'not-an-email'],
            ['key' => OptionSystem::OS_FRONT_FOOTER_SOCIAL_X_URL->value, 'value' => 'javascript:alert(1)'],
            ['key' => OptionSystem::OS_SITE_NAME->value, 'value' => ''],
            ['key' => 'OS_KEY_NOT_EXIST', 'value' => 'value'],
        ];
        foreach ($invalidData as $data) {
            $before = $optionSystemService->getValueByKey($data['key']);
            $content = $this->postUpdate($data, $server);
            $this->assertResponseIsSuccessful();
            $this->assertFalse($content['success'], $data['key']);
            $this->assertEquals($before, $optionSystemService->getValueByKey($data['key']), $data['key']);
        }
    }

    /**
     * Test méthode resetConsent() : date enregistrée, jamais modifiable par la route update()
     * @return void
     */
    public function testResetConsent(): void
    {
        $this->checkNoAccess('admin_option-system_ajax_consent_reset', methode: 'POST');
        $key = OptionSystem::OS_FRONT_CONSENT_RESET_AT->value;
        $url = $this->router->generate('admin_option-system_ajax_consent_reset');
        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $crawler = $this->client->request('GET', $this->router->generate('admin_option-system_change'));
        $this->assertCount(1, $crawler->filter('[data-option-action="' . $key . '"]'));
        $this->assertSelectorTextContains('#last-' . $key, 'jamais');
        $props = json_decode(
            (string) $crawler
                ->filter('[data-symfony--ux-vue--vue-component-value="Admin/System/Option"]')
                ->attr('data-symfony--ux-vue--vue-props-value'),
            true,
        );
        $this->assertEquals($url, $props['url_actions'][$key]);

        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => 'jeton-invalide']);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->assertNull($optionSystemService->getValueByKey($key));

        $this->client->request('POST', $url, server: ['HTTP_X-CSRF-TOKEN' => $props['csrf_token']]);
        $this->assertResponseIsSuccessful();
        $content = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertStringContainsString('Dernière demande', $content['last']);
        $value = (string) $optionSystemService->getValueByKey($key);
        $this->assertNotFalse(\DateTimeImmutable::createFromFormat(DATE_ATOM, $value));

        $content = $this->postUpdate(['key' => $key, 'value' => '2000-01-01T00:00:00+00:00'], [
            'HTTP_X-CSRF-TOKEN' => $props['csrf_token'],
        ]);
        $this->assertFalse($content['success']);
        $this->assertEquals($value, $optionSystemService->getValueByKey($key));
    }

    /**
     * Test méthode update() sur une option traduisible
     * @return void
     */
    public function testUpdateTranslatable(): void
    {
        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getCsrfToken()];
        $key = OptionSystem::OS_FRONT_FOOTER_TEXTE->value;

        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);

        $content = $this->postUpdate(['key' => $key, 'value' => 'Texte espagnol', 'locale' => 'es'], $server);
        $this->assertResponseIsSuccessful();
        $this->assertTrue($content['success']);
        $this->assertEquals('Texte espagnol', $optionSystemService->getTranslatedValueByKey($key, 'es'));

        $content = $this->postUpdate(['key' => $key, 'value' => 'Sans langue'], $server);
        $this->assertFalse($content['success']);

        $content = $this->postUpdate(['key' => $key, 'value' => 'Langue en tableau', 'locale' => ['es']], $server);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertFalse($content['success']);
        $this->assertEquals('Texte espagnol', $optionSystemService->getTranslatedValueByKey($key, 'es'));
    }

    /**
     * Désactiver la validation des pages remet les pages en attente en brouillon, sans les publier
     * @return void
     */
    public function testUpdateDisablePageValidation(): void
    {
        $pagePending = $this->createPage(
            customData: [
                'status' => PageStatus::DRAFT->value,
                'validationStatus' => PageValidationStatus::PENDING->value,
            ],
        );
        $pageRefused = $this->createPage(
            customData: [
                'status' => PageStatus::DRAFT->value,
                'validationStatus' => PageValidationStatus::REFUSED->value,
            ],
        );

        $this->client->loginUser($this->createUserSuperAdmin(), 'admin');
        $server = ['HTTP_X-CSRF-TOKEN' => $this->getCsrfToken()];
        $this->postUpdate(['key' => OptionSystem::OS_PAGE_VALIDATION->value, 'value' => '1'], $server);
        $this->em->clear();
        $repository = $this->em->getRepository(Page::class);
        $this->assertEquals(
            PageValidationStatus::PENDING->value,
            $repository->find($pagePending->getId())->getValidationStatus(),
        );

        $content = $this->postUpdate(['key' => OptionSystem::OS_PAGE_VALIDATION->value, 'value' => '0'], $server);
        $this->assertTrue($content['success']);
        $this->em->clear();
        $page = $repository->find($pagePending->getId());
        $this->assertEquals(PageValidationStatus::NONE->value, $page->getValidationStatus());
        $this->assertEquals(PageStatus::DRAFT->value, $page->getStatus());
        $this->assertEquals(
            PageValidationStatus::REFUSED->value,
            $repository->find($pageRefused->getId())->getValidationStatus(),
        );
    }

    /**
     * Envoie une requête de mise à jour et retourne la réponse JSON décodée
     * @param array $data
     * @param array $server
     * @return array
     */
    private function postUpdate(array $data, array $server): array
    {
        $this->client->request(
            'POST',
            $this->router->generate('admin_option-system_ajax_update'),
            server: $server,
            content: json_encode($data),
        );
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        return json_decode($response->getContent(), true);
    }

    /**
     * Retourne le jeton CSRF passé au composant Vue de la page des options
     * @return string
     */
    private function getCsrfToken(): string
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_option-system_change'));
        $props = $crawler
            ->filter('[data-symfony--ux-vue--vue-component-value="Admin/System/Option"]')
            ->attr('data-symfony--ux-vue--vue-props-value');

        return json_decode($props, true)['csrf_token'];
    }
}
