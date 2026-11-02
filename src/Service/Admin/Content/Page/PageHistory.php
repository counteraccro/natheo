<?php

declare(strict_types=1);
/**
 * Gestion de l'historique des modifications de pages
 * sauvegarde, restauration, listing, clean
 * Instancié avec l'utilisateur courant, ce n'est pas un service du conteneur
 * @author Gourdon Aymeric
 * @version 2.0
 */

namespace App\Service\Admin\Content\Page;

use App\Entity\Admin\System\User;
use App\Enum\Admin\System\Options\OptionUser;
use App\Utils\System\User\PersonalData;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Exclude]
class PageHistory
{
    /**
     * Nombre maximum d'entrées conservées par historique (les plus anciennes sont supprimées)
     * @var int
     */
    public const int MAX_ENTRIES = 50;

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

        $this->pathPageHistory = $kernel . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . $this->directoryHistory;

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
        $id = isset($page['id']) && is_numeric($page['id']) ? (int) $page['id'] : null;
        $path = $this->getPath($id);
        $lines = $this->getContentFile($id);
        $lines[] = $this->serialize($page);
        $this->writeLines($path, $lines);
    }

    /**
     * Écrit les lignes d'historique en ne gardant que les MAX_ENTRIES plus récentes
     * @param string $path
     * @param string[] $lines
     * @return void
     */
    private function writeLines(string $path, array $lines): void
    {
        $lines = array_slice($lines, -self::MAX_ENTRIES);
        $this->filesystem->dumpFile($path, implode("\n", $lines) . "\n");
    }

    /**
     * Retourne le chemin complet de l'historique d'une page en fonction de son id
     * @param int|null $id
     * @return string
     */
    private function getPath(?int $id = null): string
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

        $personalData = new PersonalData(
            $this->user,
            $this->user->getOptionUserByKey(OptionUser::OU_DEFAULT_PERSONAL_DATA_RENDER->value)->getValue(),
        );

        $data = ['time' => time(), 'user' => $personalData->getPersonalData(), 'pageH' => $page];
        return $serializer->serialize($data, 'json');
    }

    /**
     * Retourne le contenu d'un fichier en fonction de son id
     * Si le fichier n'existe pas, retourne un tableau vide
     * @param int|null $id
     * @return array
     */
    private function getContentFile(?int $id = null): array
    {
        return $this->readLines($this->getPath($id));
    }

    /**
     * Lit les lignes non vides d'un fichier d'historique
     * @param string $path
     * @return string[]
     */
    private function readLines(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $lines = [];
        $file = new \SplFileObject($path);
        $file->setFlags(\SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD);
        foreach ($file as $line) {
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Retourne l'ensemble de l'historique d'une page en fonction de son id
     * @param int|null $id
     * @return array
     */
    public function getHistory(?int $id = null): array
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
    public function getPageHistoryById(int $rowId, ?int $pageId = null): array
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
     * Rattache le brouillon d'historique de l'utilisateur à une page
     * Si la page a déjà un historique, le brouillon y est ajouté : il n'est jamais réutilisé pour une autre page
     * @param int $pageId
     * @return void
     */
    public function renamePageHistorySave(int $pageId): void
    {
        $path = $this->getPath();
        if (!file_exists($path)) {
            return;
        }

        $newPath = $this->getPath($pageId);
        if (file_exists($newPath)) {
            $this->writeLines($newPath, array_merge($this->readLines($newPath), $this->readLines($path)));
            $this->filesystem->remove($path);
            return;
        }

        $this->filesystem->rename($path, $newPath);
    }

    /**
     * Supprime une page history en fonction de son id
     * @param int|null $id
     * @return void
     */
    public function removePageHistory(?int $id = null): void
    {
        $path = $this->getPath($id);
        if (!file_exists($path)) {
            return;
        }
        $this->filesystem->remove($path);
    }

    /**
     * Supprime le dossier history et history-test
     * @return void
     */
    public function removePageHistoryFolder(): void
    {
        if ($this->filesystem->exists($this->getPathPageHistory())) {
            $this->filesystem->remove($this->getPathPageHistory());
        }

        if ($this->filesystem->exists($this->getPathPageHistory() . '-test')) {
            $this->filesystem->remove($this->getPathPageHistory() . '-test');
        }
    }

    /**
     * Retourne le path du dossier history
     * @return string
     */
    public function getPathPageHistory(): string
    {
        return $this->pathPageHistory;
    }
}
