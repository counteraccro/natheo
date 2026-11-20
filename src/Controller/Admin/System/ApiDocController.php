<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Documentation de l'API v2 avec les opérations d'administration
 */

namespace App\Controller\Admin\System;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use App\Api\V2\Documentation\AdminOperationsOpenApiFactory;
use App\Controller\Admin\AppAdminController;
use App\Enum\Admin\Global\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/admin/{_locale}/api-docs', name: 'admin_api_doc_', requirements: ['_locale' => '%app.supported_locales%'])]
#[IsGranted('ROLE_ADMIN')]
class ApiDocController extends AppAdminController
{
    /**
     * Swagger UI sur la spécification OpenAPI non filtrée
     * @param OpenApiFactoryInterface $openApiFactory
     * @param NormalizerInterface $normalizer
     * @return Response
     * @throws ExceptionInterface
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(OpenApiFactoryInterface $openApiFactory, NormalizerInterface $normalizer): Response
    {
        $breadcrumb = [
            Breadcrumb::DOMAIN->value => 'api_token',
            Breadcrumb::BREADCRUMB->value => [
                'api_token.doc.title' => '#',
            ],
        ];

        $openApi = $openApiFactory([AdminOperationsOpenApiFactory::WITH_ADMIN => true]);
        return $this->render('admin/system/api_doc/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'swaggerData' => [
                'spec' => $normalizer->normalize($openApi, 'json'),
                'oauth' => ['enabled' => false, 'redirectUrl' => ''],
                'persistAuthorization' => false,
                'extraConfiguration' => ['layout' => 'BaseLayout'],
            ],
        ]);
    }
}
