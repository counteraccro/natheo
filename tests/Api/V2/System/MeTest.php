<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la ressource Me de l'API v2
 */

namespace App\Tests\Api\V2\System;

use App\Api\V2\Resource\System\Me;
use App\Tests\Api\V2\AppApiV2TestCase;

class MeTest extends AppApiV2TestCase
{
    /**
     * Test GET /api/v2/me
     * @return void
     */
    public function testGet(): void
    {
        $content = $this->assertProblem($this->requestV2('GET', '/me'), 403);
        $this->assertEquals(
            $this->translator->trans('api_errors.session.required', domain: 'api_errors'),
            $content['detail'],
        );

        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false, 'avatar' => 'avatar.png']);
        $content = $this->decode($this->requestV2('GET', '/me', headers: $this->sessionHeaders($user)));
        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesResourceItemJsonSchema(Me::class);
        $this->assertEquals('Me', $content['@type']);
        $this->assertEquals($user->getId(), $content['id']);
        $this->assertEquals($user->getLogin(), $content['login']);
        $this->assertEquals($user->getEmail(), $content['email']);
        $this->assertEquals($user->getRoles(), $content['roles']);
        $this->assertEquals('/uploads/avatars/avatar.png', $content['avatar']);
        $this->assertTrue($content['canModerate']);

        $user = $this->createUser(['disabled' => false, 'anonymous' => false, 'avatar' => null]);
        $content = $this->decode($this->requestV2('GET', '/me', headers: $this->sessionHeaders($user)));
        $this->assertNull($content['avatar']);
        $this->assertFalse($content['canModerate']);
    }
}
