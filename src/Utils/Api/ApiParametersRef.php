<?php
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Référence pour les paramètres attendus
 */
namespace App\Utils\Api;

class ApiParametersRef
{
    /**
     * Paramètres de référence pour l'authentification du user
     * @var array
     */
    const PARAMS_REF_AUTH_USER = [
        'username' => 'string',
        'password' => 'string'
    ];
}
