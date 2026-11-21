<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource AdminSqlQuery et du paramètre « sql » des collections d'administration
 */

namespace App\Tests\Api\V2\Admin\Tools;

use App\Api\V2\Resource\Admin\Tools\AdminSqlQuery;
use App\Entity\Admin\System\ActionLog;
use App\Entity\Admin\Tools\SqlManager;
use App\Tests\Api\V2\AppApiV2TestCase;

class AdminSqlQueryTest extends AppApiV2TestCase
{
    /**
     * Enregistrement d'une requête, nom par défaut, journal et lecture
     * @return void
     */
    public function testPost(): void
    {
        $user = $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]);
        $headers = $this->adminSessionHeaders($user);

        $response = $this->requestV2(
            'POST',
            '/admin/sql_queries',
            body: ['name' => 'Tags', 'query' => 'SELECT 1'],
            headers: $headers,
        );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminSqlQuery::class);
        $content = $this->decode($response);
        $this->assertSame(['Tags', 'SELECT 1', false], [$content['name'], $content['query'], $content['disabled']]);

        $sqlManager = $this->em->getRepository(SqlManager::class)->find($content['id']);
        $this->assertSame($user->getId(), $sqlManager->getUser()->getId());
        $log = $this->em->getRepository(ActionLog::class)->findOneBy([]);
        $this->assertSame(
            ['sql_manager', 'create', 'AdminSqlQuery', (string) $content['id'], 'Tags'],
            [$log->getModule(), $log->getAction(), $log->getResource(), $log->getResourceId(), $log->getLabel()],
        );

        $content = $this->decode(
            $this->requestV2('POST', '/admin/sql_queries', body: ['query' => 'SELECT 2'], headers: $headers),
        );
        $this->assertSame(
            $this->translator->trans('sql_manager.api.default_name', domain: 'sql_manager'),
            $content['name'],
        );

        $response = $this->requestV2('GET', '/admin/sql_queries/' . $content['id'], headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminSqlQuery::class);
        $this->assertProblem($this->requestV2('GET', '/admin/sql_queries/999999', headers: $headers), 404);
    }

    /**
     * Requête vide refusée en 422, opération réservée au super administrateur
     * @return void
     */
    public function testPostErrors(): void
    {
        $headers = $this->adminSessionHeaders($this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]));
        $content = $this->assertProblem(
            $this->requestV2('POST', '/admin/sql_queries', body: ['query' => ''], headers: $headers),
            422,
        );
        $this->assertSame('query', $content['violations'][0]['propertyPath']);

        $admin = $this->adminSessionHeaders($this->createUserAdmin(['disabled' => false, 'anonymous' => false]));
        $this->assertProblem(
            $this->requestV2('POST', '/admin/sql_queries', body: ['query' => 'SELECT 1'], headers: $admin),
            403,
        );
        $this->assertSame(0, $this->em->getRepository(SqlManager::class)->count([]));
    }

    /**
     * Le paramètre « sql » est réservé au super administrateur, une collection sans lui reste ouverte au module
     * @return void
     */
    public function testSqlParameterAccess(): void
    {
        $admin = $this->adminSessionHeaders($this->createUserAdmin(['disabled' => false, 'anonymous' => false]));
        $this->assertProblem($this->requestV2('GET', '/admin/probes?sql=true', headers: $admin), 403);
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes?sql=false', headers: $admin)->getStatusCode());
        $this->assertEquals(200, $this->requestV2('GET', '/admin/probes', headers: $admin)->getStatusCode());

        $superAdmin = $this->adminSessionHeaders(
            $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]),
        );
        $this->assertEquals(
            200,
            $this->requestV2('GET', '/admin/probes?sql=true', headers: $superAdmin)->getStatusCode(),
        );
        $this->assertProblem($this->requestV2('GET', '/admin/probes?sql=peut-etre', headers: $superAdmin), 422);
    }
}
