<?php
/**
 * Gestion de l'historique des modifications de pages
 * sauvegarde, restauration, listing, clean
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Page;

use App\Entity\Admin\System\User;
use App\Utils\System\Options\OptionUserKey;
use App\Utils\System\User\PersonalData;
use App\Utils\Utils;
use PhpCsFixer\Finder;
use phpDocumentor\Reflection\Types\Void_;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PageHistory
{
    /**
     * Path pour les historiques de page globale
     * @var string
     */
    private string $pathPageHistory;

    /**
     * @var User
     */
    private User $user;

    private string $directoryHistory = 'pageHistory';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    private string $fileName = 'page-';

    private string $fileExt = '.txt';

    /**
     * Constructeur
     * @param ContainerBagInterface $containerBag
     * @param User $user
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerBagInterface $containerBag, User $user)
    {
        $this->user = $user;
        $this->filesystem = new Filesystem();

        $kernel = $containerBag->get('kernel.project_dir');
        $env = $containerBag->get('kernel.environment');

        if ($env === 'test') {
            $this->directoryHistory = $this->directoryHistory . '-test';
        }

        $this->pathPageHistory = $kernel . DIRECTORY_SEPARATOR . 'var'
            . DIRECTORY_SEPARATOR . $this->directoryHistory;

        $this->isExistDirectory($this->pathPageHistory);
    }

    /**
     * Test si le dossier existe, s'il n'existe pas il est créé
     * @param string $path
     * @return void
     */
    private function isExistDirectory(string $path): void
    {
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path);
        }
    }

    /**
     * Sauvegarde une instance de page
     * @param array $page
     * @return void
     */
    public function save(array $page): void
    {
        $path = $this->getPath($page['id']);
        $this->filesystem->appendToFile($path, $this->serialize($page) . "\n");
    }

    /**
     * Retourne le chemin complet de l'historique d'une page en fonction de son id
     * @param int|null $id
     * @return string
     */
    private function getPath(int $id = null): string
    {
        if ($id === null) {
            $id = 'user-' . $this->user->getId();
        }
        return $this->pathPageHistory . DIRECTORY_SEPARATOR . $this->fileName . $id . $this->fileExt;
    }

    /**
     * Sérialise les données pour l'enregistrement
     * @param array $page
     * @return string
     */
    private function serialize(array $page): string
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $personalData = new PersonalData($this->user,
            $this->user->getOptionUserByKey(OptionUserKey::OU_DEFAULT_PERSONAL_DATA_RENDER)->getValue());

        $data = ['time' => time(), 'user' => $personalData->getPersonalData(), 'pageH' => $page];
        return $serializer->serialize($data, 'json');
    }

    /**
     * Retourne le contenu d'un fichier en fonction de son id
     * Si le fichier n'existe pas, retourne un tableau vide
     * @param int $id
     * @return array
     */
    private function getContentFile(int $id = null): array
    {
        $path = $this->getPath($id);
        if (file_exists($path)) {
            return file($path);
        }
        return [];
    }

    /**
     * Retourne l'ensemble de l'historique d'une page en fonction de son id
     * @param int|null $id
     * @return array
     */
    public function getHistory(int $id = null): array
    {
        $datas = $this->getContentFile($id);
        $return = [];
        foreach ($datas as $key => $row) {
            $array = json_decode($row, true);
            $return[] = ['time' => $array['time'], 'id' => $key, 'user' => $array['user']];
        }
        return array_reverse($return);
    }

    /**
     * Retourne l'historique d'une page en fonction de son id et de la key id (ligne dans le fichier)
     * @param int $rowId
     * @param int|null $pageId
     * @return array
     */
    public function getPageHistoryById(int $rowId, int $pageId = null): array
    {
        $datas = $this->getContentFile($pageId);
        foreach ($datas as $key => $row) {
            if ($key === $rowId) {
                $array = json_decode($row, true);
                return $array['pageH'];
            }
        }
        return [];
    }

    /**
     * Convertie une pageHistory brouillon en pageHistory associé à une page
     * @param $pageId
     * @return void
     */
    public function renamePageHistorySave($pageId): void
    {
        $path = $this->getPath();
        $newPath = $this->getPath($pageId);

        // Si le fichier de pageHistory existe, on ne fait rien
        if (file_exists($newPath)) {
            return;
        }

        // Si le brouillon n'existe pas, on ne fait rien
        if (!file_exists($path)) {
            return;
        }

        $fileSystem = new Filesystem();
        $fileSystem->rename($path, $newPath);
        $fileSystem->remove($path);
    }

    /**
     * Supprime une page history en fonction de son id
     * @param $id
     * @return void
     */
    public function removePageHistory($id = null): void
    {
        $path = $this->getPath($id);
        if (!file_exists($path)) {
            return;
        }
        $fileSystem = new Filesystem();
        $fileSystem->remove($path);
    }

    /**
     * Retourne le path du dossier history
     * @return string
     */
    public function getPathPageHistory() :string
    {
        return $this->pathPageHistory;
    }
}
