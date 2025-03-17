<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test OptionSystemController
 */

namespace App\Tests\Controller\Admin\System;

use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\System\Options\OptionSystemKey;

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
        $this->assertSelectorTextContains('h1', $this->translator->trans('option_system.page_title_h1', domain: 'option_system'));
    }

    public function testUpdate() : void
    {
        $this->checkNoAccess('admin_option-system_ajax_update', methode: 'POST');
        $userSuperAdm = $this->createUserSuperAdmin();

        $this->client->loginUser($userSuperAdm, 'admin');

        $data = [
            'key' => OptionSystemKey::OS_THEME_SITE,
            'value' => 'theme-test-unit'
        ];
        $this->client->request('POST', $this->router->generate('admin_option-system_ajax_update'), content: json_encode($data));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $content = json_decode($response->getContent(), true);
        $this->assertTrue(isset($content['success']));

        /** @var OptionSystemService $optionSystemService */
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $result = $optionSystemService->getValueByKey(OptionSystemKey::OS_THEME_SITE);
        $this->assertEquals('theme-test-unit', $result);

    }
}


