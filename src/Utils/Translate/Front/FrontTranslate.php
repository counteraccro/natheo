<?php
/**
 * Class pour la génération des traductions pour les scripts vue du front
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Translate\Front;

use App\Utils\Translate\AppTranslate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontTranslate extends AppTranslate
{
    /**
     * @var string
     */
    private string $locale;

    public function __construct(
        #[
            AutowireLocator([
                'translator' => TranslatorInterface::class,
                'parameterBag' => ContainerBagInterface::class,
            ]),
        ]
        private readonly ContainerInterface $handlers,
    ) {
        parent::__construct($handlers);
    }

    /**
     * Traduction front
     * @return array[]
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTranslate(string $locale): array
    {
        $this->locale = $locale;

        return [
            'errorApi' => $this->getTranslateErrorApi(),
            'footer' => $this->getTranslateFooter(),
            'header' => $this->getTranslateHeader(),
            'main' => [
                'articleFooter' => $this->getTranslateArticleFooter(),
            ],
        ];
    }

    public function getTranslateErrorApi(): array
    {
        return [
            '401' => $this->translator->trans(
                'front.error.api.modal.error.401',
                domain: 'front',
                locale: $this->locale,
            ),
            '403' => $this->translator->trans(
                'front.error.api.modal.error.403',
                domain: 'front',
                locale: $this->locale,
            ),
        ];
    }

    /**
     * Traduction footer
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getTranslateFooter(): array
    {
        /** @var ContainerBagInterface $parameterBag */
        $parameterBag = $this->handlers->get('parameterBag');

        return [
            'credit' => $this->translator->trans(
                'front.footer.credit',
                ['year' => date('Y'), 'version' => $parameterBag->get('app.version')],
                'front',
                locale: $this->locale,
            ),
            'templateVersion' => $this->translator->trans(
                'front.footer.template.version',
                ['version' => $parameterBag->get('app.natheo_horizon.version')],
                'front',
                locale: $this->locale,
            ),
            'adminLink' => $this->translator->trans('front.footer.admin.link', domain: 'front', locale: $this->locale),
            'githubLink' => $this->translator->trans(
                'front.footer.github.link',
                domain: 'front',
                locale: $this->locale,
            ),
            'sitemapLink' => $this->translator->trans(
                'front.footer.sitemap.link',
                domain: 'front',
                locale: $this->locale,
            ),
            'frLink' => $this->translator->trans('front.footer.fr.link', domain: 'front', locale: $this->locale),
            'esLink' => $this->translator->trans('front.footer.es.link', domain: 'front', locale: $this->locale),
            'enLink' => $this->translator->trans('front.footer.en.link', domain: 'front', locale: $this->locale),
        ];
    }

    /**
     * Traduction header
     * @return array
     */
    private function getTranslateHeader(): array
    {
        return [
            'login' => $this->translator->trans('front.header.login', domain: 'front', locale: $this->locale),
            'logout' => $this->translator->trans('front.header.logout', domain: 'front', locale: $this->locale),
        ];
    }

    private function getTranslateArticleFooter(): array
    {
        return [
            'edit' => $this->translator->trans('front.article.footer.edit', domain: 'front', locale: $this->locale),
            'statPublication' => $this->translator->trans(
                'front.article.footer.statPublication',
                domain: 'front',
                locale: $this->locale,
            ),
            'infoDraft' => $this->translator->trans(
                'front.article.footer.info.draft',
                domain: 'front',
                locale: $this->locale,
            ),
            'comment' => $this->getTranslateComment(),
        ];
    }

    private function getTranslateComment(): array
    {
        return [
            'title' => $this->translator->trans('front.comment.title', domain: 'front', locale: $this->locale),
            'nbComments' => $this->translator->trans(
                'front.comment.nbComments',
                domain: 'front',
                locale: $this->locale,
            ),
            'timeAgo' => $this->translator->trans('front.comment.timeAgo', domain: 'front', locale: $this->locale),
            'validate' => $this->translator->trans('front.comment.validate', domain: 'front', locale: $this->locale),
            'moderate' => $this->translator->trans('front.comment.moderate', domain: 'front', locale: $this->locale),
            'waiting' => $this->translator->trans('front.comment.waiting', domain: 'front', locale: $this->locale),
            'formModerateLabel' => $this->translator->trans(
                'front.comment.form.moderate.label',
                domain: 'front',
                locale: $this->locale,
            ),
            'formModeratePlaceHolder' => $this->translator->trans(
                'front.comment.form.moderate.placeholder',
                domain: 'front',
                locale: $this->locale,
            ),
            'formModerateCancel' => $this->translator->trans(
                'front.comment.form.moderate.cancel',
                domain: 'front',
                locale: $this->locale,
            ),
            'formModerateSubmit' => $this->translator->trans(
                'front.comment.form.moderate.submit',
                domain: 'front',
                locale: $this->locale,
            ),
            'successValidate' => $this->translator->trans(
                'front.comment.success.validate',
                domain: 'front',
                locale: $this->locale,
            ),
            'successModerate' => $this->translator->trans(
                'front.comment.success.moderate',
                domain: 'front',
                locale: $this->locale,
            ),
            'successWaiting' => $this->translator->trans(
                'front.comment.success.waiting',
                domain: 'front',
                locale: $this->locale,
            ),
            'btnNewComment' => $this->translator->trans(
                'front.comment.btn.new.comment',
                domain: 'front',
                locale: $this->locale,
            ),
            'btnNewCommentCancel' => $this->translator->trans(
                'front.comment.btn.new.comment.cancel',
                domain: 'front',
                locale: $this->locale,
            ),
            'btnNewCommentSubmit' => $this->translator->trans(
                'front.comment.btn.new.comment.submit',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentPseudoLabel' => $this->translator->trans(
                'front.comment.form.new.comment.pseudo.label',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentPseudoPlaceholder' => $this->translator->trans(
                'front.comment.form.new.comment.pseudo.placeholder',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentPseudoError' => $this->translator->trans(
                'front.comment.form.new.comment.pseudo.error',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentEmailLabel' => $this->translator->trans(
                'front.comment.form.new.comment.email.label',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentEmailPlaceholder' => $this->translator->trans(
                'front.comment.form.new.comment.email.placeholder',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentCommentLabel' => $this->translator->trans(
                'front.comment.form.new.comment.comment.label',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentCommentPlaceholder' => $this->translator->trans(
                'front.comment.form.new.comment.comment.placeholder',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentCommentError' => $this->translator->trans(
                'front.comment.form.new.comment.error',
                domain: 'front',
                locale: $this->locale,
            ),
            'formNewCommentSuccessMessage' => $this->translator->trans(
                'front.comment.new.success.message',
                domain: 'front',
                locale: $this->locale,
            ),
        ];
    }
}
