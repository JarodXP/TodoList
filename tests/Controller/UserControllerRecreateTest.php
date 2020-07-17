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
        $this->client->followRedirects(false);
        
        $user = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com']);
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
}
