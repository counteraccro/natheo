<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Surcharge des opérations de l'API par config/cms/overwrite.yaml
 */

namespace App\Tests\Api\V2\Overwrite;

use App\Api\V2\Metadata\OverwriteMetadataFactory;
use App\Api\V2\Metadata\OverwriteValidator;
use App\DependencyInjection\Compiler\OverwriteConfigPass;
use App\Tests\Api\V2\AppApiV2TestCase;
use App\Tests\Api\V2\Resource\Admin\OverwriteProbe;
use App\Tests\Api\V2\State\Overwrite\OverwriteProbeProcessor;
use App\Tests\Api\V2\State\Overwrite\OverwriteProbeProvider;

class OverwriteTest extends AppApiV2TestCase
{
    /**
     * Seule l'opération surchargée change de provider, même quand le provider d'origine est partagé
     * @return void
     */
    public function testProvider(): void
    {
        $headers = $this->adminSessionHeaders($this->createUserContributeur());

        $content = $this->decode($this->requestV2('GET', '/admin/overwrite_probes/1', headers: $headers));
        $this->assertSame(OverwriteProbe::OVERWRITE, $content['source']);
        $this->assertMatchesResourceItemJsonSchema(OverwriteProbe::class);

        $content = $this->decode($this->requestV2('GET', '/admin/overwrite_probes', headers: $headers));
        $this->assertSame(OverwriteProbe::ORIGINAL, $content['member'][0]['source']);
    }

    /**
     * Seule l'opération surchargée change de processor
     * @return void
     */
    public function testProcessor(): void
    {
        $headers = $this->adminSessionHeaders($this->createUserAdmin());

        $response = $this->requestV2('PATCH', '/admin/overwrite_probes/1', body: ['name' => 'x'], headers: $headers);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame(OverwriteProbe::OVERWRITE, $this->decode($response)['source']);

        $response = $this->requestV2('POST', '/admin/overwrite_probes', body: ['name' => 'y'], headers: $headers);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertSame(OverwriteProbe::ORIGINAL, $this->decode($response)['source']);
    }

    /**
     * La sécurité de l'opération d'origine et celle de son module restent appliquées
     * @return void
     */
    public function testSecurityKept(): void
    {
        $contributor = $this->adminSessionHeaders($this->createUserContributeur());
        $publicSession = $this->sessionHeaders($this->createUserContributeur());
        $user = $this->adminSessionHeaders($this->createUser());

        $this->assertProblem(
            $this->requestV2('PATCH', '/admin/overwrite_probes/1', body: ['name' => 'x'], headers: $contributor),
            403,
        );
        $this->assertProblem($this->requestV2('GET', '/admin/overwrite_probes/1', headers: $publicSession), 403);
        $this->assertProblem($this->requestV2('GET', '/admin/overwrite_probes/1', headers: $user), 403);
    }

    /**
     * Les métadonnées gardent l'adresse et la sécurité, seule la classe change
     * @return void
     */
    public function testMetadata(): void
    {
        $factory = $this->container->get('api_platform.metadata.resource.metadata_collection_factory');
        $collection = $factory->create(OverwriteProbe::class);

        $get = $collection->getOperation('admin_overwrite_probe_get');
        $this->assertSame(OverwriteProbeProvider::class, $get->getProvider());
        $this->assertSame('/admin/overwrite_probes/{id}', $get->getUriTemplate());
        $this->assertStringContainsString("is_granted('ROLE_CONTRIBUTEUR')", (string) $get->getSecurity());

        $patch = $collection->getOperation('admin_overwrite_probe_patch');
        $this->assertSame(OverwriteProbeProcessor::class, $patch->getProcessor());
        $this->assertSame([OverwriteProbe::class, 'provide'], $patch->getProvider());
        $this->assertStringContainsString("is_granted('ROLE_ADMIN')", (string) $patch->getSecurity());

        $this->assertSame(
            [OverwriteProbe::class, 'provide'],
            $collection->getOperation('admin_overwrite_probe_get_collection')->getProvider(),
        );
    }

    /**
     * Fichiers de surcharge valides, opération inconnue refusée
     * @return void
     */
    public function testValidator(): void
    {
        $validator = $this->container->get(OverwriteValidator::class);
        $validator->validate();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('inconnue_get');
        $validator->validate([
            'admin_overwrite_probe_get' => [OverwriteConfigPass::PROVIDER => OverwriteProbeProvider::class],
            'inconnue_get' => [OverwriteConfigPass::PROVIDER => OverwriteProbeProvider::class],
        ]);
    }

    /**
     * Un processor donné à une opération de lecture est refusé à la construction des métadonnées
     * @return void
     */
    public function testProcessorOnReadOperation(): void
    {
        $factory = new OverwriteMetadataFactory(
            $this->container->get('api_platform.metadata.resource.metadata_collection_factory'),
            ['admin_overwrite_probe_get' => [OverwriteConfigPass::PROCESSOR => OverwriteProbeProcessor::class]],
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('admin_overwrite_probe_get');
        $factory->create(OverwriteProbe::class);
    }
}
