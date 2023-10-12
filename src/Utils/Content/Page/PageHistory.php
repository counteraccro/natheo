<?php
/**
 * Gestion de l'historique des modifications de pages
 * sauvegarde, restauration, listing, clean
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Page;

use App\Entity\Admin\System\User;
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
        $id = $page['id'];
        if ($id === null) {
            $id = 0;
        }

        $path = $this->pathPageHistoryUser . DIRECTORY_SEPARATOR . 'page-' . $id . '.txt';
        $this->filesystem->appendToFile($path, $this->serialize($page) . "\n");
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
     * Retourne l'ensemble de l'historique d'une page en fonction de son id
     * @param int $id
     * @return array
     */
    public function getHistory(int $id): array
    {
        return [$id];
    }
}
