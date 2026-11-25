<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.1
 * Données des tests de bout en bout : options du site, comptes de accounts.json, tags et FAQ des parcours
 */

use App\Kernel;
use App\Tests\Helper\Fixtures\FixturesTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

// bootEnv() lit APP_ENV : sans lui, les variables de .env.local (base de dev) seraient chargées
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
putenv('APP_ENV=test');
require dirname(__DIR__) . '/bootstrap.php';

$kernel = new Kernel('test', true);
$kernel->boot();

$database = (string) $kernel->getContainer()->get('doctrine')->getConnection()->getDatabase();
if (!str_ends_with($database, '_test')) {
    fwrite(STDERR, 'Base « ' . $database . ' » refusée : les données de bout en bout ne vont que dans une base *_test' . PHP_EOL);
    exit(1);
}

$seeder = new class ($kernel->getContainer()->get('test.service_container')) {
    use FixturesTrait;

    protected array $locales = ['fr', 'en', 'es'];

    public function __construct(protected ContainerInterface $container)
    {
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @param array<string, array{email: string, login: string, password: string, role: string}> $accounts
     * @return void
     */
    public function run(array $accounts): void
    {
        $this->generateDefaultOptionSystem();

        $users = [];
        foreach ($accounts as $key => $account) {
            $users[$key] = $this->createUser([
                'email' => $account['email'],
                'login' => $account['login'],
                'password' => $account['password'],
                'roles' => [$account['role']],
                'avatar' => null,
            ]);
        }

        $used = $this->createTagWithLabels($users['contributor'], '#1d6fa5', ['Actualité', 'News', 'Noticias']);
        $this->createPageTag($this->createPage($users['contributor'], ['disabled' => false]), $used);
        $this->createTagWithLabels($users['contributor'], '#2f8a4c', ['Tutoriel', 'Tutorial', 'Tutorial ES']);
        $this->createTagWithLabels($users['admin'], '#9e2e2e', ['Archive', 'Archive EN', 'Archivo'], true);

        $usedFaq = $this->createFaqWithTree($users['contributor'], 'FAQ Produit', [
            'Achat' => ['Délai de livraison ?', 'Retour possible ?'],
            'Paiement' => ['Moyens de paiement ?'],
        ]);
        $this->createPageContent(
            $this->createPage($users['contributor'], ['disabled' => false]),
            ['type' => \App\Enum\Admin\Content\Page\PageContentType::FAQ->value, 'typeId' => $usedFaq->getId()],
        );
        $this->createFaqWithTree($users['admin'], 'FAQ Interne', ['Équipe' => ['Qui contacter ?']]);
    }

    /**
     * FAQ active, ses catégories et leurs questions, textes français repris en anglais et en espagnol avec le suffixe
     * de la langue
     * @param \App\Entity\Admin\System\User $user
     * @param string $title
     * @param array<string, string[]> $tree titre de catégorie => titres de ses questions
     * @return \App\Entity\Admin\Content\Faq\Faq
     */
    private function createFaqWithTree(
        \App\Entity\Admin\System\User $user,
        string $title,
        array $tree,
    ): \App\Entity\Admin\Content\Faq\Faq {
        $text = fn(string $value, string $locale): string => $locale === 'fr' ? $value : $value . ' ' . $locale;
        $faq = $this->createFaq($user, ['disabled' => false]);
        foreach ($this->locales as $locale) {
            $this->createFaqTranslation($faq, ['locale' => $locale, 'title' => $text($title, $locale)]);
        }
        $position = 1;
        foreach ($tree as $categoryTitle => $questions) {
            $category = $this->createFaqCategory($faq, ['renderOrder' => $position++, 'disabled' => false]);
            foreach ($this->locales as $locale) {
                $this->createFaqCategoryTranslation($category, [
                    'locale' => $locale,
                    'title' => $text($categoryTitle, $locale),
                ]);
            }
            foreach (array_values($questions) as $i => $questionTitle) {
                $question = $this->createFaqQuestion($category, ['renderOrder' => $i + 1, 'disabled' => false]);
                foreach ($this->locales as $locale) {
                    $this->createFaqQuestionTranslation($question, [
                        'locale' => $locale,
                        'title' => $text($questionTitle, $locale),
                        'answer' => $text('Réponse à « ' . $questionTitle . ' »', $locale),
                    ]);
                }
            }
        }
        return $faq;
    }

    /**
     * @param \App\Entity\Admin\System\User $user
     * @param string $color
     * @param string[] $labels libellés fr, en, es
     * @param bool $disabled
     * @return \App\Entity\Admin\Content\Tag\Tag
     */
    private function createTagWithLabels(
        \App\Entity\Admin\System\User $user,
        string $color,
        array $labels,
        bool $disabled = false,
    ): \App\Entity\Admin\Content\Tag\Tag {
        $tag = $this->createTag($user, ['color' => $color, 'disabled' => $disabled]);
        foreach ($this->locales as $i => $locale) {
            $this->createTagTranslation($tag, ['locale' => $locale, 'label' => $labels[$i]]);
        }
        return $tag;
    }
};

$seeder->run(json_decode((string) file_get_contents(__DIR__ . '/accounts.json'), true, flags: JSON_THROW_ON_ERROR));
$kernel->shutdown();
