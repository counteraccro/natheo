<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Teste InfoController
 */
namespace App\Tests\Controller\Admin\System;

use App\Tests\AppWebTestCase;

class InfoControllerTest extends AppWebTestCase
{
    /**
     * Test méthode index();
     * @return void
     */
    public function testIndex():void
    {
        $_SERVER['SERVER_SOFTWARE'] = self::getFaker()->text();
        $_SERVER['REQUEST_SCHEME'] = self::getFaker()->domainName();
        $_SERVER['HTTP_HOST'] = self::getFaker()->text();
        $_SERVER['HTTP_USER_AGENT'] = self::getFaker()->userAgent();

        $this->checkNoAccess('admin_info_index');

        $user = $this->createUserSuperAdmin();

        $this->client->loginUser($user, 'admin');
        $this->client->request('GET', $this->router->generate('admin_info_index'));
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('info.page.title.h1', domain: 'info'));
    }
}