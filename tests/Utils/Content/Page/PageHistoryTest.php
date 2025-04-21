<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test pageHistory
 */

namespace App\Tests\Utils\Content\Page;

use App\Entity\Admin\System\User;
use App\Tests\AppWebTestCase;
use App\Utils\Content\Page\PageHistory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class PageHistoryTest extends AppWebTestCase
{

    /**
     * @var PageHistory
     */
    private PageHistory $pageHistory;

    /**
     * @var User
     */
    private User $currentUser;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        parent::setUp();
        /** @var ContainerBagInterface $containerBag */
        $containerBag = $this->container->get(ContainerBagInterface::class);
        $this->currentUser = $this->createUser();
        $this->pageHistory = new PageHistory($containerBag, $this->currentUser);
        $this->filesystem = new Filesystem();
    }

    /**
     * Test méthode save()
     * @return void
     */
    public function testSave(): void
    {
        $this->pageHistory->save($this->getDataTest());
        $exist = $this->filesystem->exists($this->pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-user-' . $this->currentUser->getId() . '.txt');
        $this->assertTrue($exist);
        $this->pageHistory->removePageHistory();

        $id = 1;
        $this->pageHistory->save($this->getDataTest($id));
        $exist = $this->filesystem->exists($this->pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-' . $id . '.txt');
        $this->assertTrue($exist);
        $this->pageHistory->removePageHistory($id);
    }

    /**
     * Test méthode removePageHistory()
     * @return void
     */
    public function testRemovePageHistory(): void
    {
        $this->pageHistory->save($this->getDataTest());
        $this->pageHistory->removePageHistory();
        $exist = $this->filesystem->exists($this->pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-user-' . $this->currentUser->getId() . '.txt');
        $this->assertFalse($exist);

        $id = 1;
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->removePageHistory($id);
        $exist = $this->filesystem->exists($this->pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-' . $id . '.txt');
        $this->assertFalse($exist);
    }

    /**
     * Test méthode getHistory()
     * @return void
     */
    public function testGetHistory(): void
    {
        $id = 1;
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->save($this->getDataTest($id));

        $result = $this->pageHistory->getHistory($id);
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $row = $result[0];
        $this->assertArrayHasKey('time', $row);
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('user', $row);

        $this->pageHistory->removePageHistory($id);
    }

    /**
     * Test méthode
     * @return void getPageHistoryById()
     */
    public function testGetPageHistoryById(): void
    {
        $id = 1;
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->save($this->getDataTest($id));

        $result = $this->pageHistory->getPageHistoryById(2, $id);
        $this->assertEquals($this->getDataTest($id), $result);

        $result = $this->pageHistory->getPageHistoryById(12, $id);
        $this->assertEmpty($result);
        $this->pageHistory->removePageHistory($id);


        $id = null;
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->save($this->getDataTest($id));
        $this->pageHistory->save($this->getDataTest($id));
        $result = $this->pageHistory->getPageHistoryById(1, $id);
        $this->assertEquals($this->getDataTest($id), $result);
        $this->pageHistory->removePageHistory($id);
    }

    /**
     * Test méthode renamePageHistorySave()
     * @return void
     */
    public function testRenamePageHistorySave(): void
    {
        $this->pageHistory->save($this->getDataTest());
        $path = $this->pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-user-' . $this->currentUser->getId() . '.txt';
        $exist = $this->filesystem->exists($path);
        $this->assertTrue($exist);

        $idPage = 99;
        $this->pageHistory->renamePageHistorySave($idPage);
        $exist = $this->filesystem->exists($path);
        $this->assertFalse($exist);

        $exist = $this->filesystem->exists($this->pageHistory->getPathPageHistory() . DIRECTORY_SEPARATOR . 'page-' . $idPage . '.txt');
        $this->assertTrue($exist);
        $this->pageHistory->removePageHistory($idPage);
    }

    /**
     * Retourne un jeu de données de test
     * @param $idPage
     * @return array
     */
    private function getDataTest($idPage = null): array
    {

        return [
            "id" => $idPage,
            "render" => 1,
            "status" => 1,
            "pageTranslations" => [
                [
                    "id" => 1,
                    "page" => 1,
                    "locale" => "fr",
                    "titre" => "Dernières pages",
                    "url" => "pages"
                ],
                [
                    "id" => 2,
                    "page" => 1,
                    "locale" => "es",
                    "titre" => "Últimas páginas",
                    "url" => "paginas"
                ],
                [
                    "id" => 3,
                    "page" => 1,
                    "locale" => "en",
                    "titre" => "Last pages",
                    "url" => "page"
                ]
            ],
            "pageContents" => [
                [
                    "id" => 23,
                    "page" => 1,
                    "renderOrder" => 1,
                    "type" => 1,
                    "pageContentTranslations" => [
                        [
                            "id" => 37,
                            "pageContent" => 23,
                            "locale" => "fr",
                            "text" => "[fr] Contenu de votre page"
                        ],
                        [
                            "id" => 38,
                            "pageContent" => 23,
                            "locale" => "en",
                            "text" => "[en] Contenu de votre page"
                        ],
                        [
                            "id" => 39,
                            "pageContent" => 23,
                            "locale" => "es",
                            "text" => "[es] Contenu de votre page"
                        ]
                    ],
                    "typeId" => null,
                    "renderBlock" => 1
                ]
            ],
            "pageStatistiques" => [
                [
                    "id" => 1,
                    "page" => 1,
                    "key" => "PAGE_NB_VISITEUR",
                    "value" => "100"
                ],
                [
                    "id" => 2,
                    "page" => 1,
                    "key" => "PAGE_NB_READ",
                    "value" => "32"
                ]
            ],
            "disabled" => false,
            "category" => 1,
            "landingPage" => false,
            "openComment" => true,
            "nbComment" => 0,
            "ruleComment" => 2,
            "menus" => [
                4
            ],
            "tags" => [
                [
                    "id" => 1,
                    "color" => "#6F42C1",
                    "disabled" => false,
                    "tagTranslations" => [
                        [
                            "id" => 1,
                            "tag" => 1,
                            "locale" => "fr",
                            "label" => "Natheo"
                        ],
                        [
                            "id" => 2,
                            "tag" => 1,
                            "locale" => "es",
                            "label" => "Natheo"
                        ],
                        [
                            "id" => 3,
                            "tag" => 1,
                            "locale" => "en",
                            "label" => "Natheo"
                        ]
                    ]
                ],
                [
                    "id" => 8,
                    "color" => "#23e515",
                    "disabled" => false,
                    "tagTranslations" => [
                        [
                            "id" => 22,
                            "tag" => 8,
                            "locale" => "fr",
                            "label" => "Page"
                        ],
                        [
                            "id" => 23,
                            "tag" => 8,
                            "locale" => "es",
                            "label" => "Page (ES)"
                        ],
                        [
                            "id" => 24,
                            "tag" => 8,
                            "locale" => "en",
                            "label" => "Page (EN)"
                        ]
                    ]
                ],
                [
                    "id" => 10,
                    "color" => "#00478c",
                    "disabled" => false,
                    "tagTranslations" => [
                        [
                            "id" => 28,
                            "tag" => 10,
                            "locale" => "fr",
                            "label" => "toto"
                        ],
                        [
                            "id" => 29,
                            "tag" => 10,
                            "locale" => "en",
                            "label" => "toto (en)"
                        ],
                        [
                            "id" => 30,
                            "tag" => 10,
                            "locale" => "es",
                            "label" => "toto (es)"
                        ]
                    ]
                ]
            ]
        ];
    }
}