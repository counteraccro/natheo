<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service qui gère la manipulation de dates et son format
 */
namespace App\Service\Global;

use DateTime;
use DateTimeInterface;
use App\Service\AppService;

class DateService extends AppService
{

    /**
     * Affiche le temps entre 2 date sous la forme d'un texte de la forme
     * 'il y'a ...'
     * @param DateTimeInterface|null $dateRef
     * @param DateTimeInterface|null $dateDiff
     * @return string
     */
    public function getStringDiffDate(DateTimeInterface $dateRef = null, DateTimeInterface $dateDiff = null): string
    {
        if ($dateRef === null) {
            return '<i>' . $this->translator->trans('date.diff.no_data') . '</i>';
        }

        if ($dateDiff === null) {
            $dateDiff = new DateTime('now');
        }
        $dateInterval = $dateRef->diff($dateDiff);

        $return = $this->translator->trans('date.diff.start');

        if ($dateInterval->y > 0) {
            $return .= ' ' . $dateInterval->y . $this->translator->trans(
                    'date.diff.year',
                    ['years' => $dateInterval->y]
                );
        }

        if ($dateInterval->m > 0) {
            $return .= ' ' . $dateInterval->m . $this->translator->trans('date.diff.month');
        }

        if ($dateInterval->d > 0) {
            $return .= ' ' . $dateInterval->d . ' ' . $this->translator->trans(
                    'date.diff.day',
                    ['days' => $dateInterval->d]
                );
        }

        if ($dateInterval->h > 0) {
            $return .= ' ' . $dateInterval->h . ' ' . $this->translator->trans(
                    'date.diff.hour',
                    ['hours' => $dateInterval->h]
                );
        }

        if ($dateInterval->i > 0) {
            $return .= ' ' . $dateInterval->i . ' ' . $this->translator->trans(
                    'date.diff.minute',
                    ['minutes' => $dateInterval->i]
                );
            if ($dateInterval->s > 0) {
                $return .= ' ' . $this->translator->trans('date.diff.and');
            }
        }

        if ($dateInterval->s > 0) {
            $return .= ' ' . $dateInterval->s . ' ' . $this->translator->trans(
                    'date.diff.seconde',
                    ['secondes' => $dateInterval->s]
                );
        }

        if ($return === $this->translator->trans('date.diff.start')) {
            $return .= ' ' . $this->translator->trans('date.diff.instant');
        }

        return '<div class="tooltip-nat">' . $return . '
                        <span class="tooltiptext-nat">' . $dateRef->format('l d F Y H:i:s') . '</span>
                    </div>';
    }
}
