<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test controller MarkdownController
 */

namespace App\Tests\Controller\Admin\Global;

use App\Enum\Admin\Module\CoreModuleId;
use App\Service\Admin\Module\ModuleActivationService;
use App\Tests\AppWebTestCase;

class MarkdownControllerTest extends AppWebTestCase
{
    /**
     * Test méthode loadDatas
     * @return void
     */
    public function testLoadDatas(): void
    {
        $this->checkNoAccess('admin_markdown_load-datas');

        $userContributeur = $this->createUserContributeur();
        $this->client->loginUser($userContributeur, 'admin');
        $this->client->request('GET', $this->router->generate('admin_markdown_load-datas'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content);
        $this->assertArrayHasKey('media', $content);
        $this->assertArrayHasKey('internalLinks', $content);
        $this->assertNotNull($content['media']);

        $activationService = $this->container->get(ModuleActivationService::class);
        $activationService->setModuleDisabled(CoreModuleId::MEDIA->value, true);
        $activationService->setModuleDisabled(CoreModuleId::PAGE->value, true);
        $this->client->request('GET', $this->router->generate('admin_markdown_load-datas'));
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNull($content['media']);
        $this->assertNull($content['internalLinks']);
    }
}
