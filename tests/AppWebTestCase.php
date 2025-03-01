<?php
namespace App\Tests;

use App\Tests\Helper\Fixtures\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;
use Symfony\Contracts\Translation\TranslatorInterface;

class AppWebTestCase extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var KernelBrowser
     */
    protected KernelBrowser $client;

    /**
     * @var mixed|object|Container|null
     */
    protected Router $router;

    /**
     * @var mixed|object|Container|null
     */
    protected TranslatorInterface $translator;

    protected array $locales =  ['fr', 'es', 'en'];

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->router = $this->container->get('router');
        $this->translator = $this->container->get(TranslatorInterface::class);

        $this->em = $this->container->get('doctrine')->getManager();

        $this->client->request('GET', '/', parameters: ['_locale' => 'fr']);
        $session = $this->client->getRequest()->getSession();
        $session->set('_locale', 'fr');

        $this->generateDefaultOptionSystem();
    }

    /**
     * Vérifie si le user n'a pas accès à la route définie. Si le user est null, utilise le user avec le role ROLE_USER
     * @param string $route
     * @param array $params
     * @param string $methode
     * @param null $user
     * @param string $firewall
     * @return void
     */
    public function checkNoAccess(string $route, array $params = [], string $methode = 'GET', $user = null, string $firewall = 'admin'): void
    {
        if($user === null) {
            $user = $this->createUser();
        }
        $this->generateDefaultOptionUser($user);

        $this->client->loginUser($user, $firewall);
        $this->client->request($methode, $this->router->generate($route, $params));

        $this->assertResponseStatusCodeSame(403);
    }

}