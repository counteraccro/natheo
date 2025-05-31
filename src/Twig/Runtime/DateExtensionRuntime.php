<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Manipulation des dates
 */

namespace App\Twig\Runtime;

use App\Service\Global\DateService;
use DateTimeInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Attribute\AsTwigFunction;

class DateExtensionRuntime extends AppExtensionRuntime
{

    private DateService $dateService;

    /**
     * @param ContainerInterface $handlers
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(#[AutowireLocator([
        'translator' => TranslatorInterface::class,
        'router' => RouterInterface::class,
        'dateService' => DateService::class
    ])] private readonly ContainerInterface $handlers)
    {
        $this->dateService = $this->handlers->get('dateService');
        parent::__construct($this->handlers);
    }

    /**
     * Affiche le temps entre 2 date sous la forme de
     * @param DateTimeInterface|null $dateRef
     * @param DateTimeInterface|null $dateDiff
     * @return string
     */
    #[AsTwigFunction('diff_now', isSafe: ['html'])]
    public function getDiffNow(?DateTimeInterface $dateRef = null, ?DateTimeInterface $dateDiff = null): string
    {
        return $this->dateService->getStringDiffDate($dateRef, $dateDiff);
    }
}
