<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Base des tests des ressources AdminComment et AdminCommentStatusCount
 */

namespace App\Tests\Api\V2\Admin\Content\Comment;

use App\Entity\Admin\Content\Page\Page;
use App\Entity\Admin\System\ActionLog;
use App\Entity\Admin\System\User;
use App\Enum\Admin\Content\Page\PageCategory;
use App\Tests\Api\V2\AppApiV2TestCase;

abstract class AdminCommentTestCase extends AppApiV2TestCase
{
    /**
     * Article de blog traduit dans chaque langue, « <title> » en français et « <title> en|es » ailleurs
     * @param string $title
     * @param string[] $locales
     * @return Page
     */
    protected function createTitledPage(string $title, array $locales = ['fr', 'en', 'es']): Page
    {
        $page = $this->createPage(customData: ['category' => PageCategory::BLOG->value]);
        foreach ($locales as $locale) {
            $this->createPageTranslation($page, [
                'locale' => $locale,
                'titre' => $locale === 'fr' ? $title : $title . ' ' . $locale,
                'url' => strtolower(str_replace(' ', '-', $title)) . '-' . $locale,
            ]);
        }
        return $page;
    }

    /**
     * En-têtes d'une session back-office d'un nouveau contributeur
     * @param User|null $user rempli avec le contributeur créé
     * @return array<string, string>
     */
    protected function contributorHeaders(?User &$user = null): array
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        return $this->adminSessionHeaders($user);
    }

    /**
     * Actions du journal, de la plus ancienne à la plus récente
     * @return array<int, array{0: string|null, 1: string|null, 2: string|null}>
     */
    protected function logs(): array
    {
        return array_map(
            fn(ActionLog $log) => [$log->getAction(), $log->getResourceId(), $log->getLabel()],
            $this->em->getRepository(ActionLog::class)->findBy(['module' => 'comment'], ['id' => 'ASC']),
        );
    }

    /**
     * Identifiants des commentaires d'une collection
     * @param string $path
     * @param array $headers
     * @return int[]
     */
    protected function ids(string $path, array $headers): array
    {
        $response = $this->requestV2('GET', $path, headers: $headers);
        $this->assertEquals(200, $response->getStatusCode(), $path);
        return array_column($this->decode($response)['member'], 'id');
    }

    /**
     * Refus sans jeton (401), avec un jeton public, un jeton back-office sans session ou une session sans le rôle
     * du module (403)
     * @param string $method
     * @param string $path
     * @param array|null $body
     * @return void
     */
    protected function assertAccessDenied(string $method, string $path, ?array $body = null): void
    {
        $this->assertEquals(401, $this->requestV2($method, $path, self::HEADER_NONE, $body)->getStatusCode());
        $this->assertProblem($this->requestV2($method, $path, body: $body), 403);
        $this->assertProblem(
            $this->requestV2($method, $path, body: $body, headers: $this->backOfficeTokenHeaders()),
            403,
        );
        $user = $this->createUser(['disabled' => false, 'anonymous' => false, 'roles' => ['ROLE_USER']]);
        $this->assertProblem(
            $this->requestV2($method, $path, body: $body, headers: $this->adminSessionHeaders($user)),
            403,
        );
    }
}
