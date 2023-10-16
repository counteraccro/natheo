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
     * Format équivalent à l d F Y H:i:s
     */
    public const DATE_FORMAT_ALL = 'all';

    /**
     * Format équivalent à d/m/Y
     */
    public const DATE_FORMAT_DATE = 'date';

    /**
     * Format équivalent H:i:s
     */
    public const DATE_FORMAT_TIME = 'time';

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
                        <span class="tooltiptext-nat">' .
            $dateRef->format($this->getDateFormat(self::DATE_FORMAT_ALL)) . '</span>
                    </div>';
    }

    /**
     * Retourne un format de date en fonction de $format
     * @param string $format
     * @return string
     */
    private function getDateFormat(string $format = ''): string
    {
        return match ($format) {
            self::DATE_FORMAT_ALL => 'l d F Y H:i:s',
            self::DATE_FORMAT_TIME => 'H:i:s',
            self::DATE_FORMAT_DATE => 'd/m/Y',
            default => 'd/m/Y H:i:s',
        };
    }
}
