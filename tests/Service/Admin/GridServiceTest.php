<?php

namespace App\Tests\Service\Admin;

use App\Service\Admin\GridService;
use App\Service\Admin\System\User\UserService;
use App\Tests\AppWebTestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class GridServiceTest extends AppWebTestCase
{
    private GridService $gridService;

    public function setUp(): void
    {
        parent::setUp();
        $this->gridService = $this->container->get(GridService::class);
    }

    /**
     * Test méthode addAllDataRequiredGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddAllDataRequiredGrid(): void
    {
        $result = $this->gridService->addAllDataRequiredGrid([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey(GridService::KEY_URL_SAVE_RAW_SQL, $result);
        $this->assertArrayHasKey('listLimit', $result);
        $this->assertArrayHasKey('translate', $result);
    }

    /**
     * Test méthode addOptionsSelectLimit()
     * @return void
     */
    public function testAddOptionsSelectLimit(): void
    {
        $result = $this->gridService->addOptionsSelectLimit([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('listLimit', $result);
    }

    /**
     * Test méthode getFormatedSQLQuery()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testGetFormatedSQLQuery(): void
    {
        for($i = 0; $i < 10; $i++) {
            $this->createUser();
        }

        /** @var UserService $userService */
        $userService = $this->container->get(UserService::class);
        $paginate = $userService->getAllPaginate(1, 5);

        $result = $this->gridService->getFormatedSQLQuery($paginate);
        $this->assertIsString($result);
        $this->assertStringContainsString('SELECT', $result);
        $this->assertStringContainsString('FROM', $result);
    }

    /**
     * Test méthode addTranslateGrid()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testAddTranslateGrid(): void
    {
        $result = $this->gridService->addTranslateGrid([]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('translate', $result);
    }

    /**
     * Test méthode renderRole()
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testRenderRole(): void
    {
        $result = $this->gridService->renderRole('ROLE_USER');
        $this->assertIsString($result);
        $this->assertStringContainsString($this->translator->trans('global.role.user', domain: 'global'), $result);

        $result = $this->gridService->renderRole('ROLE_CONTRIBUTEUR');
        $this->assertIsString($result);
        $this->assertStringContainsString($this->translator->trans('global.role.contributeur', domain: 'global'), $result);

        $result = $this->gridService->renderRole('ROLE_ADMIN');
        $this->assertIsString($result);
        $this->assertStringContainsString($this->translator->trans('global.role.admin', domain: 'global'), $result);

        $result = $this->gridService->renderRole('ROLE_SUPER_ADMIN');
        $this->assertIsString($result);
        $this->assertStringContainsString($this->translator->trans('global.role.superadmin', domain: 'global'), $result);
    }
}