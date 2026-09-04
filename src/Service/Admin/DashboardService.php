<?php

declare(strict_types=1);
/**
 * Service pour le dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Service\Admin;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\ApiToken;
use App\Enum\Admin\Comment\Status;
use App\Enum\Admin\Content\Page\PageStatus;
use App\Enum\Admin\System\Options\OptionSystem;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Repository\Admin\Content\Page\PageRepository;
use App\Repository\Admin\System\ApiTokenRepository;
use App\Utils\System\ApiToken\ApiTokenConst;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardService extends AppAdminService
{
    public function __construct(
        #[AutowireLocator(self::HANDLERS)] ContainerInterface $handlers,
        private readonly ApiTokenRepository $apiTokenRepo,
        private readonly CommentRepository $commentRepo,
        private readonly PageRepository $pageRepo,
    ) {
        parent::__construct($handlers);
    }

    /**
     * Retourne les informations du block Help config
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBlockHelpConfig(): array
    {
        $translator = $this->getTranslator();
        $optionSystem = $this->getOptionSystemService();
        $siteName = $optionSystem->getValueByKey(OptionSystem::OS_SITE_NAME->value);
        $adresseSite = $optionSystem->getValueByKey(OptionSystem::OS_ADRESSE_SITE->value);
        $openSite = $optionSystem->getValueByKey(OptionSystem::OS_OPEN_SITE->value);
        $apiTokensDefault = $this->findBy(ApiToken::class, [
            'token' => [ApiTokenConst::API_TOKEN_READ, ApiTokenConst::API_TOKEN_WRITE, ApiTokenConst::API_TOKEN_ADMIN],
        ]);
        $nbApiToken = $this->apiTokenRepo->count([]);

        $configComplete = true;
        $body = [
            OptionSystem::OS_SITE_NAME->value => [
                'success' => true,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.site.name.success',
                    domain: 'dashboard',
                ),
            ],
            OptionSystem::OS_ADRESSE_SITE->value => [
                'success' => true,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.site.adresse.success',
                    domain: 'dashboard',
                ),
            ],
            OptionSystem::OS_OPEN_SITE->value => [
                'success' => true,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.site.open.success',
                    domain: 'dashboard',
                ),
            ],
            'API_TOKEN_STATUS' => [
                'success' => true,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.api_token.status.success',
                    domain: 'dashboard',
                ),
            ],
        ];
        if ($siteName === OptionSystem::OS_SITE_NAME->getDefault()) {
            $body[OptionSystem::OS_SITE_NAME->value] = [
                'success' => false,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.site.name.warning',
                    domain: 'dashboard',
                ),
            ];
            $configComplete = false;
        }

        if ($adresseSite === OptionSystem::OS_ADRESSE_SITE->getDefault()) {
            $body[OptionSystem::OS_ADRESSE_SITE->value] = [
                'success' => false,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.site.adresse.warning',
                    domain: 'dashboard',
                ),
            ];
            $configComplete = false;
        }

        if ($openSite === OptionSystem::OS_OPEN_SITE->getDefault()) {
            $body[OptionSystem::OS_OPEN_SITE->value] = [
                'success' => false,
                'msg' => $translator->trans(
                    'dashboard.block.help.first.connexion.site.open.warning',
                    domain: 'dashboard',
                ),
            ];
            $configComplete = false;
        }

        if (!empty($apiTokensDefault)) {
            $body['API_TOKEN_STATUS']['success'] = false;
            $body['API_TOKEN_STATUS']['msgTitle'] = $translator->trans(
                'dashboard.block.help.first.connexion.api_token.default.warning',
                domain: 'dashboard',
            );
            $configComplete = false;
            $arrayMsg = [];
            foreach ($apiTokensDefault as $apiToken) {
                /** @var ApiToken $apiToken */
                $arrayMsg[] = $translator->trans(
                    'dashboard.block.help.first.connexion.api_token.default.warning.submsg',
                    ['name' => $apiToken->getName()],
                    domain: 'dashboard',
                );
            }
            $body['API_TOKEN_STATUS']['msg'] = $arrayMsg;
        } else {
            if ($nbApiToken === 0) {
                $body['API_TOKEN_STATUS'] = [
                    'success' => false,
                    'msg' => $translator->trans(
                        'dashboard.block.help.first.connexion.api_token.empty.warning',
                        domain: 'dashboard',
                    ),
                ];
                $configComplete = false;
            }
        }

        $router = $this->getRouter();

        $links = [
            'link_options' => [
                'label' => $translator->trans('dashboard.block.help.first.connexion.link.options', domain: 'dashboard'),
                'link' => $router->generate('admin_option-system_change'),
            ],
            'link_tokens' => [
                'label' => $translator->trans('dashboard.block.help.first.connexion.link.tokens', domain: 'dashboard'),
                'link' => $router->generate('admin_api_token_index'),
            ],
        ];

        return ['success' => true, 'body' => $body, 'all', 'configComplete' => $configComplete, 'links' => $links];
    }

    /**
     * Génère le bloc pour les 10 derniers commentaires
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBlockLastComment(): array
    {
        $commentService = $this->getCommentService();

        $result = $this->commentRepo->findBy([], ['id' => 'DESC'], 10);

        $body = [];
        foreach ($result as $comment) {
            /** @var Comment $comment */
            $body[] = [
                'id' => $comment->getId(),
                'author' => $comment->getAuthor(),
                'status' => $commentService->getStatusFormatedByCode($comment->getStatus()),
                'date' => $comment->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return ['success' => true, 'body' => $body];
    }

    /**
     * Retourne le bloc des dernières pages
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBlockLastPageCreate(): array
    {
        $result = $this->pageRepo->findBy([], ['id' => 'DESC'], 10);
        $currentLocale = $this->getLocales()['current'];

        $body = [];
        foreach ($result as $page) {
            $body[] = [
                'id' => $page->getId(),
                'title' => $page->getPageTranslationByLocale($currentLocale)->getTitre(),
                'status' => $this->getStatusFormatedByCode($page->getStatus()),
                'date' => $page->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return ['success' => true, 'body' => $body];
    }

    /**
     * Retourne un status formaté HTML en fonction de son code
     * @param int $status
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getStatusFormatedByCode(int $status): string
    {
        $pageService = $this->getPageService();
        $string = $pageService->getStatusStr($status);

        return match ($status) {
            PageStatus::PUBLISH->value => '<span class="badge ' .
                PageStatus::PUBLISH->getClassCss() .
                '">' .
                $string .
                '</span>',
            PageStatus::ARCHIVED->value => '<span class="badge ' .
                PageStatus::ARCHIVED->getClassCss() .
                '">' .
                $string .
                '</span>',
            PageStatus::DRAFT->value => '<span class="badge ' .
                PageStatus::DRAFT->getClassCss() .
                '">' .
                $string .
                '</span>',
        };
    }
}
