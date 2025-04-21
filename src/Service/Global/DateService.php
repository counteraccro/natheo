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
     * @param bool $short
     * @return string
     */
    public function getStringDiffDate(
        ?DateTimeInterface $dateRef = null,
        ?DateTimeInterface $dateDiff = null,
        bool               $short = false): string
    {
        if ($dateRef === null) {
            return '<i>' . $this->translator->trans('date.diff.no_data') . '</i>';
        }

        if ($dateDiff === null) {
            $dateDiff = new DateTime('now');
        }
        $dateInterval = $dateRef->diff($dateDiff);

        $tabReturn = [];
        if ($dateInterval->y > 0) {
            $tabReturn['y'] = $this->getTranslateValue($dateInterval->y, 'date.diff.year', 'years');
        }

        if ($dateInterval->m > 0) {
            $tabReturn['m'] = $this->getTranslateValue($dateInterval->m, 'date.diff.month', 'month');
        }

        if ($dateInterval->d > 0) {
            $tabReturn['d'] = $this->getTranslateValue($dateInterval->d, 'date.diff.day', 'days');
        }

        if ($dateInterval->h > 0) {
            $tabReturn['h'] = $this->getTranslateValue($dateInterval->h, 'date.diff.hour', 'hours');
        }

        if ($dateInterval->i > 0) {
            $tabReturn['i'] = $this->getTranslateValue($dateInterval->i, 'date.diff.minute', 'minutes');

            if ($dateInterval->s > 0) {
                $tabReturn['id'] = ' ' . $this->translator->trans('date.diff.and');
            }
        }

        if ($dateInterval->s > 0) {
            $tabReturn['s'] = $this->getTranslateValue($dateInterval->s, 'date.diff.seconde', 'secondes');
        }


        $return = $this->constructString($tabReturn, $short);
        return $this->returnFormatString($return, $dateRef);
    }

    /**
     * Construit la chaine de retour
     * @param array $tab
     * @param bool $short
     * @return string
     */
    private function constructString(array $tab, bool $short): string
    {
        if ($short) {
            $return = $this->translator->trans('date.diff.start.short') . ' ';
        } else {
            $return = $this->translator->trans('date.diff.start') . ' ';
        }
        foreach ($tab as $value) {
            if ($short) {
                return $return .= $value;
            }
            $return .= $value;
        }

        if (empty($tab)) {
            $return .= ' ' . $this->translator->trans('date.diff.instant');
        }
        return $return;
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
