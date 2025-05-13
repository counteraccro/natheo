<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test CommentPopulate
 */

namespace App\Tests\Utils\Content\Comment;

use App\Entity\Admin\Content\Comment\Comment;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Comment\CommentPopulate;

class CommentPopulateTest extends AppWebTestCase
{
    /**
     * Test de la méthode populate()
     * @return void
     */
    public function testPopulate() :void {
        $comment = $this->createComment();
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
        $this->assertEquals($populate['author'], $commentP->getAuthor());
        $this->assertEquals($populate['email'], $commentP->getEmail());
        $this->assertEquals($populate['ip'], $commentP->getIp());
    }
}