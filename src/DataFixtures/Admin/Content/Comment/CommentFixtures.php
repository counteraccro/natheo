<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Fixtures pour les commentaires
 */
namespace App\DataFixtures\Admin\Content\Comment;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Enum\Admin\Comment\CommentStatus;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class CommentFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{
    const COMMENT_FIXTURES_DATA_FILE =
        'content' . DIRECTORY_SEPARATOR . 'comment' . DIRECTORY_SEPARATOR . 'comment_fixtures_data.yaml';

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::COMMENT_FIXTURES_DATA_FILE);

        if ($data === null) {
            return;
        }

        $nbValidated = [];
        $dates = [];
        foreach ($data['comments'] as $ref => $commentData) {
            $comment = new Comment();
            foreach ($commentData as $key => $value) {
                switch ($key) {
                    case 'userModeration':
                        $comment->setUserModeration($this->getUserReference($value));
                        break;
                    case 'page':
                        $comment->setPage($this->getReference($value, Page::class));
                        break;
                    case 'date':
                        break;
                    case 'status':
                        $comment->setStatus($this->enumValue(CommentStatus::class, $value));
                        break;
                    default:
                        $this->setData($key, $value, $comment);
                }
            }
            if ($comment->getStatus() === CommentStatus::VALIDATE->value) {
                $nbValidated[$commentData['page']] = ($nbValidated[$commentData['page']] ?? 0) + 1;
            }
            $manager->persist($comment);
            $this->addReference($ref, $comment);
            if (isset($commentData['date'])) {
                $dates[] = [$comment, $commentData['date']];
            }
        }

        foreach ($nbValidated as $pageRef => $nb) {
            $this->getReference($pageRef, Page::class)->setNbComment($nb);
        }
        $manager->flush();
        $this->backdate($manager, $dates);
    }

    public static function getGroups(): array
    {
        return [self::GROUP_PAGE, self::GROUP_CONTENT, self::GROUP_COMMENT];
    }

    public function getOrder(): int
    {
        return 291;
    }
}
