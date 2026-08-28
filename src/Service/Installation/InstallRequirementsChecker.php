<?php
/**
 * Service pour gérer les prérequis pour l'installation
 * @author Gourdon Aymeric
 * @version 1.0
 */
declare(strict_types=1);

namespace App\Service\Installation;
use App\Service\Admin\AppAdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class InstallRequirementsChecker extends AppAdminService
{
    /**
     * Retourne les infos requises pour l'installation
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInfoRequired(): array
    {
        $return = [
            'php_current_version' => PHP_VERSION,
            'extensions' => $this->checkExtensionsRequirements(),
            'folders' => $this->checkWritableDirectories(),
        ];

        $return = array_merge($return, $this->checkPHPRequirements());

        $hasError = false;

        array_walk_recursive($return, function ($value) use (&$hasError) {
            if ($value === false) {
                $hasError = true;
            }
        });
        $return['next'] = !$hasError;

        return $return;
    }

    /**
     * Renvoi true si tous les requirements sont valide
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isAllRequirements(): bool
    {
        return $this->getInfoRequired()['next'];
    }

    /**
     * Vérification si la version de PHP est bonne ou non
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkPHPRequirements(): array
    {
        $kernelInterface = $this->getKernel();

        $composerJsonPath = $kernelInterface->getProjectDir() . '/composer.json';
        $requiredPhpConstraint = null;
        $requiredVersionNumber = null;

        if (file_exists($composerJsonPath)) {
            $composerData = json_decode(file_get_contents($composerJsonPath), true);
            $requiredPhpConstraint = $composerData['require']['php'] ?? null; // ex: "^8.1"

            if ($requiredPhpConstraint) {
                preg_match('/(\d+\.\d+(\.\d+)?)/', $requiredPhpConstraint, $matches);
                $requiredVersionNumber = $matches[1] ?? null;
            }
        }
        $isCompatible = $requiredVersionNumber ? version_compare(PHP_VERSION, $requiredVersionNumber, '>=') : false;

        return [
            'php_required_constraint' => $requiredPhpConstraint,
            'php_required_version' => $requiredVersionNumber,
            'is_compatible' => $isCompatible,
        ];
    }

    /**
     * Vérifie si les extensions primordiales sont installée
     * @return array
     */
    public function checkExtensionsRequirements(): array
    {
        $requiredExtensions = ['gd', 'intl', 'pdo_mysql'];
        $extensionsStatus = [];

        foreach ($requiredExtensions as $extension) {
            $extensionsStatus[$extension] = extension_loaded($extension);
        }

        return $extensionsStatus;
    }

    /**
     * Vérifie les droits d'écriture sur les dossiers nécessaires à Symfony.
     *
     * @return array<string, bool>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function checkWritableDirectories(): array
    {
        $kernelInterface = $this->getKernel();

        $filesystem = new Filesystem();
        $projectDir = $kernelInterface->getProjectDir();

        $directories = [
            'var/cache',
            'var/log',
            'var/sessions',
            'public/uploads',
            'public/assets/natheotheque',
            'public/assets/thumbnails',
        ];

        $results = [];

        foreach ($directories as $relativePath) {
            $fullPath = $projectDir . '/' . $relativePath;
            $results[$relativePath] = $this->isWritable($filesystem, $fullPath);
        }

        return $results;
    }

    private function isWritable(Filesystem $filesystem, string $path): bool
    {
        if (!$filesystem->exists($path)) {
            return false;
        }

        $testFile = $path . '/.write_test_' . uniqid();

        try {
            $filesystem->dumpFile($testFile, 'test');
            $filesystem->remove($testFile);

            return true;
        } catch (IOExceptionInterface) {
            return false;
        }
    }
}
