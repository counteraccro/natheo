<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de l'enum CommentStatus
 */

namespace App\Tests\Enum\Admin\Comment;

use App\Enum\Admin\Comment\CommentStatus;
use App\Tests\AppWebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentStatusTest extends AppWebTestCase
{
    /**
     * Chaque statut a un libellé traduit dans le domaine comment
     * @return void
     */
    public function testGetLabel(): void
    {
        $translator = $this->container->get(TranslatorInterface::class);
        $this->assertSame('En attente', CommentStatus::WAIT_VALIDATION->getLabel()->trans($translator, 'fr'));
        $this->assertSame('Validé', CommentStatus::VALIDATE->getLabel()->trans($translator, 'fr'));
        $this->assertSame('Modéré', CommentStatus::MODERATE->getLabel()->trans($translator, 'fr'));
        foreach (CommentStatus::cases() as $status) {
            $this->assertSame('comment', $status->getLabel()->getDomain());
        }
    }
}
