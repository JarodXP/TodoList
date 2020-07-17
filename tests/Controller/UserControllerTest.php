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
    public function testListActionReturn200InSuccess()
    {
        $this->client->request('GET', '/users');

        $this->assertResponseStatusCodeSame(200);
    }

    public function testUsersActionRedirectsToLoginWhenUserIsNotAuthenticated()
    {
        $this->assertsRedirectionToLoginWhenUserIsNotAuthenticated('/users');
    }

    /**
     * Test listAction
     */
    public function testCorrectNbOfUsersInList()
    {
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
     * Test createAction
     * Tests that no user is created and error message displayed when using existing email
     */
    public function testSubmitCreateUserFormWithExistingEmail()
    {
        // Client arrives on the create user page
        $this->client->request('GET', '/users/create');

        $formValues = [
                'username' => 'Reuno',
                'firstPassword' => 'azerty',
                'secondPassword' => 'azerty',
                'email' => 'unique@unique.com'
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
     * Tests that no user is registered and help messages are displayed when form is not valid
     * @dataProvider nonValidFormFieldProvider
     */
    public function testSubmitUserControllerNonValidForm(array $formValues, string $expected)
    {
        $this->client->request('GET', '/users/create');

        // Temporay: while password validation non available
        // When available, remove the following lines and uncomment provider's empty password
        if ($formValues['firstPassword'] == '') {// Temp
            $formValues['firstPassword'] = 'azerty';// Temp

            $passwordValidation = false;// Temp
        
            $this->assertTrue($passwordValidation, 'Test to be removed when constraint added on null password');// Temp
        }

        //Submit the form with provider data
        $this->submitUserForm('Ajouter', $formValues);

        //Asserts no user has been created
        $this->assertEquals(0, count($this->getEntityRepo('App:User')->findBy(['email' => $formValues['email']])));

        //Asserts error message
        $alert = $this->crawler->filter('div.has-error > .help-block li')->text();
        $this->assertSame($expected, $alert);
    }

    /**
     * Test code return for editAction
     */
    public function testEditActionReturn200InSuccess()
    {
        $userId = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com'])->getId();

        $this->client->request('GET', '/users/'.$userId.'/edit');
        
        $this->assertResponseStatusCodeSame(200);
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
                                    'username' => 'junior',
                                    'firstPassword' => '',
                                    'secondPassword' => '',
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
