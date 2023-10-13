<?php
/**
 * Gestion de l'historique des modifications de pages
 * sauvegarde, restauration, listing, clean
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Page;

use App\Entity\Admin\System\User;
use App\Utils\Utils;
use PhpCsFixer\Finder;
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
     * Path pour les historiques de page pour le user
     * @var string
     */
    private string $pathPageHistoryUser;

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
        $this->pathPageHistory = $kernel . DIRECTORY_SEPARATOR . 'var'
            . DIRECTORY_SEPARATOR . $this->directoryHistory;

        $this->isExistDirectory($this->pathPageHistory);
        $this->pathPageHistoryUser = $this->pathPageHistory .
            DIRECTORY_SEPARATOR . 'history-user-' . $this->user->getId();
        $this->isExistDirectory($this->pathPageHistoryUser);

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
            $id = 0;
        }
        return $this->pathPageHistoryUser . DIRECTORY_SEPARATOR . $this->fileName . $id . $this->fileExt;
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
        $data = ['time' => time(), 'pageH' => $page];
        return $serializer->serialize($data, 'json');
    }

    /**
     * Retourne le contenu d'un fichier en fonction de son id
     * @param int $id
     * @return array
     */
    private function getContentFile(int $id)
    {
        $path = $this->getPath($id);
        return file($path);
    }

    /**
     * Retourne l'ensemble de l'historique d'une page en fonction de son id
     * @param int $id
     * @return array
     */
    public function getHistory(int $id): array
    {
        $datas = $this->getContentFile($id);
        $return = [];
        foreach($datas as $key => $row)
        {
            $array = json_decode($row, true);
            $return[] = ['time' => $array['time'], 'id' => $key];
        }
        return $return;
    }
}
