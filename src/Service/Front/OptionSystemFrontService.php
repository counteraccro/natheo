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
        if($result === "1"){
            return true;
        }
        return false;
    }
}
