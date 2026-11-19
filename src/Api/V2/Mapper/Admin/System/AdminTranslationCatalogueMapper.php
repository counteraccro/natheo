<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Conversion d'un domaine de traduction et de ses messages en ressource AdminTranslationCatalogue
 */

namespace App\Api\V2\Mapper\Admin\System;

use App\Api\V2\Resource\Admin\System\AdminTranslationCatalogue;

final readonly class AdminTranslationCatalogueMapper
{
    /**
     * @param string $domain
     * @param string $locale
     * @param array<string, string> $messages messages bruts par clé
     * @return AdminTranslationCatalogue
     */
    public function map(string $domain, string $locale, array $messages): AdminTranslationCatalogue
    {
        $catalogue = $this->mapSummary($domain);
        $catalogue->locale = $locale;
        $catalogue->messages = new \ArrayObject($messages);
        return $catalogue;
    }

    /**
     * @param string $domain
     * @return AdminTranslationCatalogue
     */
    public function mapSummary(string $domain): AdminTranslationCatalogue
    {
        $catalogue = new AdminTranslationCatalogue();
        $catalogue->domain = $domain;
        return $catalogue;
    }
}
