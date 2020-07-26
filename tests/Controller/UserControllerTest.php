<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use ControllerUtilsTrait;
    
    protected ?KernelBrowser $client = null;
    protected ?Crawler $crawler = null;

    public function setUp():void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    /**
     * Test listAction
     */
    public function testCorrectNbOfUsersInList()
    {
        //Login with admin role
        $this->authenticateClient('admin@admin.com');

        $this->crawler = $this->client->request('GET', '/users');

        //Gets the number of users in database
        $usersInDb = count($this->getEntityRepo('App:User')->findAll());

        //Counts the number of users displayed in the list
        $usersInList = count($this->crawler->filter('tbody > tr'));

        $this->assertEquals($usersInDb, $usersInList);
    }

    /**
     * Test createAction
     * Tests that the client is correctly redirected after creating user and that a user has been created
     */
    public function testSubmitUserControllerValidForm()
    {
        //Login with admin role
        $this->authenticateClient('admin@admin.com');

        $this->client->followRedirects(false);
        
        // Client arrives on the create user page
        $this->crawler = $this->client->request('GET', '/users/create');

        //Submits the form
        $this->submitUserForm('Ajouter');

        //Asserts redirection
        $this->assertResponseRedirects('/users');

        $this->client->followRedirect();

        //Asserts that alert success message is displayed
        $this->assertSelectorExists('div.alert-success');

        //Asserts a user has been created
        $this->assertEquals(1, count($this->getEntityRepo('App:User')->findBy(['email' => 'beber@gmail.com'])));
    }

    /**
     * Tests that no user is registered and help messages are displayed when form is not valid
     * @dataProvider nonValidFormFieldProvider
     */
    public function testSubmitUserControllerNonValidForm(array $formValues, string $expected)
    {
        $this->authenticateClient('admin@admin.com');
        
        $this->client->request('GET', '/users/create');

        //Submit the form with provider data
        $this->submitUserForm('Ajouter', $formValues);

        //Asserts no user has been created
        $this->assertEquals(0, count($this->getEntityRepo('App:User')->findBy(['email' => $formValues['email']])));

        //Asserts error message
        $alert = $this->crawler->filter('div.has-error > .help-block li')->text();
        $this->assertSame($expected, $alert);
    }

    /**
     * Provides a set of form field values for create user form
     */
    public function nonValidFormFieldProvider()
    {
        return [
                [
                    //Password fields are not identical
                    'formValues' => [
                                    'username' => 'Arthur',
                                    'firstPassword' => 'ytreza',
                                    'secondPassword' => 'azerty',
                                    'email' => 'arthur@gmail.com',
                                    'roles' => ['ROLE_USER']
                                    ],
                    'expected' => 'Les deux mots de passe doivent correspondre.'
                ],
                [
                    //Username is empty
                    'formValues' => [
                                    'username' => '',
                                    'firstPassword' => 'azerty',
                                    'secondPassword' => 'azerty',
                                    'email' => 'alias@gmail.com',
                                    'roles' => ['ROLE_USER']
                                    ],
                    'expected' => 'Vous devez saisir un nom d\'utilisateur.'
                ],
                [
                    //Password is empty
                    'formValues' => [
                                    'username' => 'junior',
                                    'firstPassword' => '',
                                    'secondPassword' => '',
                                    'email' => 'junior@gmail.com',
                                    'roles' => ['ROLE_USER']
                                    ],
                    'expected' => 'Vous devez saisir un mot de passe.'
                ],
                [
                    //Email is empty
                    'formValues' => [
                                    'username' => 'Robert',
                                    'firstPassword' => 'azerty',
                                    'secondPassword' => 'azerty',
                                    'email' => '',
                                    'roles' => ['ROLE_USER']
                                    ],
                    'expected' => 'Vous devez saisir une adresse email.'
                ]
        ];
    }

    /**
     * Test createAction
     * Tests that no user is created and error message displayed when using existing email
     */
    public function testSubmitCreateUserFormWithExistingEmail()
    {
        $this->authenticateClient('admin@admin.com');

        // Client arrives on the create user page
        $this->client->request('GET', '/users/create');

        $formValues = [
                'username' => 'Reuno',
                'firstPassword' => 'azerty',
                'secondPassword' => 'azerty',
                'email' => 'unique@unique.com',
                'roles' => ['ROLE_USER']
            ];

        //Submits the form
        $this->submitUserForm('Ajouter', $formValues);

        //Asserts no user has been created
        $this->assertEquals(1, count($this->getEntityRepo('App:User')->findBy(['email' => 'unique@unique.com'])));

        //Asserts error message
        $alert = $this->crawler->filter('div.has-error > .help-block li')->text();
        $this->assertSame('Cette adresse mail existe déjà', $alert);
    }

    /**
     * test editAction
     * tests if choice list roles form field displays the correct role
     */
    public function testRoleChoiceListDiplaysCorrectRoleForEditUser()
    {
        //Login with admin role
        $this->authenticateClient('admin@admin.com');
                
        $user = $this->getEntityRepo('App:User')->findOneBy(['email' => 'admin@admin.com']);
        $this->crawler = $this->client->request('GET', '/users/'.$user->getId().'/edit');

        $this->assertSelectorExists('#user_roles_0[checked="checked"]');
        $this->assertSelectorNotExists('#user_roles_1[checked="checked"]');
    }

    /**
     * test createAction
     * tests if choice list roles form field is displayed for create page
     */
    public function testRoleChoiceListIsDiplayedForCreateUserWithDefaultRoleUser()
    {
        $this->authenticateClient('admin@admin.com');

        $this->crawler = $this->client->request('GET', '/users/create');

        $this->assertSelectorNotExists('#user_roles_0[checked="checked"]');
        $this->assertSelectorExists('#user_roles_1[checked="checked"]');
    }
}
