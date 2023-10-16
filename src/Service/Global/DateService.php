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
    public function getStringDiffDate(
        DateTimeInterface $dateRef = null,
        DateTimeInterface $dateDiff = null): string
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
            $return .= $this->getTranslateValue($dateInterval->y, 'date.diff.year', 'years');
        }

        if ($dateInterval->m > 0) {
            $return .= $this->getTranslateValue($dateInterval->m, 'date.diff.month', 'month');
        }

        if ($dateInterval->d > 0) {
            $return .= $this->getTranslateValue($dateInterval->d, 'date.diff.day', 'days');
        }

        if ($dateInterval->h > 0) {
            $return .= $this->getTranslateValue($dateInterval->h, 'date.diff.hour', 'hours');
        }

        if ($dateInterval->i > 0) {
            $return .= $this->getTranslateValue($dateInterval->i, 'date.diff.minute', 'minutes');

            if ($dateInterval->s > 0) {
                $return .= ' ' . $this->translator->trans('date.diff.and');
            }
        }

        if ($dateInterval->s > 0) {
            $return .= $this->getTranslateValue($dateInterval->s, 'date.diff.seconde', 'secondes');
        }

        if ($return === $this->translator->trans('date.diff.start')) {
            $return .= ' ' . $this->translator->trans('date.diff.instant');
        }

        return $this->returnFormatString($return, $dateRef);
    }

    /**
     * Format le contenu de la chaine de caractères de retour
     * @param string $str
     * @param DateTimeInterface $date
     * @return string
     */
    private function returnFormatString(string $str, DateTimeInterface $date): string
    {
        return '<div class="tooltip-nat">' . $str . '
                        <span class="tooltiptext-nat">' .
            $date->format($this->getDateFormat(self::DATE_FORMAT_ALL)) . '</span>
                    </div>';
    }


    /**
     * Génère une traduction en fonction de la valeur, de la clé de traduction et de la clé
     * de la valeur pour la traduction
     * @param int $value
     * @param string $keyTranslate
     * @param string $keyValue
     * @return string
     */
    private function getTranslateValue(int $value, string $keyTranslate, string $keyValue = 'default'): string
    {
        return ' ' . $value . ' ' . $this->translator->trans(
                $keyTranslate,
                [$keyValue => $value]
            );
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
