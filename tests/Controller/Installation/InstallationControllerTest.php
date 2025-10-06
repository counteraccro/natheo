<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *
 */

namespace App\Tests\Controller\Installation;

use App\Tests\AppWebTestCase;

class InstallationControllerTest extends AppWebTestCase
{
    /**
     * Test méthode stepOne()
     * @return void
     */
    public function testStepOne(): void
    {
        $this->client->request('GET', $this->router->generate('installation_step_1'));
        $this->assertResponseRedirects($this->router->generate('installation_step_2'));

        $this->createUser();
        $this->client->request('GET', $this->router->generate('installation_step_1'));
        $this->assertResponseRedirects($this->router->generate('auth_user_login'));
    }

    /**
     * Test méthode testConnexionDatabase()
     * @return void
     */
    public function testTestConnexionDatabase(): void
    {
        $this->client->request('GET', $this->router->generate('installation_check_database'));
        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('connexion', $content);
        $this->assertTrue($content['connexion']);
    }

    /**
     * Test méthode stepTwo()
     * @return void
     */
    public function testStepTwo(): void
    {
        $this->client->request('GET', $this->router->generate('installation_step_2'));
        $this->assertResponseIsSuccessful();

        $this->createUser();
        $this->client->request('GET', $this->router->generate('installation_step_2'));
        $this->assertResponseRedirects($this->router->generate('auth_user_login'));
    }
}
