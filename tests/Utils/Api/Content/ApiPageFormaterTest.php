<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Test ApiPageFormater
 */

namespace Utils\Api\Content;

use App\Dto\Api\Content\Page\ApiFindPageDto;
use App\Tests\AppWebTestCase;
use App\Utils\Api\Content\ApiPageFormater;

class ApiPageFormaterTest extends AppWebTestCase
{
    /**
     * Test méthode convertPage()
     * @return void
     */
    public function testConvertPage(): void
    {
        $page = $this->createPageAllDataDefault();

        $slug = $page->getPageTranslationByLocale('fr')->getUrl();

        $dto = new ApiFindPageDto($slug, 'fr', false, false, true, [0], '');

        $apiPageFormater = new ApiPageFormater($page, $dto);
        $result = $apiPageFormater->convertPage()->getPageForApi();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('title', $result);
        $this->assertEquals($page->getPageTranslationByLocale('fr')->getTitre(), $result['title']);
        $this->assertArrayHasKey('render', $result);
        $this->assertEquals($page->getRender(), $result['render']);
        $this->assertArrayHasKey('author', $result);
        $this->assertIsArray($result['author']);
        $this->assertEquals($page->getUser()->getEmail(), $result['author']['author']);
        $this->assertEquals($page->getUser()->getAvatar(), $result['author']['avatar']);
        $this->assertEquals($page->getUser()->getDescription(), $result['author']['description']);
        $this->assertArrayHasKey('statistiques', $result);
        $this->assertArrayHasKey('contents', $result);
        $this->assertCount($page->getPageContents()->count(), $result['contents']);
    }
}
