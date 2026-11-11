<?php

declare(strict_types=1);
/**
 * Test du LastUsedAtTrait
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Entity\Trait;

use App\Entity\Admin\System\ApiToken;
use PHPUnit\Framework\TestCase;

class LastUsedAtTraitTest extends TestCase
{
    /**
     * Sans date de dernière utilisation, la date est à mettre à jour
     * @return void
     */
    public function testOutdatedWithoutLastUsedAt(): void
    {
        $token = new ApiToken();
        $token->setLastUsedAt(null);

        $this->assertTrue($token->isLastUsedAtOutdated(new \DateTimeImmutable()));
    }

    /**
     * La date n'est mise à jour qu'à partir d'un intervalle complet
     * @return void
     */
    public function testOutdatedFromInterval(): void
    {
        $lastUsedAt = new \DateTimeImmutable('2026-01-01 12:00:00');
        $token = new ApiToken();
        $token->setLastUsedAt($lastUsedAt);
        $interval = ApiToken::LAST_USED_AT_INTERVAL;

        $this->assertFalse($token->isLastUsedAtOutdated($lastUsedAt));
        $this->assertFalse($token->isLastUsedAtOutdated($lastUsedAt->modify('+' . ($interval - 1) . ' seconds')));
        $this->assertTrue($token->isLastUsedAtOutdated($lastUsedAt->modify('+' . $interval . ' seconds')));
        $this->assertTrue($token->isLastUsedAtOutdated($lastUsedAt->modify('+1 hour')));
    }
}
