<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test CommentPopulate
 */

namespace App\Tests\Populate\Admin\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Tests\AppWebTestCase;
use App\Populate\Admin\Content\Comment\CommentPopulate;

class CommentPopulateTest extends AppWebTestCase
{
    /**
     * Test de la méthode populate()
     * @return void
     */
    public function testPopulate(): void
    {
        $comment = $this->createComment();
        $author = $comment->getAuthor();
        $email = $comment->getEmail();
        $ip = $comment->getIp();
        $populate = [
            'comment' => 'Commentaire test',
            'author' => 'Auteur',
            'email' => 'auteur@test.com',
            'ip' => '127.0.0.1',
        ];

        $commentPopulate = new CommentPopulate($comment, $populate);
        $commentP = $commentPopulate->populate()->getComment();
        $this->assertInstanceOf(Comment::class, $commentP);
        $this->assertEquals($populate['comment'], $commentP->getComment());
        // Champs saisis par le visiteur : hors liste blanche, non modifiables en admin
        $this->assertEquals($author, $commentP->getAuthor());
        $this->assertEquals($email, $commentP->getEmail());
        $this->assertEquals($ip, $commentP->getIp());
    }

    /**
     * Test de la méthode populate() : texte du commentaire brut, commentaire de modération sans HTML
     * @return void
     */
    public function testPopulateRawCommentAndStripModeration(): void
    {
        $comment = $this->createComment();
        $populate = [
            'comment' => 'x<y et `<div>`',
            'moderationComment' => '<b>Hors</b> sujet<script>alert(1)</script>',
        ];

        $commentP = (new CommentPopulate($comment, $populate))->populate()->getComment();
        $this->assertEquals('x<y et `<div>`', $commentP->getComment());
        $this->assertEquals('Hors sujetalert(1)', $commentP->getModerationComment());
    }
}
