<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 *  Service option system
 */

namespace App\Service\Front;

use App\Utils\System\Options\OptionSystemKey;
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
        $result = $optionSystemService->getValueByKey(OptionSystemKey::OS_OPEN_SITE);
        if ($result === '1') {
            return true;
        }
        return false;
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
        $robotNoFollow = $optionSystemService->getValueByKey(OptionSystemKey::OS_FRONT_ROBOT_NO_FOLLOW);
        $robotNoIndex = $optionSystemService->getValueByKey(OptionSystemKey::OS_FRONT_ROBOT_NO_INDEX);

        if ($format) {
            $return = [];
            if (intval($robotNoFollow) === 1) {
                $return[] = ['name' => 'robots', 'value' => 'nofollow'];
            }
            if (intval($robotNoIndex) === 1) {
                $return[] = ['name' => 'robots', 'value' => 'noindex'];
            }
            return $return;
        }

        return [
            OptionSystemKey::OS_FRONT_ROBOT_NO_FOLLOW => $robotNoFollow,
            OptionSystemKey::OS_FRONT_ROBOT_NO_INDEX => $robotNoIndex,
        ];
    }
}
