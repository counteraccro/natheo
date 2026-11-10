<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Service gérant les grids de l'application
 */
namespace App\Service\Admin\Dev;

use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfoGit(): array
    {
        $parameterBag = $this->getParameterBag();

        return $this->getInfoGitFromPath($parameterBag->get('kernel.project_dir') . '/.git');
    }

    /**
     * Retourne les infos git du dossier .git donné, valeurs vides si elles sont introuvables
     * (pas de dépôt, HEAD détaché sans branche, référence uniquement dans packed-refs)
     * @param string $gitBasePath Chemin du dossier .git
     * @return array
     */
    public function getInfoGitFromPath(string $gitBasePath): array
    {
        $head = is_file($gitBasePath . '/HEAD') ? trim((string) file_get_contents($gitBasePath . '/HEAD')) : '';

        $branche = '';
        $hash = $head;
        $refPath = $gitBasePath . '/HEAD';
        if (str_starts_with($head, 'ref: ')) {
            $ref = substr($head, 5);
            $branche = preg_replace('#^refs/heads/#', '', $ref);
            $refPath = $gitBasePath . '/' . $ref;
            $hash = is_file($refPath) ? trim((string) file_get_contents($refPath)) : '';
        }

        $time = is_file($refPath) ? filemtime($refPath) : false;

        return [
            self::KEY_BRANCHE => $branche,
            self::KEY_HASH => $hash,
            self::KEY_LAST_COMMIT => $time === false ? '' : date('d/m/Y H:i:s', $time),
            self::KEY_LAST_COMMIT_SHORT => $time === false ? '' : date('d/m/Y', $time),
        ];
    }
}
