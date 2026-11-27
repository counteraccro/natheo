<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Catalogues de traduction de l'interface servis par l'API : domaines du dossier translations et leurs messages bruts
 */

namespace App\Service\Api\System;

use App\Service\Api\AppApiService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

class TranslationCatalogueService extends AppApiService
{
    /**
     * Catalogues de Symfony indexés par le message anglais, sans intérêt pour l'interface
     * @var string[]
     */
    public const array EXCLUDED_DOMAINS = ['security', 'validators'];

    /**
     * Nom d'un fichier de traduction : domaine, suffixe ICU facultatif, langue et format
     * @var string
     */
    private const string FILE_PATTERN = '/^([a-z0-9_]+)(?:\+intl-icu)?\.[a-z]{2}\.(?:ya?ml|xlf|xliff|php|json)$/';

    /**
     * Domaines servis, triés par nom
     * @return string[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDomains(): array
    {
        $files = (new Finder())
            ->files()
            ->in($this->getParameterBag()->get('translator.default_path'))
            ->depth(0);

        $domains = [];
        foreach ($files as $file) {
            if (preg_match(self::FILE_PATTERN, $file->getFilename(), $matches) === 1) {
                $domains[$matches[1]] = true;
            }
        }
        $domains = array_diff(array_keys($domains), self::EXCLUDED_DOMAINS);
        sort($domains);
        return $domains;
    }

    /**
     * Indique si le domaine est servi
     * @param string $domain
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function hasDomain(string $domain): bool
    {
        return in_array($domain, $this->getDomains(), true);
    }

    /**
     * Messages bruts (ICU non formaté) du domaine dans la langue, modifications du module Traductions comprises
     * @param string $domain
     * @param string $locale
     * @return array<string, string>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMessages(string $domain, string $locale): array
    {
        $translator = $this->getTranslator();
        if (!($translator instanceof TranslatorBagInterface)) {
            return [];
        }

        $catalogue = $translator->getCatalogue($locale);
        $messages = $catalogue->all($domain . MessageCatalogueInterface::INTL_DOMAIN_SUFFIX) + $catalogue->all($domain);
        ksort($messages);
        return $messages;
    }
}
