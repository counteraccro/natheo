<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Seul accès aux fichiers de la médiathèque : rangement, corbeille, restauration et suppression
 */

namespace App\Service\Global\Media;

use App\Dto\Media\MediaStorageConfig;
use App\Dto\Media\StoredMediaFile;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\String\ByteString;
use Symfony\Component\String\Slugger\AsciiSlugger;

class MediaStorage
{
    /**
     * Longueur maximale du nom slugifié dans une clé
     */
    private const int SLUG_MAX_LENGTH = 60;

    /**
     * Longueur du suffixe aléatoire d'une clé
     */
    private const int RANDOM_LENGTH = 8;

    private ?FilesystemOperator $storage = null;

    private ?FilesystemOperator $trash = null;

    public function __construct(
        private readonly MediaStorageConfigResolver $configResolver,
        private readonly MediaImageProcessor $imageProcessor,
    ) {}

    /**
     * Range un fichier envoyé : photo redressée et sans métadonnées, miniature des images
     * @param string $sourcePath fichier local envoyé, laissé intact
     * @param string $originalName nom d'origine, base de la clé
     * @param \DateTimeInterface|null $date date de l'envoi, pour le dossier du mois
     * @return StoredMediaFile
     * @throws FilesystemException
     * @throws MediaFileException
     */
    public function store(string $sourcePath, string $originalName, ?\DateTimeInterface $date = null): StoredMediaFile
    {
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            throw new MediaFileException(MediaFileException::UNREADABLE);
        }
        $hash = (string) hash_file('sha256', $sourcePath);
        $mimeType = (string) mime_content_type($sourcePath);

        $workPath = (string) tempnam(sys_get_temp_dir(), 'media');
        try {
            copy($sourcePath, $workPath);
            $isImage = in_array($mimeType, MediaImageProcessor::IMAGE_MIME_TYPES, true);
            if ($isImage) {
                $this->imageProcessor->normalize($workPath, $mimeType);
            }
            $dimensions = $isImage ? $this->imageProcessor->dimensions($workPath) : null;

            $key = $this->generateKey($originalName, $date ?? new \DateTimeImmutable());
            $this->writeFile($this->servedFilesystem(), $key, $workPath);

            $thumbnail = $isImage ? $this->imageProcessor->thumbnail($workPath, $mimeType) : null;
            if ($thumbnail !== null) {
                $this->servedFilesystem()->write(MediaStorageConfig::thumbnailKey($key), $thumbnail);
            }

            return new StoredMediaFile(
                $key,
                $mimeType,
                (int) filesize($workPath),
                $hash,
                $dimensions[0] ?? null,
                $dimensions[1] ?? null,
                $thumbnail !== null,
            );
        } finally {
            @unlink($workPath);
        }
    }

    /**
     * Range la couverture d'une vidéo à la place de sa miniature
     * @param string $key clé de la vidéo
     * @param string $sourcePath image envoyée
     * @return void
     * @throws FilesystemException
     * @throws MediaFileException
     */
    public function storeCover(string $key, string $sourcePath): void
    {
        $this->servedFilesystem()->write(
            MediaStorageConfig::thumbnailKey($key),
            $this->imageProcessor->cover($sourcePath),
        );
    }

    /**
     * Fichier et miniature déplacés dans la corbeille, qui n'est pas servie
     * @param string $key
     * @return void
     * @throws FilesystemException
     */
    public function trash(string $key): void
    {
        $this->transfer($key, $this->servedFilesystem(), $this->trashFilesystem());
    }

    /**
     * Fichier et miniature remis dans le stockage servi
     * @param string $key
     * @return void
     * @throws FilesystemException
     */
    public function restore(string $key): void
    {
        $this->transfer($key, $this->trashFilesystem(), $this->servedFilesystem());
    }

    /**
     * Fichier et miniature effacés, qu'ils soient à la corbeille ou non
     * @param string $key
     * @return void
     * @throws FilesystemException
     */
    public function delete(string $key): void
    {
        foreach ([$this->servedFilesystem(), $this->trashFilesystem()] as $filesystem) {
            $filesystem->delete($key);
            $filesystem->delete(MediaStorageConfig::thumbnailKey($key));
        }
    }

    /**
     * Fichier présent dans le stockage servi
     * @param string $key
     * @return bool
     * @throws FilesystemException
     */
    public function exists(string $key): bool
    {
        return $this->servedFilesystem()->fileExists($key);
    }

    /**
     * Fichier présent à la corbeille
     * @param string $key
     * @return bool
     * @throws FilesystemException
     */
    public function isTrashed(string $key): bool
    {
        return $this->trashFilesystem()->fileExists($key);
    }

    /**
     * Miniature ou couverture présente dans le stockage servi
     * @param string $key clé du fichier
     * @return bool
     * @throws FilesystemException
     */
    public function hasThumbnail(string $key): bool
    {
        return $this->servedFilesystem()->fileExists(MediaStorageConfig::thumbnailKey($key));
    }

    /**
     * Clé libre <aaaa>/<mm>/<nom-slugifié>-<aléa>.<ext>
     * @param string $originalName
     * @param \DateTimeInterface $date
     * @return string
     * @throws FilesystemException
     */
    private function generateKey(string $originalName, \DateTimeInterface $date): string
    {
        $extension = strtolower((string) preg_replace('/[^a-z0-9]/i', '', pathinfo($originalName, PATHINFO_EXTENSION)));
        $slug = (new AsciiSlugger())
            ->slug(pathinfo($originalName, PATHINFO_FILENAME))
            ->lower()
            ->truncate(self::SLUG_MAX_LENGTH)
            ->trim('-')
            ->toString();
        $slug = $slug === '' ? 'media' : $slug;

        do {
            $key =
                $date->format('Y/m') .
                '/' .
                $slug .
                '-' .
                ByteString::fromRandom(self::RANDOM_LENGTH, 'abcdefghijklmnopqrstuvwxyz0123456789')->toString() .
                ($extension === '' ? '' : '.' . $extension);
        } while ($this->servedFilesystem()->fileExists($key) || $this->trashFilesystem()->fileExists($key));

        return $key;
    }

    /**
     * Déplace un fichier et sa miniature d'un stockage à l'autre
     * @param string $key
     * @param FilesystemOperator $from
     * @param FilesystemOperator $to
     * @return void
     * @throws FilesystemException
     */
    private function transfer(string $key, FilesystemOperator $from, FilesystemOperator $to): void
    {
        foreach ([$key, MediaStorageConfig::thumbnailKey($key)] as $path) {
            if (!$from->fileExists($path)) {
                continue;
            }
            $stream = $from->readStream($path);
            try {
                $to->writeStream($path, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            $from->delete($path);
        }
    }

    /**
     * Écrit un fichier local dans un stockage
     * @param FilesystemOperator $filesystem
     * @param string $key
     * @param string $localPath
     * @return void
     * @throws FilesystemException
     */
    private function writeFile(FilesystemOperator $filesystem, string $key, string $localPath): void
    {
        $stream = fopen($localPath, 'rb');
        if ($stream === false) {
            throw new MediaFileException(MediaFileException::UNREADABLE);
        }
        try {
            $filesystem->writeStream($key, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /**
     * Stockage des fichiers servis
     * @return FilesystemOperator
     */
    private function servedFilesystem(): FilesystemOperator
    {
        return $this->storage ??= new Filesystem(
            new LocalFilesystemAdapter($this->configResolver->resolve()->storageRoot, lazyRootCreation: true),
        );
    }

    /**
     * Stockage de la corbeille
     * @return FilesystemOperator
     */
    private function trashFilesystem(): FilesystemOperator
    {
        return $this->trash ??= new Filesystem(
            new LocalFilesystemAdapter($this->configResolver->resolve()->trashRoot, lazyRootCreation: true),
        );
    }
}
