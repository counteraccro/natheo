<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Tests\Service\Admin;

use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Service\Admin\StatisticsService;
use App\Tests\AppWebTestCase;

class StatisticsServiceTest extends AppWebTestCase
{
    private StatisticsService $statisticsService;

    public function setUp(): void
    {
        parent::setUp();
        $this->statisticsService = $this->container->get(StatisticsService::class);
    }

    /**
     * @return void
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function testgetDashboardStatistics(): void
    {
        $this->clearCacheByKey('dashboard_statistics');

        $nbView = $this->getFaker()->numberBetween(20, 40);
        $nbView2 = $this->getFaker()->numberBetween(20, 40);
        $total = $nbView + $nbView2;

        $user = $this->createUserContributeur();
        $page = $this->createPage($user, ['status' => PageStatus::PUBLISH->value]);
        $this->createPageStatistique($page, ['key' => PageStatistics::NB_READ->value, 'value' => strval($nbView)]);

        $page2 = $this->createPage($user, ['status' => PageStatus::PUBLISH->value]);
        $this->createPageStatistique($page2, [
            'key' => PageStatistics::NB_READ->value,
            'value' => strval($nbView2),
        ]);

        $page3 = $this->createPage($user, ['status' => PageStatus::ARCHIVED->value]);
        $this->createPageStatistique($page3, [
            'key' => PageStatistics::NB_READ->value,
            'value' => strval($this->getFaker()->numberBetween(20, 40)),
        ]);

        $nbComments = $this->getFaker()->numberBetween(2, 5);
        for ($i = 0; $i < $nbComments; $i++) {
            $this->createComment($page, null, ['status' => CommentStatus::VALIDATE->value]);
        }

        $this->createComment($page, null, ['status' => CommentStatus::WAIT_VALIDATION->value]);

        $content = $this->statisticsService->getDashboardStatistics();
        $this->assertArrayHasKey('nbPage', $content);
        $this->assertArrayHasKey('nbComments', $content);
        $this->assertArrayHasKey('nbWaitComments', $content);
        $this->assertArrayHasKey('nbUsers', $content);
        $this->assertArrayHasKey('nbViews', $content);

        $this->assertEquals(2, $content['nbPage']);
        $this->assertEquals($nbComments, $content['nbComments']);
        $this->assertEquals(1, $content['nbWaitComments']);
        $this->assertEquals(1, $content['nbUsers']);
        $this->assertEquals($total, $content['nbViews']);
    }
}
