<?php

declare(strict_types=1);
/**
 * Constantes pour les médias
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Utils\Content\Media;

class MediaConst
{
    /**
     * Défini un média de type fichier
     * @var string
     */
    const MEDIA_TYPE_FILE = 'file';

    /**
     * Défini un média de type image
     * @var string
     */
    const MEDIA_TYPE_IMG = 'img';

    /**
     * Extensions autorisées à l'upload et type(s) MIME réel(s) accepté(s) pour chacune : le
     * couple extension déclarée / contenu réel est toujours vérifié, jamais indépendamment.
     * "application/zip" n'est toléré que pour docx/xlsx/pptx (formats OOXML = archives zip).
     * @var array<string, string[]>
     */
    const UPLOAD_ALLOWED_MIMES_BY_EXTENSION = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
        ],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => [
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
        ],
    ];

    /**
     * Taille maximale (en octets) d'un fichier uploadé, alignée sur la limite déjà
     * appliquée côté front (FileUpload.vue, prop max-size en Mo)
     * @var int
     */
    const MAX_UPLOAD_SIZE_BYTES = 20 * 1024 * 1024;
}
