<?php

declare(strict_types=1);
/**
 * Test de la modération d'un commentaire
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Entity\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Comment\CommentStatus;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    /**
     * Un commentaire modéré garde son modérateur et la raison, sans balises
     * @return void
     */
    public function testModerateKeepsModeratorAndReason(): void
    {
        $moderator = new User();
        $comment = (new Comment())->moderate(CommentStatus::MODERATE, ' <b>Publicité</b> ', $moderator);

        $this->assertEquals(CommentStatus::MODERATE->value, $comment->getStatus());
        $this->assertEquals('Publicité', $comment->getModerationComment());
        $this->assertSame($moderator, $comment->getUserModeration());
    }

    /**
     * Une raison vide n'est pas enregistrée
     * @return void
     */
    public function testModerateWithoutReason(): void
    {
        $comment = (new Comment())->moderate(CommentStatus::MODERATE, '<p> </p>', new User());

        $this->assertNull($comment->getModerationComment());
        $this->assertNotNull($comment->getUserModeration());
    }

    /**
     * Valider ou remettre en attente efface le modérateur et la raison
     * @return void
     */
    public function testOtherStatusClearsModeration(): void
    {
        foreach ([CommentStatus::VALIDATE, CommentStatus::WAIT_VALIDATION] as $status) {
            $comment = (new Comment())->moderate(CommentStatus::MODERATE, 'Hors sujet', new User());
            $comment->moderate($status, 'Ignoré', new User());

            $this->assertEquals($status->value, $comment->getStatus());
            $this->assertNull($comment->getModerationComment());
            $this->assertNull($comment->getUserModeration());
        }
    }
}
