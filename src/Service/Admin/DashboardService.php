<?php
/**
 * Service pour le dashboard
 * @author Gourdon Aymeric
 * @version 1.0
 */
namespace App\Service\Admin;

use App\Entity\Admin\Content\Comment\Comment;
use App\Entity\Admin\System\ApiToken;
use App\Repository\Admin\Content\Comment\CommentRepository;
use App\Utils\System\ApiToken\ApiTokenConst;
use App\Utils\System\Options\OptionSystemKey;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DashboardService extends AppAdminService
{

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
        $siteName = $optionSystem->getValueByKey(OptionSystemKey::OS_SITE_NAME);
        $adresseSite = $optionSystem->getValueByKey(OptionSystemKey::OS_ADRESSE_SITE);
        $openSite = $optionSystem->getValueByKey(OptionSystemKey::OS_OPEN_SITE);
        $apiTokensDefault = $this->findBy(ApiToken::class, ['token' => [ApiTokenConst::API_TOKEN_READ, ApiTokenConst::API_TOKEN_WRITE, ApiTokenConst::API_TOKEN_ADMIN]]);
        $nbApiToken = $this->getRepository(ApiToken::class)->count([]);

        $configComplete = true;
        $body = [
            OptionSystemKey::OS_SITE_NAME => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.site.name.success', domain: 'dashboard'),
            ],
            OptionSystemKey::OS_ADRESSE_SITE => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.site.adresse.success', domain: 'dashboard'),
            ],
            OptionSystemKey::OS_OPEN_SITE => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.site.open.success', domain: 'dashboard'),
            ],
            'API_TOKEN_STATUS' => [
                'success' => true,
                'msg' => $translator->trans('dashboard.block.help.first.connexion.api_token.status.success', domain: 'dashboard'),
            ]
        ];
        if ($siteName === OptionSystemKey::OS_SITE_NAME_DEFAULT_VALUE) {
            $body[OptionSystemKey::OS_SITE_NAME] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.site.name.warning', domain: 'dashboard')];
            $configComplete = false;
        }

        if ($adresseSite === OptionSystemKey::OS_ADRESSE_SITE_DEFAULT_VALUE) {
            $body[OptionSystemKey::OS_ADRESSE_SITE] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.site.adresse.warning', domain: 'dashboard')];
            $configComplete = false;
        }

        if ($openSite === OptionSystemKey::OS_OPEN_SITE_DEFAULT_VALUE) {
            $body[OptionSystemKey::OS_OPEN_SITE] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.site.open.warning', domain: 'dashboard')];
            $configComplete = false;
        }

        if (!empty($apiTokensDefault)) {
            $body['API_TOKEN_STATUS']['success'] = false;
            $body['API_TOKEN_STATUS']['msgTitle'] = $translator->trans('dashboard.block.help.first.connexion.api_token.default.warning', domain: 'dashboard');
            $configComplete = false;
            $arrayMsg = [];
            foreach ($apiTokensDefault as $apiToken) {
                /** @var ApiToken $apiToken */
                $arrayMsg[] = $translator->trans('dashboard.block.help.first.connexion.api_token.default.warning.submsg', ['name' => $apiToken->getName()], domain: 'dashboard');
            }
            $body['API_TOKEN_STATUS']['msg'] = $arrayMsg;
        } else {
            if ($nbApiToken === 0) {
                $body['API_TOKEN_STATUS'] = ['success' => false, 'msg' => $translator->trans('dashboard.block.help.first.connexion.api_token.empty.warning', domain: 'dashboard')];
                $configComplete = false;
            }
        }

        return ['success' => true, 'body' => $body, 'all', 'configComplete' => $configComplete];
    }

    /**
     * Génère le bloc pour les 10 derniers commentaires
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getBlockLastComment()
    {
        /** @var CommentRepository $repository */
        $repository = $this->getRepository(Comment::class);
        $commentService = $this->getCommentService();

        $result = $repository->findBy([], ['id' => 'DESC'], 10);

        $body = [];
        foreach($result as $comment) {
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
}
