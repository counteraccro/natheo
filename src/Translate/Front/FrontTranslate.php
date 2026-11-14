<?php

declare(strict_types=1);
/**
 * Class pour la génération des traductions pour les scripts vue du front
 * @author Gourdon Aymeric
 * @version 1.1
 */

namespace App\Translate\Front;

use App\Translate\AppTranslate;
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
            'menu' => [
                'headerLabel' => $this->trans('front.menu.header_label'),
                'sideLabel' => $this->trans('front.menu.side_label'),
                'footerLabel' => $this->trans('front.menu.footer_label'),
                'submenu' => $this->trans('front.menu.submenu'),
            ],
            'main' => [
                'articleFooter' => $this->getTranslateArticleFooter(),
                'breadcrumb' => $this->trans('front.breadcrumb.index'),
                'editLink' => $this->trans('front.page.edit_link'),
                'loading' => $this->trans('front.page.loading'),
                'headerImgAlt' => $this->trans('front.article.header.img.alt'),
                'updatedAt' => $this->trans('front.page.updated_at'),
                'readingTime' => $this->transPlural('front.page.reading_time'),
                'tagsLabel' => $this->trans('front.page.tags_label'),
                'listing' => $this->getTranslateListing(),
                'pagination' => $this->getTranslatePagination(),
            ],
        ];
    }

    /**
     * Traduction d'une clé du domaine front dans la locale courante
     * @param string $key
     * @return string
     */
    private function trans(string $key): string
    {
        return $this->translator->trans($key, domain: 'front', locale: $this->locale);
    }

    /**
     * Formes d'un libellé accordé à un nombre (clés <$key>_one et <$key>_other), avec « # » à la place du nombre
     * @param string $key
     * @return array{one: string, other: string}
     */
    private function transPlural(string $key): array
    {
        return ['one' => $this->trans($key . '_one'), 'other' => $this->trans($key . '_other')];
    }

    /**
     * Traduction des erreurs API
     * @return array
     */
    public function getTranslateErrorApi(): array
    {
        return [
            '404' => $this->trans('front.error.api.modal.error.404'),
            '429' => $this->trans('front.error.api.modal.error.429'),
            'default' => $this->trans('front.error.api.modal.error.default'),
            'tokenMissing' => $this->trans('front.error.api.token.missing'),
            'back' => $this->trans('front.error.api.modal.back'),
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
            'sitemapLink' => $this->translator->trans(
                'front.footer.sitemap.link',
                domain: 'front',
                locale: $this->locale,
            ),
            'frLink' => $this->translator->trans('front.footer.fr.link', domain: 'front', locale: $this->locale),
            'esLink' => $this->translator->trans('front.footer.es.link', domain: 'front', locale: $this->locale),
            'enLink' => $this->translator->trans('front.footer.en.link', domain: 'front', locale: $this->locale),
            'socialLabel' => $this->trans('front.footer.social_label'),
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
            'openMenu' => $this->trans('front.header.open.menu'),
            'darkMode' => $this->trans('front.header.dark.mode'),
            'skipLink' => $this->trans('front.header.skip_link'),
            'closeMenu' => $this->trans('front.header.close_menu'),
            'localeLabel' => $this->trans('front.header.locale_label'),
        ];
    }

    /**
     * Traduction contenu de type listing
     * @return array
     */
    private function getTranslateListing(): array
    {
        return [
            'by' => $this->trans('front.listing.by'),
            'empty' => $this->trans('front.listing.empty'),
            'pages' => $this->transPlural('front.listing.pages'),
        ];
    }

    /**
     * Traduction pagination
     * @return array
     */
    private function getTranslatePagination(): array
    {
        return [
            'of' => $this->trans('front.pagination.of'),
            'label' => $this->trans('front.pagination.label'),
            'first' => $this->trans('front.pagination.first'),
            'previous' => $this->trans('front.pagination.previous'),
            'next' => $this->trans('front.pagination.next'),
            'last' => $this->trans('front.pagination.last'),
        ];
    }

    private function getTranslateArticleFooter(): array
    {
        return [
            'reads' => $this->transPlural('front.article.footer.reads'),
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
            'sortLabel' => $this->trans('front.comment.sort_label'),
            'sortDesc' => $this->trans('front.comment.sort_desc'),
            'sortAsc' => $this->trans('front.comment.sort_asc'),
            'statusWait' => $this->trans('front.comment.status_wait'),
            'statusModerate' => $this->trans('front.comment.status_moderate'),
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
                'front.comment.button.new.comment',
                domain: 'front',
                locale: $this->locale,
            ),
            'btnNewCommentSubmit' => $this->translator->trans(
                'front.comment.button.new.comment_submit',
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
            'formNewCommentSuccessMessage' => $this->translator->trans(
                'front.comment.new.success.message',
                domain: 'front',
                locale: $this->locale,
            ),
            'moderationPrefix' => $this->trans('front.comment.moderation.prefix'),
            'pagination' => $this->getTranslatePagination(),
        ];
    }
}
