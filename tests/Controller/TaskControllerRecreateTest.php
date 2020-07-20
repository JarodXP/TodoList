<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This test class is related to the same than UserControllerTest,
 * but uses a different database connection trait to avoid database
 * conflict in the same transaction.
 */
class TaskControllerRecreateTest extends WebTestCase
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
    public function testEditTaskSubmitForm()
    {
        $this->authenticateClient();
        $this->client->followRedirects(false);
        
        /**@var App/Entity/Task $task*/
        $task = $this->getEntityRepo('App:Task')->findOneBy(['title' => 'Task 1']);
        $this->client->request('GET', '/tasks/'.$task->getId().'/edit');

        $formValues = [
            'title' => $task->getTitle(),
            'content' => 'Il faut décrire la tache!'
        ];

        $this->submitTaskForm('Modifier', $formValues);
        
        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirects();
    }

    /**
     * Test toggleAction
     * tests if success message is correctly displayed after task toggle
     */
    public function testToggleSuccessMessage()
    {
        $this->assertTaskActionMessageAndRedirection('toggle');
    }

    public function testDeleteSuccessMessage()
    {
        $this->assertTaskActionMessageAndRedirection('delete');
    }

    public function testAuthenticatedUserIsBoundToCreatedTask()
    {
        $this->authenticateClient();
        
        // Client arrives on the create task page
        $this->crawler = $this->client->request('GET', '/tasks/create');

        //Submit the form with provider data
        $this->submitTaskForm('Ajouter');

        /**@var App/Entity/Task $newTask */
        $newTask = $this->getEntityRepo('App:Task')->findOneBy(['title' => 'Une grosse tache']);
        $authenticateduser = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com']);

        $this->assertSame($authenticateduser, $newTask->getUser());
    }

    
    /**
     * Sets a default value for create user form and submits
     */
    protected function submitTaskForm(string $btnText, array $formValues = null)
    {
        if (is_null($formValues)) {
            $formValues = [
                'title' => 'Une grosse tache',
                'content' => 'RAS',
            ];
        }

        $this->crawler = $this->client->submitForm($btnText, [
                'task[title]' => $formValues['title'],
                'task[content]' => $formValues['content']
            ]);
    }

    /**
     * Asserts redirection and success message
     */
    protected function assertTaskActionMessageAndRedirection(string $action)
    {
        $this->client->followRedirects(false);
        
        $this->authenticateClient();

        //Gets Task 1
        $task = $this->getEntityRepo('App:Task')->findOneBy(['title' => 'Task 1']);

        //Request the toggle
        $this->client->request('POST', '/tasks/'.$task->getId().'/'.$action);

        //Asserts redirection
        $this->assertResponseRedirects('/tasks');

        $this->client->followRedirect();

        //Asserts that alert success message is displayed
        $this->assertSelectorExists('div.alert-success');
    }
}
