<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test DashboardService
 */
namespace App\Tests\Service\Admin;

use App\Entity\Admin\System\ApiToken;
use App\Service\Admin\DashboardService;
use App\Service\Admin\System\OptionSystemService;
use App\Tests\AppWebTestCase;
use App\Utils\System\ApiToken\ApiTokenConst;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DashboardServiceTest extends AppWebTestCase
{
    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    /**
     * @var OptionSystemService
     */
    private OptionSystemService $optionSystemService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = $this->container->get(DashboardService::class);
        $this->optionSystemService = $this->container->get(OptionSystemService::class);
    }

    /**
     * test méthode getBlockHelpConfig()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetBlockHelpConfig():void
    {
        $apiToken = $this->createApiToken(['token' => ApiTokenConst::API_TOKEN_READ]);

        $result = $this->dashboardService->getBlockHelpConfig();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('configComplete', $result);
        $this->assertFalse($result['configComplete']);
        $this->assertArrayHasKey('body', $result);
        $body = $result['body'];
        $this->assertIsArray($body);

        $this->checkValidFormatErrorReturnBlockHelpConfig(OptionSystemKey::OS_SITE_NAME, $body);
        $this->checkValidFormatErrorReturnBlockHelpConfig(OptionSystemKey::OS_ADRESSE_SITE, $body);
        $this->checkValidFormatErrorReturnBlockHelpConfig(OptionSystemKey::OS_OPEN_SITE, $body);
        $this->checkValidFormatErrorReturnBlockHelpConfig('API_TOKEN_STATUS', $body);

        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_SITE_NAME, 'unit-test');
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_ADRESSE_SITE, 'www.unit-test.com');
        $this->optionSystemService->saveValueByKee(OptionSystemKey::OS_OPEN_SITE, 1);

        $apiToken->setToken('token-unit-test');
        $this->dashboardService->save($apiToken);

        $result = $this->dashboardService->getBlockHelpConfig();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('configComplete', $result);
        $this->assertTrue($result['configComplete']);
        $this->assertArrayHasKey('body', $result);
        $body = $result['body'];
        $this->assertIsArray($body);

        $this->checkValidFormatSuccessReturnBlockHelpConfig(OptionSystemKey::OS_SITE_NAME, $body);
        $this->checkValidFormatSuccessReturnBlockHelpConfig(OptionSystemKey::OS_ADRESSE_SITE, $body);
        $this->checkValidFormatSuccessReturnBlockHelpConfig(OptionSystemKey::OS_OPEN_SITE, $body);
        $this->checkValidFormatSuccessReturnBlockHelpConfig('API_TOKEN_STATUS', $body);
    }

    /**
     * test méthode getBlockLastComment()
     * @return void
     */
    public function testGetBlockLastComment() :void
    {
        trigger_error( 'DashboardServiceTest::testGetBlockLastComment() pas correctement testé pour le moment - A faire : testé avec commentaires', E_USER_WARNING);

        $result = $this->dashboardService->getBlockLastComment();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('body', $result);
        $this->assertEmpty($result['body']);


        /*for ($i = 0; $i < 15; $i++)
        {

        }*/
    }

    /**
     * Vérifie si le format $array correspond au format lorsqu'il y à une erreur
     * @param string $key
     * @param array $array
     * @return void
     */
    private function checkValidFormatErrorReturnBlockHelpConfig(string $key, array $array):void
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertArrayHasKey('success', $array[$key]);
        $this->assertFalse($array[$key]['success']);
        $this->assertArrayHasKey('msg', $array[$key]);
        $this->assertNotEmpty($array[$key]['msg']);
    }

    /**
     * Vérifie si le format $array correspond au format lorsque tout est bon
     * @param string $key
     * @param array $array
     * @return void
     */
    private function checkValidFormatSuccessReturnBlockHelpConfig(string $key, array $array):void
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertArrayHasKey('success', $array[$key]);
        $this->assertTrue($array[$key]['success']);
        $this->assertArrayHasKey('msg', $array[$key]);
        $this->assertNotEmpty($array[$key]['msg']);
    }
}