<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Trait principal Fixture
 */
namespace App\Tests\Helper\Fixtures;

use App\Tests\Helper\Fixtures\Content\CommentFixturesTrait;
use App\Tests\Helper\Fixtures\Content\FaqFixturesTrait;
use App\Tests\Helper\Fixtures\Content\Media\MediaFixturesTrait;
use App\Tests\Helper\Fixtures\Content\MenuFixturesTrait;
use App\Tests\Helper\Fixtures\Content\PageFixturesTrait;
use App\Tests\Helper\Fixtures\Content\TagFixturesTrait;
use App\Tests\Helper\Fixtures\System\ApiTokenFixturesTrait;
use App\Tests\Helper\Fixtures\System\MailFixturesTrait;
use App\Tests\Helper\Fixtures\System\NotificationFixturesTrait;
use App\Tests\Helper\Fixtures\System\OptionSystemFixturesTrait;
use App\Tests\Helper\Fixtures\System\SidebarElementFixturesTrait;
use App\Tests\Helper\Fixtures\System\TranslateFixturesTrait;
use App\Tests\Helper\Fixtures\System\User\OptionUserFixturesTrait;
use App\Tests\Helper\Fixtures\System\User\UserDataFixturesTrait;
use App\Tests\Helper\Fixtures\System\User\UserFixturesTrait;
use App\Tests\Helper\Fixtures\Tools\SqlManagerFixturesTrait;
use App\Tests\Helper\Fixtures\Content\Media\MediaFolderFixturesTrait;
use Doctrine\ORM\EntityManagerInterface;

trait FixturesTrait
{
    use UserFixturesTrait, OptionUserFixturesTrait, OptionSystemFixturesTrait, MailFixturesTrait, UserDataFixturesTrait,
        NotificationFixturesTrait, SidebarElementFixturesTrait, TagFixturesTrait, ApiTokenFixturesTrait, TranslateFixturesTrait,
        SqlManagerFixturesTrait, FaqFixturesTrait, MediaFolderFixturesTrait, MediaFixturesTrait, MenuFixturesTrait, PageFixturesTrait,
        CommentFixturesTrait;

    /**
     * @var EntityManagerInterface
     */
    protected readonly EntityManagerInterface $em;

    /**
     * Initialise une entité et set ses valeurs en fonction de $data
     * @param string $entityName
     * @param array $data
     * @param object|null $entityToUpdate
     * @return object
     */
    public function initEntity(string $entityName, array $data, ?object $entityToUpdate = null): object
    {
        $object = new $entityName();
        if ($entityToUpdate !== null) {
            $object = $entityToUpdate;
        }

        foreach ($data as $property => $value) {
            if (!property_exists($object, $property)) {
                echo "error " . $property . " not existe for " . $object::class . "\n";
                continue;
            }

            $setterAddName = 'add' . ucfirst(rtrim($property, 's'));
            $setterName = 'set' . ucfirst($property);

            // add
            if(method_exists($object, $setterAddName) && !method_exists($object, $setterName)) {
                $value = is_array($value) ? $value : [$value];
                foreach ($value as $item) {
                    $object->$setterAddName($item);
                }
                continue;
            }

            // set
            if (method_exists($object, $setterName)) {
                $object->$setterName($value);
            }
        }
        return $object;
    }

    /**
     * @param object $object
     * @return void
     */
    public function persistAndFlush(object $object): void
    {
        $this->em->persist($object);
        $this->em->flush();
    }
}