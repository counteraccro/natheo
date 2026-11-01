<?php

declare(strict_types=1);
/**
 * Remplissage des entités Content limité à une liste blanche de champs
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content;

/**
 * Remplissage d'une entité depuis les données JSON reçues, limité à une liste blanche de champs
 */
trait PopulateTrait
{
    /**
     * Applique sur $object les valeurs de $populate dont la clé figure dans $allowed
     * Les autres clés (inconnues, identifiants, relations, propriétaire) sont ignorées
     * @param object $object entité à remplir
     * @param array $populate données reçues
     * @param string[] $allowed champs modifiables de l'entité
     * @return object
     */
    private function mergeData(object $object, array $populate, array $allowed): object
    {
        foreach ($allowed as $key) {
            if (!array_key_exists($key, $populate)) {
                continue;
            }
            $func = 'set' . ucfirst($key);
            $object->$func($populate[$key]);
        }
        return $object;
    }
}
