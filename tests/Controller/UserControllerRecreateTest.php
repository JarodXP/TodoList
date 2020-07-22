<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityRepository;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This test class is related to the same than UserControllerTest,
 * but uses a different database connection trait to avoid database
 * conflict in the same transaction.
 */
class UserControllerRecreateTest extends WebTestCase
{
    use RecreateDatabaseTrait;
    use ControllerUtilsTrait;

    protected ?KernelBrowser $client = null;
    protected ?Crawler $crawler = null;

    public function setUp():void
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
        //Login with admin role
        $this->authenticateClient('admin@admin.com');
        
        $this->client->followRedirects(false);
        
        $user = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com']);
        $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $formValues = [
            'username' => $user->getUsername(),
            'firstPassword' => 'azerty',
            'secondPassword' => 'azerty',
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ];

        $this->submitUserForm('Modifier', $formValues);
        
        $this->assertResponseRedirects('/users');
    }

    /**
     * test createAtion
     * Tests that role is registered in database after subscription.
     */
    public function testRoleIsSetWhenUserIsCreated()
    {
        $this->client->request('GET', '/users/create');

        $formValues = [
            'username' => 'admin0',
            'firstPassword' => 'azerty',
            'secondPassword' => 'azerty',
            'email' => 'admin@gmail.com',
            'roles' => ['ROLE_ADMIN']
        ];

        $this->submitUserForm('Ajouter', $formValues);
        
        /**@var App/Entity/User $newUser */
        $newUser = $this->getEntityRepo('App:User')->findOneBy(['email' => 'admin@gmail.com']);

        $this->assertSame(['ROLE_ADMIN'], $newUser->getRoles());
    }
}
