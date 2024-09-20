<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Constantes liés à API_TOKEN
 */
namespace App\Utils\System\ApiToken;

class ApiTokenConst
{
    /**
     * Nombre de segment du token
     * @var int
     */
    const API_TOKEN_SEGMENT = 4;

    /**
     * Taille d'un segment du token
     * @var int
     */
    const API_TOKEN_LENGTH = 24;

    /**
     * Token par défaut de lecture
     * @var string
     */
    const API_TOKEN_READ = 'read.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

    /**
     * Token par défaut d'écriture
     * @var string
     */
    const API_TOKEN_WRITE = 'write.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

    /**
     * Token par défaut admin
     * @var string
     */
    const API_TOKEN_ADMIN = 'admin.CZjfAZu6FatHfrCU8MaCudqc.GfmytciCqV8P236QSu3jJizG.EfgV96RRTSTxeqVBDHTxX2yh.9xicEZXkXzx7hL85eUZ8YrEJ';

    /**
     * Role token lecture
     * @var string
     */
    const API_TOKEN_ROLE_READ = 'ROLE_READ_API';

    /**
     * Role token écriture
     * @var string
     */
    const API_TOKEN_ROLE_WRITE = 'ROLE_WRITE_API';

    /**
     * Role token admin
     * @var string
     */
    const API_TOKEN_ROLE_ADMIN = 'ROLE_ADMIN_API';

    /**
     * Liste des roles
     * @var array
     */
    const API_TOKEN_ROLES = [
        self::API_TOKEN_ROLE_READ => self::API_TOKEN_ROLE_READ,
        self::API_TOKEN_ROLE_WRITE => self::API_TOKEN_ROLE_WRITE,
        self::API_TOKEN_ROLE_ADMIN => self::API_TOKEN_ROLE_ADMIN,
    ];
}
