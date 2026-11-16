<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource OptionSystem de l'API v2
 */

namespace App\Tests\Api\V2\System;

use App\Api\V2\Resource\System\OptionSystem;
use App\Enum\Admin\System\Options\OptionSystem as OptionSystemKey;
use App\Service\Admin\System\OptionSystemService;
use App\Service\Api\System\ApiOptionSystemService;
use App\Tests\Api\V2\AppApiV2TestCase;

class OptionSystemTest extends AppApiV2TestCase
{
    /**
     * Test GET /api/v2/option_systems
     * @return void
     */
    public function testGetCollection(): void
    {
        $this->assertProblem($this->requestV2('GET', '/option_systems', self::HEADER_NONE), 401);

        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_SITE_NAME->value, 'unit-test_site');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_OPEN_COMMENT->value, '1');
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_FRONT_ROBOT_NO_INDEX->value, '0');

        $content = $this->decode($this->requestV2('GET', '/option_systems'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceCollectionJsonSchema(OptionSystem::class);

        $whiteList = $this->container->get(ApiOptionSystemService::class)->getWhiteListeOptionSystem();
        $this->assertCount(count($whiteList), $content['member']);
        $this->assertArrayNotHasKey('view', $content);

        $values = array_column($content['member'], 'value', 'key');
        $this->assertSame('unit-test_site', $values['site_name']);
        $this->assertTrue($values['open_comment']);
        $this->assertFalse($values['front_robot_no_index']);
        $this->assertArrayNotHasKey('notification', $values);
        $this->assertEquals('/api/v2/option_systems/site_name', $content['member'][0]['@id']);
    }

    /**
     * Test GET /api/v2/option_systems/{key}
     * @return void
     */
    public function testGetItem(): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(OptionSystemKey::OS_SITE_NAME->value, 'unit-test_site');

        $content = $this->decode($this->requestV2('GET', '/option_systems/site_name'));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(OptionSystem::class);
        $this->assertEquals('OptionSystem', $content['@type']);
        $this->assertEquals('site_name', $content['key']);
        $this->assertEquals('unit-test_site', $content['value']);

        $detail = $this->translator->trans('api_errors.find.option.system.not.found', domain: 'api_errors');
        // Option non exposée, clé v1, clé en majuscules, clé inconnue
        foreach (['notification', 'OS_SITE_NAME', 'SITE_NAME', 'azerty'] as $key) {
            $content = $this->assertProblem($this->requestV2('GET', '/option_systems/' . $key), 404);
            $this->assertEquals($detail, $content['detail']);
        }
    }

    /**
     * Option traduisible : valeur dans la langue de `locale` puis d'`Accept-Language`, repli sur la langue par défaut
     * @return void
     */
    public function testTranslatableValue(): void
    {
        $this->container
            ->get(OptionSystemService::class)
            ->saveValueByKee(
                OptionSystemKey::OS_FRONT_FOOTER_TEXTE->value,
                json_encode(['fr' => 'Texte FR', 'en' => 'Text EN', 'es' => '']),
            );

        $cases = [
            ['?locale=en', [], 'Text EN'],
            ['', ['HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9'], 'Text EN'],
            ['?locale=fr', ['HTTP_ACCEPT_LANGUAGE' => 'en'], 'Texte FR'],
            ['?locale=es', [], 'Texte FR'],
            ['', [], 'Texte FR'],
        ];
        foreach ($cases as [$query, $headers, $expected]) {
            $content = $this->decode(
                $this->requestV2('GET', '/option_systems/front_footer_texte' . $query, headers: $headers),
            );
            $this->assertResponseStatusCodeSame(200);
            $this->assertSame($expected, $content['value'], $query . json_encode($headers));

            $content = $this->decode($this->requestV2('GET', '/option_systems' . $query, headers: $headers));
            $this->assertMatchesResourceCollectionJsonSchema(OptionSystem::class);
            $this->assertSame($expected, array_column($content['member'], 'value', 'key')['front_footer_texte']);
        }

        $this->assertProblem($this->requestV2('GET', '/option_systems?locale=de'), 422);
    }

    /**
     * Bandeau de consentement : activation, texte Markdown et libellés dans la langue de la requête
     * @return void
     */
    public function testConsentOptions(): void
    {
        $optionSystemService = $this->container->get(OptionSystemService::class);
        $optionSystemService->saveValueByKee(OptionSystemKey::OS_FRONT_CONSENT_ENABLED->value, '1');
        $optionSystemService->saveValueByKee(
            OptionSystemKey::OS_FRONT_CONSENT_TEXT->value,
            json_encode(['fr' => 'Texte *FR*', 'en' => 'Text *EN*']),
        );
        $optionSystemService->saveValueByKee(
            OptionSystemKey::OS_FRONT_CONSENT_ACCEPT_LABEL->value,
            json_encode(['fr' => 'Accepter', 'en' => 'Accept']),
        );
        $optionSystemService->saveValueByKee(
            OptionSystemKey::OS_FRONT_CONSENT_REFUSE_LABEL->value,
            json_encode(['fr' => 'Refuser', 'en' => 'Refuse']),
        );

        $content = $this->decode($this->requestV2('GET', '/option_systems?locale=en'));
        $this->assertMatchesResourceCollectionJsonSchema(OptionSystem::class);
        $values = array_column($content['member'], 'value', 'key');
        $this->assertTrue($values['front_consent_enabled']);
        $this->assertArrayHasKey('front_consent_reset_at', $values);
        $this->assertSame('Text *EN*', $values['front_consent_text']);
        $this->assertSame('Accept', $values['front_consent_accept_label']);
        $this->assertSame('Refuse', $values['front_consent_refuse_label']);

        $content = $this->decode($this->requestV2('GET', '/option_systems/front_consent_text?locale=fr'));
        $this->assertSame('Texte *FR*', $content['value']);
    }
}
