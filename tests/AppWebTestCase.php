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

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->router = $this->container->get('router');
        $this->translator = $this->container->get(TranslatorInterface::class);

        $this->em = $this->container->get('doctrine')->getManager();

        $this->client->request('GET', '/');
        $session = $this->client->getRequest()->getSession();
        $session->set('_locale', 'fr');
    }

}