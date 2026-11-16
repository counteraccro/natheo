<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 *  Service option system
 */

namespace App\Service\Front;

use App\Enum\Admin\System\Options\OptionSystem;
use App\Service\Global\MarkdownService;
use League\CommonMark\Exception\CommonMarkException;
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

    /**
     * Scripts des options système à injecter dans la page, chaîne vide pour une option non renseignée
     * @return array{top: string, startBody: string, endBody: string}
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getFrontScripts(): array
    {
        $optionSystemService = $this->getOptionSystemService();
        // Un champ vidé en laissant un retour à la ligne ne compte pas comme un script
        return array_map(trim(...), [
            'top' => $optionSystemService->getValueByKey(OptionSystem::OS_FRONT_SCRIPT_TOP->value) ?? '',
            'startBody' => $optionSystemService->getValueByKey(OptionSystem::OS_FRONT_SCRIPT_START_BODY->value) ?? '',
            'endBody' => $optionSystemService->getValueByKey(OptionSystem::OS_FRONT_SCRIPT_END_BODY->value) ?? '',
        ]);
    }

    /**
     * Vrai si le bandeau de consentement est activé et qu'au moins un script est renseigné
     * @param array{top: string, startBody: string, endBody: string} $scripts
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isConsentRequired(array $scripts): bool
    {
        $enabled = $this->getOptionSystemService()->getValueByKey(OptionSystem::OS_FRONT_CONSENT_ENABLED->value);
        return $enabled === '1' && implode('', $scripts) !== '';
    }

    /**
     * Contenu du bandeau de consentement dans la langue de la page : texte converti en HTML, libellés des boutons
     * (chaîne vide quand le thème doit afficher son propre libellé) et date de la dernière demande de consentement
     * @param string $locale
     * @return array{text: string, accept: string, refuse: string, resetAt: string|null}
     * @throws CommonMarkException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getConsent(string $locale): array
    {
        $optionSystemService = $this->getOptionSystemService();
        $text =
            $optionSystemService->getTranslatedValueByKey(OptionSystem::OS_FRONT_CONSENT_TEXT->value, $locale) ?? '';

        return [
            'text' => $text === '' ? '' : (new MarkdownService())->convertMarkdownToHtml($text, true),
            'accept' =>
                $optionSystemService->getTranslatedValueByKey(
                    OptionSystem::OS_FRONT_CONSENT_ACCEPT_LABEL->value,
                    $locale,
                ) ?? '',
            'refuse' =>
                $optionSystemService->getTranslatedValueByKey(
                    OptionSystem::OS_FRONT_CONSENT_REFUSE_LABEL->value,
                    $locale,
                ) ?? '',
            'resetAt' => $optionSystemService->getValueByKey(OptionSystem::OS_FRONT_CONSENT_RESET_AT->value) ?: null,
        ];
    }
}
