<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Constantes globales des APIs
 */
namespace App\Utils\Api;

class ApiConst
{
    /**
     * Taille du token pour le user
     * @var int
     */
    public const API_SIZE_USER_TOKEN = 32;

    /**
     * Message de succès
     * @var string
     */
    public const API_MSG_SUCCESS = 'success';

    /**
     * Message d'erreur
     * @var string
     */
    public const API_MSG_ERROR = 'error';
}
