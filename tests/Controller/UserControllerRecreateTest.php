<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerRecreateTest extends WebTestCase
{
    use RecreateDatabaseTrait;

    protected ?KernelBrowser $client = null;
    protected ?Crawler $crawler = null;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    /**
     * Test editAction
     * Tests redirection after submit edit form
     */
    public function testEditUserSubmitForm()
    {
        $this->client->followRedirects(false);
        
        $user = $this->getUserRepo()->findOneBy(['email' => 'unique@unique.com']);
        $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $formValues = [
            'username' => $user->getUsername(),
            'firstPassword' => 'azerty',
            'secondPassword' => 'azerty',
            'email' => $user->getEmail()
        ];

        $this->submitUserForm('Modifier', $formValues);
        
        $this->assertResponseRedirects('/users');
    }

    
    /**
     * Sets a default value for create user form and submits
     */
    protected function submitUserForm(string $btnText, array $formValues = null)
    {
        if (is_null($formValues)) {
            $formValues = [
                'username' => 'Beber',
                'firstPassword' => 'azerty',
                'secondPassword' => 'azerty',
                'email' => 'beber@gmail.com'
            ];
        }

        $this->crawler = $this->client->submitForm($btnText, [
                'user[username]' => $formValues['username'],
                'user[password][first]' => $formValues['firstPassword'],
                'user[password][second]' => $formValues['secondPassword'],
                'user[email]' => $formValues['email']
            ]);
    }

    /**
     * Initiates the kernel and gets the user repository
     */
    protected static function getUserRepo():EntityRepository
    {
        self::bootKernel();
        $container = self::$container;
        $manager = $container->get('doctrine.orm.default_entity_manager');
        return $container->get('doctrine.orm.container_repository_factory')->getRepository($manager, 'App:User');
    }
}
