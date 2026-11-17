<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des conventions des collections et écritures d'administration, sur la ressource de test AdminProbe
 */

namespace App\Tests\Api\V2\Admin;

use App\Api\V2\Concurrency\Etag;
use App\Tests\Api\V2\AppApiV2TestCase;
use App\Tests\Api\V2\Resource\Admin\AdminProbe;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminConventionsTest extends AppApiV2TestCase
{
    /**
     * Filtres génériques : recherche, statut, tri, nombre d'éléments par page ; valeur invalide : 422
     * @return void
     */
    public function testFilters(): void
    {
        $headers = $this->adminSessionHeaders(
            $this->createUserContributeur(['disabled' => false, 'anonymous' => false]),
        );

        $this->assertSame([1, 2, 3], $this->ids('/admin/probes', $headers));
        $this->assertMatchesResourceCollectionJsonSchema(AdminProbe::class);
        $this->assertSame([1], $this->ids('/admin/probes?search=alp', $headers));
        $this->assertSame([2], $this->ids('/admin/probes?status=disabled', $headers));
        $this->assertSame([1, 3], $this->ids('/admin/probes?status=enabled', $headers));
        $this->assertSame([3, 2, 1], $this->ids('/admin/probes?order[name]=desc', $headers));
        $this->assertSame([1, 3], $this->ids('/admin/probes?order[name]=asc&status=enabled', $headers));

        $content = $this->decode($this->requestV2('GET', '/admin/probes?itemsPerPage=1&page=2', headers: $headers));
        $this->assertSame([2], array_column($content['member'], 'id'));
        $this->assertSame(3, $content['totalItems']);
        $permissions = array_diff_key($content['member'][0]['permissions'], ['@type' => true]);
        $this->assertSame(['edit' => true, 'disable' => true, 'delete' => false], $permissions);

        $this->assertProblem($this->requestV2('GET', '/admin/probes?status=inconnu', headers: $headers), 422);
        $this->assertProblem($this->requestV2('GET', '/admin/probes?order[name]=haut', headers: $headers), 422);
    }

    /**
     * ETag sur la lecture ; If-Match périmé : 412 ; If-Match à jour, « * » ou absent : écriture acceptée
     * @return void
     */
    public function testConcurrency(): void
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        $headers = $this->adminSessionHeaders($user);
        $item = '/admin/probes/' . AdminProbe::EXISTING_ID;

        $response = $this->requestV2('GET', $item, headers: $headers);
        $etag = $response->headers->get('ETag');
        $this->assertNotNull($etag);
        $this->assertMatchesResourceItemJsonSchema(AdminProbe::class);

        $stale = ['HTTP_IF_MATCH' => '"perime"', ...$headers];
        $content = $this->assertProblem($this->requestV2('PATCH', $item, body: ['name' => 'x'], headers: $stale), 412);
        $translator = $this->container->get(TranslatorInterface::class);
        $this->assertSame($translator->trans('api_errors.title.412', domain: 'api_errors'), $content['title']);
        $this->assertSame(
            $translator->trans('api_errors.precondition.failed', domain: 'api_errors'),
            $content['detail'],
        );
        $this->assertProblem($this->requestV2('DELETE', $item, headers: $stale), 412);

        $response = $this->requestV2(
            'PATCH',
            $item,
            body: ['name' => 'x'],
            headers: ['HTTP_IF_MATCH' => $etag, ...$headers],
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEquals($etag, $response->headers->get('ETag'));

        foreach ([['HTTP_IF_MATCH' => '"a", *'], []] as $ifMatch) {
            $response = $this->requestV2('PATCH', $item, body: ['name' => 'x'], headers: [...$ifMatch, ...$headers]);
            $this->assertEquals(200, $response->getStatusCode());
        }
        $this->assertEquals(
            204,
            $this->requestV2('DELETE', $item, headers: ['HTTP_IF_MATCH' => $etag, ...$headers])->getStatusCode(),
        );
        $this->assertNull($this->client->getResponse()->headers->get('ETag'));
        $this->assertSame(Etag::of(new AdminProbe()), $etag);
    }

    /**
     * Identifiants des éléments d'une collection
     * @param string $path
     * @param array $headers
     * @return int[]
     */
    private function ids(string $path, array $headers): array
    {
        $response = $this->requestV2('GET', $path, headers: $headers);
        $this->assertEquals(200, $response->getStatusCode(), $path);
        return array_column($this->decode($response)['member'], 'id');
    }
}
