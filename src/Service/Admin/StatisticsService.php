<?php

declare(strict_types=1);
/**
 *
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Service\Admin;

use App\Enum\Admin\Comment\CommentStatus;
use App\Enum\Admin\Content\Page\PageStatistics;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Repository\Admin\Content\Page\PageStatistiqueRepository;
use App\Repository\Admin\System\UserRepository;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class StatisticsService extends AppAdminService
{
    /**
     * 5 minutes
     */
    private const int CACHE_TTL = 300;

    public function __construct(
        #[AutowireLocator(AppAdminHandlerService::HANDLERS)] ContainerInterface $handlers,
        private readonly PageRepository $pageRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CacheInterface $cache,
        private readonly UserRepository $userRepository,
        private readonly PageStatistiqueRepository $pageStatistiqueRepository,
    ) {
        parent::__construct($handlers);
    }

    /**
     * Statistiques pour le dashboard
     * @return array
     * @throws InvalidArgumentException
     */
    public function getDashboardStatistics(): array
    {
        return $this->cache->get('dashboard_statistics', function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_TTL);

            $byStatus = array_column($this->commentRepository->getNbGroupByStatus(), 'nb', 'status');

            return [
                'nbPage' => $this->pageRepository->count(['status' => PageStatus::PUBLISH]),
                'nbComments' => $byStatus[CommentStatus::VALIDATE->value] ?? 0,
                'nbWaitComments' => $byStatus[CommentStatus::WAIT_VALIDATION->value] ?? 0,
                'nbUsers' => $this->userRepository->count(),
                'nbViews' => $this->formatCompactNumber(
                    $this->pageStatistiqueRepository->getTotalStatByKey(
                        PageStatistics::NB_READ->value,
                        $this->getRawQueryManager(),
                    ),
                ),
            ];
        });
    }

    /**
     * Format un nombre
     * @param int|float $number
     * @return string
     */
    public function formatCompactNumber(int|float $number): string
    {
        return match (true) {
            $number >= 1_000_000_000 => $this->roundToUnit($number, 1_000_000_000, 'B'),
            $number >= 1_000_000 => $this->roundToUnit($number, 1_000_000, 'M'),
            $number >= 1_000 => $this->roundToUnit($number, 1_000, 'K'),
            default => (string) $number,
        };
    }

    /**
     * Arrondie correctement un nombre
     * @param int|float $number
     * @param int $unit
     * @param string $suffix
     * @return string
     */
    private function roundToUnit(int|float $number, int $unit, string $suffix): string
    {
        $value = round($number / $unit, 1);
        $formatted = rtrim(rtrim((string) $value, '0'), '.');
        return $formatted . $suffix;
    }
}
