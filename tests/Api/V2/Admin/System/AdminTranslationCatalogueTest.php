<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests des ressources AdminTranslationCatalogue : domaines servis et catalogue d'un domaine
 */

namespace App\Tests\Api\V2\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminTranslationCatalogue;
use App\Tests\Api\V2\AppApiV2TestCase;

class AdminTranslationCatalogueTest extends AppApiV2TestCase
{
    /**
     * Refus sans session, avec un token seul et avec une session ouverte par un token public
     * @return void
     */
    public function testAccess(): void
    {
        $user = $this->createUserAdmin(['disabled' => false, 'anonymous' => false]);
        $publicSession = $this->sessionHeaders($user, 'ROLE_ADMIN_API');

        foreach (['/admin/translations', '/admin/translations/admin_ui'] as $path) {
            $this->assertProblem($this->requestV2('GET', $path, self::HEADER_NONE), 401);
            $this->assertProblem($this->requestV2('GET', $path, self::HEADER_ADMIN), 403);
            $this->assertProblem($this->requestV2('GET', $path, headers: $this->backOfficeTokenHeaders()), 403);
            $this->assertProblem($this->requestV2('GET', $path, headers: $publicSession), 403);
        }
    }

    /**
     * Domaines du dossier translations, triés, sans les catalogues de Symfony, avec l'IRI de chacun
     * @return void
     */
    public function testCollection(): void
    {
        $headers = $this->adminSessionHeaders($this->createUser(['disabled' => false, 'anonymous' => false]));

        $response = $this->requestV2('GET', '/admin/translations', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceCollectionJsonSchema(AdminTranslationCatalogue::class);
        $members = $this->decode($response)['member'];
        $domains = array_column($members, 'domain');

        $this->assertContains('admin_ui', $domains);
        $this->assertContains('admin_module', $domains);
        $this->assertContains('api_errors', $domains);
        $this->assertNotContains('security', $domains);
        $this->assertNotContains('validators', $domains);
        $sorted = $domains;
        sort($sorted);
        $this->assertSame($sorted, $domains);
        $this->assertSame(
            self::PREFIX . '/admin/translations/admin_ui',
            $members[array_search('admin_ui', $domains)]['@id'],
        );
    }

    /**
     * Messages bruts du domaine dans la langue demandée, avec Content-Language
     * @return void
     */
    public function testItem(): void
    {
        $headers = $this->adminSessionHeaders($this->createUser(['disabled' => false, 'anonymous' => false]));

        $response = $this->requestV2('GET', '/admin/translations/admin_ui?locale=en', headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertMatchesResourceItemJsonSchema(AdminTranslationCatalogue::class);
        $this->assertSame('en', $response->headers->get('Content-Language'));
        $catalogue = $this->decode($response);
        $this->assertSame('admin_ui', $catalogue['domain']);
        $this->assertSame('en', $catalogue['locale']);
        $this->assertSame(
            $this->translator->trans('admin_ui.dialog.close', domain: 'admin_ui', locale: 'en'),
            $catalogue['messages']['admin_ui.dialog.close'],
        );
        // Message ICU renvoyé brut, sans formatage
        $this->assertStringContainsString('{', $catalogue['messages']['admin_ui.pagination.range']);

        $french = $this->decode($this->requestV2('GET', '/admin/translations/admin_ui?locale=fr', headers: $headers));
        $this->assertNotSame(
            $catalogue['messages']['admin_ui.dialog.close'],
            $french['messages']['admin_ui.dialog.close'],
        );
    }

    /**
     * Domaine inconnu ou catalogue de Symfony : 404
     * @return void
     */
    public function testItemNotFound(): void
    {
        $headers = $this->adminSessionHeaders($this->createUser(['disabled' => false, 'anonymous' => false]));

        foreach (['inconnu', 'security', 'validators'] as $domain) {
            $problem = $this->assertProblem(
                $this->requestV2('GET', '/admin/translations/' . $domain, headers: $headers),
                404,
            );
            $this->assertSame(
                $this->translator->trans('api_errors.find.translation_catalogue.not_found', domain: 'api_errors'),
                $problem['detail'],
            );
        }
    }

    /**
     * ETag, cache privé à revalider, 304 sans corps quand If-None-Match correspond, 200 pour une autre langue
     * @return void
     */
    public function testNotModified(): void
    {
        $headers = $this->adminSessionHeaders($this->createUser(['disabled' => false, 'anonymous' => false]));

        $response = $this->requestV2('GET', '/admin/translations/admin_ui?locale=fr', headers: $headers);
        $etag = (string) $response->headers->get('ETag');
        $this->assertNotSame('', $etag);
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-cache'));

        $response = $this->requestV2(
            'GET',
            '/admin/translations/admin_ui?locale=fr',
            headers: [...$headers, 'HTTP_IF_NONE_MATCH' => $etag],
        );
        $this->assertEquals(304, $response->getStatusCode());
        $this->assertSame('', (string) $response->getContent());

        $response = $this->requestV2(
            'GET',
            '/admin/translations/admin_ui?locale=en',
            headers: [...$headers, 'HTTP_IF_NONE_MATCH' => $etag],
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
