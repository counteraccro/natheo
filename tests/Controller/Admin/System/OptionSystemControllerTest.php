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
