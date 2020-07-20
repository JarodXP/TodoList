<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TaskControllerTest extends WebTestCase
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

    public function testTasksActionsRedirectsToLoginWhenUserIsNotAuthenticated()
    {
        $this->assertsRedirectionToLoginWhenUserIsNotAuthenticated('/tasks');
    }

    /**
     * Test listAction
     */
    public function testListActionReturn200WhenUserIsAuthenticated()
    {
        $this->authenticateClient();

        $this->client->request('GET', '/tasks');

        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Test listAction
     */
    public function testCorrectNbOfTasksInList()
    {
        $this->authenticateClient();
        
        $this->crawler = $this->client->request('GET', '/tasks');

        $repo = $this->getEntityRepo('App:Task');

        //Gets the number of tasks in database
        $tasksInDb = count($this->getEntityRepo('App:Task')->findAll());

        //Counts the number of users displayed in the list
        $tasksInList = count($this->crawler->filter('div.thumbnail'));

        $this->assertEquals($tasksInDb, $tasksInList);
    }

    /**
     * Test createAction
     * Tests that the client is correctly redirected after creating task and that a task has been created
     */
    public function testSubmitTaskCreationValidForm()
    {
        $this->authenticateClient();

        $this->client->followRedirects(false);
        
        // Client arrives on the create task page
        $this->crawler = $this->client->request('GET', '/tasks/create');

        //Submits the form
        $this->submitTaskForm('Ajouter');

        //Asserts redirection
        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();

        //Asserts that alert success message is displayed
        $this->assertSelectorExists('div.alert-success');

        //Asserts a user has been created
        $this->assertEquals(1, count($this->getEntityRepo('App:Task')->findBy(['title' => 'Une grosse tache'])));
    }


    /**
     * Tests that no user is registered and help messages are displayed when form is not valid
     * @dataProvider nonValidFormFieldProvider
     */
    public function testSubmitTaskCreationNonValidForm(array $formValues, string $expected, string $message)
    {
        $this->authenticateClient();
        
        $this->client->request('GET', '/tasks/create');
        
        //Submit the form with provider data
        $this->submitTaskForm('Ajouter', $formValues);

        //Asserts no task has been created
        $this->assertEquals(0, count($this->getEntityRepo('App:Task')->findBy(['title' => $formValues['title']])), $message);

        //Asserts error message
        $alert = $this->crawler->filter('div.has-error > .help-block li')->text();
        $this->assertSame($expected, $alert);
    }

    /**
     * Test code return for editAction
     */
    public function testEditActionReturn200InSuccess()
    {
        $this->authenticateClient();

        $taskId = $this->getEntityRepo('App:Task')->findOneBy(['title' => 'Task 1'])->getId();

        $this->client->request('GET', '/tasks/'.$taskId.'/edit');
        
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * Sets a default value for create task form and submits
     */
    protected function submitTaskForm(string $btnText, array $formValues = null)
    {
        if (is_null($formValues)) {
            $formValues = [
                'title' => 'Une grosse tache',
                'content' => 'RAS'
            ];
        }

        $this->crawler = $this->client->submitForm($btnText, [
                'task[title]' => $formValues['title'],
                'task[content]' => $formValues['content']
            ]);
    }

    /**
     * Provides a set of form field values for create task form
     */
    public function nonValidFormFieldProvider()
    {
        return [
                [
                    //Title field is empty
                    'formValues' => [
                                    'title' => '',
                                    'content' => 'ras'
                                    ],
                    'expected' => 'Vous devez saisir un titre.',
                    'message' => 'Validation constraint missing on empty title'
                ],
                [
                    //Content is empty
                    'formValues' => [
                                    'title' => 'Task1',
                                    'content' => ''
                                    ],
                    'expected' => 'Vous devez saisir du contenu.',
                    'message' => 'Validation constraint missing on empty content'
                ],
                [
                    //Title is too long
                    'formValues' => [
                                    'title' => 'aaaaaaaaaaaaaaaaaaaaaa',
                                    'content' => 'ras'
                                    ],
                    'expected' => 'Le titre ne doit pas dépasser 20 caractères',
                    'message' => 'Validation constraint missing on title too long'
                ]
        ];
    }
}
