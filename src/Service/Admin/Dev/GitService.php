<?php

namespace App\Service\Admin\Dev;

use App\Service\Admin\AppAdminService;

class GitService extends AppAdminService
{

    /**
     * Clé pour avoir l'info de la branche
     * @var string
     */
    const KEY_BRANCHE = 'branche';

    /**
     * Clé pour avoir l'info du dernier hash
     * @var string
     */
    const KEY_HASH = 'hash';

    /**
     * Clé pour avoir l'info de la date du dernier commit
     * @var string
     */
    const KEY_LAST_COMMIT = 'date_last_commit';

    /**
     * Clé pour avoir l'info de la date du dernier commit version courte
     * @var string
     */
    const KEY_LAST_COMMIT_SHORT = 'date_last_commit_short';

    /**
     * Retourne les infos git locales sous la forme d'un tableau
     * @return array
     */
    public function getInfoGit(): array
    {
        $parameterBag = $this->getParameterBag();

        $root = $parameterBag->get('kernel.project_dir');
        $gitBasePath = $root . '/.git'; // e.g in laravel: base_path().'/.git';

        $gitStr = file_get_contents($gitBasePath . '/HEAD');
        $branche = rtrim(preg_replace("/(.*?\/){2}/", '', $gitStr));
        $gitPathBranch = $gitBasePath . '/refs/heads/' . $branche;

        return [
            self::KEY_BRANCHE => $branche,
            self::KEY_HASH => file_get_contents($gitPathBranch),
            self::KEY_LAST_COMMIT =>  date('d/m/Y H:i:s', filemtime($gitPathBranch)),
            self::KEY_LAST_COMMIT_SHORT =>  date('d/m/Y', filemtime($gitPathBranch))
        ];
    }
}
