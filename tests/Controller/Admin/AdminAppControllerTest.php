<?php

declare(strict_types=1);
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Tests de la page de l'application Vue du back-office et du renouvellement de son access token
 */

namespace App\Tests\Controller\Admin;

use App\Entity\Admin\System\User;
use App\Service\Api\System\User\UserSessionService;
use App\Tests\AppWebTestCase;

class AdminAppControllerTest extends AppWebTestCase
{
    /**
     * En-têtes d'une navigation du navigateur
     * @var array<string, string>
     */
    private const array HTML = ['HTTP_ACCEPT' => 'text/html,application/xhtml+xml,*/*;q=0.8'];

    /**
     * Sans session : renvoi vers la connexion
     * @return void
     */
    public function testIndexRequiresLogin(): void
    {
        $this->client->request('GET', '/admin/fr/inconnu', server: self::HTML);
        $this->assertResponseRedirects();
        $this->assertStringContainsString('login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    /**
     * Racine du back-office : renvoi vers le tableau de bord
     * @return void
     */
    public function testIndexRootRedirectsToDashboard(): void
    {
        $this->loginContributeur();
        $this->client->request('GET', '/admin/fr/', server: self::HTML);
        $this->assertResponseRedirects($this->router->generate('admin_dashboard_index'));
    }

    /**
     * Adresse inconnue : page de l'application, sans cache, avec sa CSP et son nonce, et données de démarrage
     * @return void
     */
    public function testIndex(): void
    {
        $user = $this->loginContributeur(['firstname' => 'Ada', 'lastname' => 'Lovelace']);
        $this->client->request('GET', '/admin/fr/inconnu/page', server: self::HTML);

        $this->assertResponseIsSuccessful();
        $this->assertThemeInitBeforeStylesheets();
        $response = $this->client->getResponse();
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9+\\/=]+)'/", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        preg_match("/'nonce-([^']+)'/", $csp, $nonce);
        $this->assertSame($nonce[1], $this->client->getCrawler()->filter('script#theme-init')->attr('nonce'));

        $bootstrap = $this->getBootstrap();
        $this->assertSame('fr', $bootstrap['locale']);
        $this->assertSame('/admin/fr/', $bootstrap['basePath']);
        $this->assertSame('/api/v2', $bootstrap['api']['prefix']);
        $this->assertSame($this->router->generate('admin_app_token'), $bootstrap['api']['tokenUrl']);
        $this->assertSame(
            ['id' => $user->getId(), 'name' => 'Ada Lovelace', 'initials' => 'AL'],
            [
                'id' => $bootstrap['user']['id'],
                'name' => $bootstrap['user']['name'],
                'initials' => $bootstrap['user']['initials'],
            ],
        );
        $this->assertNull($bootstrap['user']['impersonating']);
        $this->assertNull($bootstrap['urls']['impersonationExit']);
        $this->assertSame($this->router->generate('admin_dashboard_index'), $bootstrap['urls']['dashboard']);
        $this->assertIsBool($bootstrap['options']['notifications']);
        $this->assertIsInt($bootstrap['options']['itemsPerPage']);
        $this->assertSame(['admin_ui', 'admin_app'], array_keys($bootstrap['messages']));
        $this->assertArrayHasKey('admin_ui.dialog.close', $bootstrap['messages']['admin_ui']);
        $this->assertNull($bootstrap['debug']);

        $session = $this->container
            ->get(UserSessionService::class)
            ->findByAccessToken($bootstrap['api']['accessToken']);
        $this->assertSame($user->getId(), $session?->getUser()->getId());
        $this->assertTrue($session->getApiToken()->isInternal());
        $this->assertSame(
            $session->getAccessTokenExpiresAt()->format(\DateTimeInterface::ATOM),
            $bootstrap['api']['expiresAt'],
        );
    }

    /**
     * Appel AJAX ou autre méthode : réponse de Symfony, pas la page de l'application
     * @return void
     */
    public function testIndexIgnoresNonHtmlRequests(): void
    {
        $this->loginContributeur();
        $this->client->request('GET', '/admin/fr/inconnu', server: ['HTTP_ACCEPT' => 'application/json']);
        $this->assertResponseStatusCodeSame(404);
        $this->client->request('POST', '/admin/fr/inconnu', server: self::HTML);
        $this->assertResponseStatusCodeSame(404);
        $this->client->request('GET', $this->router->generate('admin_tag_tag_by_name'));
        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * Une page Twig existante reste servie par son contrôleur
     * @return void
     */
    public function testTwigRoutesTakePriority(): void
    {
        $this->loginContributeur();
        $this->client->request('GET', $this->router->generate('admin_dashboard_index'), server: self::HTML);
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $this->client->getCrawler()->filter('#admin-bootstrap'));
    }

    /**
     * Access token : réponse sans cache, même token tant qu'il reste valable, celui de la page
     * @return void
     */
    public function testToken(): void
    {
        $this->loginContributeur();
        $this->client->request('GET', '/admin/fr/inconnu', server: self::HTML);
        $pageToken = $this->getBootstrap()['api']['accessToken'];

        $this->client->request('GET', $this->router->generate('admin_app_token'));
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString(
            'no-store',
            (string) $this->client->getResponse()->headers->get('Cache-Control'),
        );
        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertSame(['accessToken', 'expiresAt'], array_keys($data));
        $this->assertSame($pageToken, $data['accessToken']);
    }

    /**
     * Usurpation : la page ouvre la session de l'utilisateur usurpé, avec l'utilisateur d'origine
     * @return void
     */
    public function testIndexDuringImpersonation(): void
    {
        $superAdmin = $this->createUserSuperAdmin(['disabled' => false, 'anonymous' => false]);
        $this->generateDefaultOptionUser($superAdmin);
        $target = $this->createUserContributeur(['disabled' => false, 'anonymous' => false]);
        $this->generateDefaultOptionUser($target);
        $this->client->loginUser($superAdmin, 'admin');

        $this->client->request(
            'GET',
            '/admin/fr/inconnu?_switch_user=' . urlencode((string) $target->getEmail()),
            server: self::HTML,
        );
        $this->client->request('GET', '/admin/fr/inconnu', server: self::HTML);
        $this->assertResponseIsSuccessful();

        $bootstrap = $this->getBootstrap();
        $this->assertSame($target->getId(), $bootstrap['user']['id']);
        $this->assertNotNull($bootstrap['user']['impersonating']);
        $this->assertStringContainsString('_switch_user=_exit', (string) $bootstrap['urls']['impersonationExit']);

        $session = $this->container
            ->get(UserSessionService::class)
            ->findByAccessToken($bootstrap['api']['accessToken']);
        $this->assertSame($target->getId(), $session?->getUser()->getId());
        $this->assertSame($superAdmin->getId(), $session->getImpersonator()?->getId());
    }

    /**
     * Connecte un contributeur avec ses options
     * @param array<string, mixed> $customData
     * @return User
     */
    private function loginContributeur(array $customData = []): User
    {
        $user = $this->createUserContributeur(['disabled' => false, 'anonymous' => false, ...$customData]);
        $this->generateDefaultOptionUser($user);
        $this->client->loginUser($user, 'admin');
        return $user;
    }

    /**
     * Données de démarrage de la page affichée
     * @return array<string, mixed>
     */
    private function getBootstrap(): array
    {
        return json_decode(
            $this->client->getCrawler()->filter('script#admin-bootstrap')->text(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
