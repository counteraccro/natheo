<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Service option system
 */

namespace App\Service\Front;

use App\Enum\Admin\System\Options\OptionSystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class OptionSystemFrontService extends AppFrontService
{
    /**
     * Retourne true si le site est ouvert, false sinon
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isOpenSite(): bool
    {
        $optionSystemService = $this->getOptionSystemService();
        $result = $optionSystemService->getValueByKey(OptionSystem::OS_OPEN_SITE->value);
        if ($result === '1') {
            return true;
        }
        return false;
    }

    /**
     * Retourne le token API utilisé par le front, chaîne vide si non configuré
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrontApiToken(): string
    {
        return $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_FRONT_API_TOKEN->value) ?? '';
    }

    /**
     * Retourne les options lié aux robots
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMetaRobots($format = false): array
    {
        $optionSystemService = $this->getOptionSystemService();
        $robotNoFollow = $optionSystemService->getValueByKey(OptionSystem::OS_FRONT_ROBOT_NO_FOLLOW->value);
        $robotNoIndex = $optionSystemService->getValueByKey(OptionSystem::OS_FRONT_ROBOT_NO_INDEX->value);

        if ($format) {
            $values = [];
            if (intval($robotNoIndex) === 1) {
                $values[] = 'noindex';
            }
            if (intval($robotNoFollow) === 1) {
                $values[] = 'nofollow';
            }
            return empty($values) ? [] : [['name' => 'robots', 'value' => implode(', ', $values)]];
        }

        return [
            OptionSystem::OS_FRONT_ROBOT_NO_FOLLOW->value => $robotNoFollow,
            OptionSystem::OS_FRONT_ROBOT_NO_INDEX->value => $robotNoIndex,
        ];
    }
}
