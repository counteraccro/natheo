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
     * Constructeur
     * @param ContainerBagInterface $containerBag
     * @param User $user
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerBagInterface $containerBag, User $user)
    {
        $this->user = $user;

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
        $fileSystem = new Filesystem();
        if(!$fileSystem->exists($path))
        {
            $fileSystem->mkdir($path);
        }
    }

    public function save(array $page)
    {
        
    }
}
