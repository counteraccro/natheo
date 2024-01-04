<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Service gérant la FAQ
 */
namespace App\Service\Admin\Content\Faq;

use App\Service\Admin\GridService;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class FaqService extends AppService
{

    /**
     * @var GridService
     */
    private GridService $gridService;

    public function __construct(
        TranslatorInterface    $translator,
        RequestStack           $requestStack,
        Security               $security,
        ContainerBagInterface  $params,
        EntityManagerInterface $entityManager,
        GridService            $gridService
    )
    {
        $this->gridService = $gridService;
        parent::__construct($translator, $requestStack, $security, $params, $entityManager);
    }
}
