<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Fixtures pour les commentaires
 */
namespace App\DataFixtures\Admin\Content\Comment;

use App\DataFixtures\AppFixtures;
use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Yaml\Yaml;

class CommentFixtures extends AppFixtures implements FixtureGroupInterface, OrderedFixtureInterface
{

    const COMMENT_FIXTURES_DATA_FILE = 'content' . DIRECTORY_SEPARATOR . 'Comment' . DIRECTORY_SEPARATOR .
    'comment_fixtures_data.yaml';

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $data = Yaml::parseFile($this->pathDataFixtures . self::COMMENT_FIXTURES_DATA_FILE);

        if($data === null) {
            return;
        }

        foreach ($data['comments'] as $ref => $commentData) {
            $comment = new Comment();
            foreach ($commentData as $key => $value) {
                switch ($key) {
                    case "userModeration" :
                        $comment->setUserModeration($this->getReference($value, User::class));
                        break;
                    case "page":
                        $comment->setPage($this->getReference($value, Page::class));
                        break;
                    default:
                        $this->setData($key, $value, $comment);
                }
            }
            $manager->persist($comment);
            $this->addReference($ref, $comment);
        }
        $manager->flush();
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
