<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Manipulation des dates
 */

namespace App\Twig\Runtime;

use App\Service\Global\DateService;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class DateExtensionRuntime extends AppExtensionRuntime implements RuntimeExtensionInterface
{

    private DateService $dateService;

    public function __construct(RouterInterface     $router,
                                TranslatorInterface $translator,
                                DateService         $dateService)
    {
        $this->dateService = $dateService;
        parent::__construct($router, $translator);
    }

    /**
     * Affiche le temps entre 2 date sous la forme de
     * @param DateTimeInterface|null $dateRef
     * @param DateTimeInterface|null $dateDiff
     * @return string
     */
    public function getDiffNow(DateTimeInterface $dateRef = null, DateTimeInterface $dateDiff = null): string
    {
       return $this->dateService->getStringDiffDate($dateRef, $dateDiff);
    }
}
