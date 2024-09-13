<?php

namespace App\Service\Admin;

use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DashboardService extends AppAdminService
{

    /**
     * Retourne les informations du block Help config
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBlockHelpConfig(): array
    {
        $translator = $this->getTranslator();
        $optionSystem = $this->getOptionSystemService();
        $siteName = $optionSystem->getValueByKey(OptionSystemKey::OS_SITE_NAME);
        $adresseSite = $optionSystem->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $openSite = $optionSystem->getValueByKey(OptionSystemKey::OS_OPEN_SITE);

        $configComplete = true;
        $body = [
            OptionSystemKey::OS_SITE_NAME => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.site.name.success', domain: 'dashboard'),
            ],
            OptionSystemKey::OS_ADRESSE_SITE => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.site.adresse.success', domain: 'dashboard'),
            ],
            OptionSystemKey::OS_OPEN_SITE => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.site.open.success', domain: 'dashboard'),
            ],
        ];
        if ($siteName === OptionSystemKey::OS_SITE_NAME_DEFAULT_VALUE) {
            $body[OptionSystemKey::OS_SITE_NAME] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.site.name.warning', domain: 'dashboard')];
            $configComplete = false;
        }

        if ($adresseSite === OptionSystemKey::OS_ADRESSE_SITE_DEFAULT_VALUE) {
            $body[OptionSystemKey::OS_ADRESSE_SITE] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.site.adresse.warning', domain: 'dashboard')];
            $configComplete = false;
        }

        if ($openSite === OptionSystemKey::OS_OPEN_SITE_DEFAULT_VALUE) {
            $body[OptionSystemKey::OS_OPEN_SITE] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.site.open.warning', domain: 'dashboard')];
            $configComplete = false;
        }

        return ['success' => true, 'body' => $body, 'all', 'configComplete' => $configComplete];
    }
}
