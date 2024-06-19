<?php
/**
 * Constante pour les données venant de AdvancedOptions
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Tools\AdvancedOptions;

class AdvancedOptionsConst
{
    /**
     * Path fichier env de l'application
     */
    public const FILE_ENV = '..' . DIRECTORY_SEPARATOR . '.env';

    /**
     * Path fichier env.local de l'application
     */
    public const FILE_ENV_LOCAL = '..' . DIRECTORY_SEPARATOR . '.env.local';

    /**
     * Dev mode
     */
    public const ENV_DEV = 'dev';

    /**
     * Prod mode
     */
    public const ENV_PROD = 'prod';
   }
