<?php

namespace App\Tests\Controller;

use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class UserControllerTest extends WebTestCase
{
    private ?KernelBrowser $_client = null;
    private ?Crawler $_crawler = null;

    public function setUp()
    {
        $this->_client = static::createClient();
        $this->_client->followRedirects(true);
    }

    /**
     * Asserts that the create user form is correctly displayed
     */
    public function assertCreateUserFormFieldsCorrectlyDisplayed()
    {
        $content = $this->_client->getResponse()->getContent();

        $this->assertStringContainsString('<form name="user" method="post" action="/users/create">', $content);
        $this->assertStringContainsString('<input type="text" id="user_username" name="user[username]" required="required" class="form-control" />', $content);
        $this->assertStringContainsString('<input type="password" id="user_password_first" name="user[password][first]" required="required" class="form-control" />', $content);
        $this->assertStringContainsString('<input type="password" id="user_password_second" name="user[password][second]" required="required" class="form-control" />', $content);
        $this->assertStringContainsString('<input type="email" id="user_email" name="user[email]" required="required" class="form-control" />', $content);
        $this->assertStringContainsString('<input type="hidden" id="user__token" name="user[_token]" value=', $content);
        $this->assertStringContainsString('<button type="submit" class="btn btn-success pull-right">Ajouter</button>', $content);
    }

    /**
     * Asserts that the client is correctly redirected after creating user
     */
    public function assertCreateUserRedirectedAfterSuccessfullSubmission()
    {
        $this->_crawler = $this->_client->submitForm('Ajouter', [
            'user[username]' => 'Beber',
            'user[password][first]' => 'azerty',
            'user[password][second]' => 'azerty',
            'user[email]' => 'beber@gmail.com'
        ]);

        //Asserts alert success message
        $alert = $this->_crawler->filter('div.alert-success > strong')->text();
        $this->assertSame('Superbe !', $alert);
    }

    public function assertUsersList()
    {
        //Gets the number of users in database
        $container = self::$container;
        $manager = $container->get('doctrine.orm.default_entity_manager');
        $userRepo = $container->get('doctrine.orm.container_repository_factory')->getRepository($manager, 'App:User');
        $usersInDb = count($userRepo->findAll());

        //Counts the number of users displayed in the list
        $usersInList = count($this->_crawler->filter('tbody > tr'));

        $this->assertEquals($usersInDb, $usersInList);
    }

    /**
     * Tests that the client is correctly redirected after creating user
     */
    public function testCreateUserWithFormValid()
    {
        //1. Client arrives on the landing page
        $this->crawler = $this->_client->request('GET', '/');

        //2. Clicks on the link to create a user
        $this->_client->clickLink('Créer un utilisateur');

        //3. Asserts form is correctly displayed
        $this->assertCreateUserFormFieldsCorrectlyDisplayed();

        //4. Asserts that once the form is submitted, the client is redirected and alerted
        $this->assertCreateUserRedirectedAfterSuccessfullSubmission();

        //5. Asserts the list of existing users is correctly displayed
        $this->assertUsersList();
    }

    /**
     * Provides a set of form field values for create user form
     */
    public function formFieldProvider()
    {
        return [
                [
                    //Password fields are not identical
                    'formValues' => [
                                    'username' => 'Arthur',
                                    'firstPassword' => 'ytreza',
                                    'secondPassword' => 'azerty',
                                    'email' => 'arthur@gmail.com'
                                    ],
                    'expected' => 'Les deux mots de passe doivent correspondre.'
                ],
                [
                    //Username is empty
                    'formValues' => [
                                    'username' => '',
                                    'firstPassword' => 'azerty',
                                    'secondPassword' => 'azerty',
                                    'email' => 'alias@gmail.com'
                                    ],
                    'expected' => 'Vous devez saisir un nom d\'utilisateur.'
                ],
                [
                    //Password is empty
                    'formValues' => [
                                    'username' => '',
                                    'firstPassword' => 'a',
                                    'secondPassword' => 'a',
                                    'email' => 'junior@gmail.com'
                                    ],
                    'expected' => 'Vous devez saisir un mot de passe.'
                ],
                [
                    //Email is empty
                    'formValues' => [
                                    'username' => 'Robert',
                                    'firstPassword' => 'azerty',
                                    'secondPassword' => 'azerty',
                                    'email' => ''
                                    ],
                    'expected' => 'Vous devez saisir une adresse email.'
                ]
        ];
    }

    /**
     * Tests that no user is registered and help messages are displayed when form is not valid
     * @dataProvider formFieldProvider
     */
    public function testCreateUserWithFormNonValid(array $formValues, string $expected)
    {
        $this->_client->request('GET', '/users/create');

        $this->_crawler = $this->_client->submitForm('Ajouter', [
                'user[username]' => $formValues['username'],
                'user[password][first]' => $formValues['firstPassword'],
                'user[password][second]' => $formValues['secondPassword'],
                'user[email]' => $formValues['email']
            ]);

        $alert = $this->_crawler->filter('div.has-error > .help-block li')->text();

        $this->assertSame($expected, $alert);
    }

    public function testEditUser()
    {
    }
}
