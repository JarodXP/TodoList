<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouteAccessTest extends WebTestCase
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
     * @dataProvider routeProvider
     * Tests routes Response status for non-authenticated user.
     */
    public function testNonAuthenticatedUserResponseStatus(string $route, string $message, string $tag)
    {
        //Gets a task id to be used in the routes assertions
        $taskId = $this->getEntityRepo('App:Task')->findAll()[0]->getId();

        $expectedStatus = [
            'home' => 302,
            'login' => 200,
            'logout' => 302,
            'userList' => 302,
            'userCreate' => 302,
            'userEdit' => 302,
            'taskList' => 302,
            'taskEdit' => 302,
            'taskCreate' => 302,
            'taskToggle' => 302,
            'taskDelete' => 302
        ];

        $this->assertStatusCodes($route, 'Failure for NON_AUTHENTICATED user: '.$message, $tag, $expectedStatus, $taskId);

        //Asserts redirection except for login
        if ($tag !== 'login') {
            if ($tag !== 'logout') {
                $this->assertsRedirection('login');
            } else {
                $this->assertsRedirection('homepage');
            }
        }
    }

    /**
     * @dataProvider routeProvider
     * Tests routes Response status for non-authenticated user.
     */
    public function testRoleUserResponseStatusForOwnedTasks(string $route, string $message, string $tag)
    {
        $this->client->followRedirects(false);

        //Logs in user "unique" with ROLE_USER
        $this->authenticateClient();
        
        //Gets the session user and a task he owns
        /**@var User $currentUser */
        $currentUser = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com']);
        $ownedTaskId = $this->getEntityRepo('App:Task')->findOneBy(['user' => $currentUser])->getId();

        $expectedStatus = [
            'home' => 200,
            'login' => 200,
            'logout' => 302,
            'userList' => 403,
            'userCreate' => 403,
            'userEdit' => 403,
            'taskList' => 200,
            'taskEdit' => 200,
            'taskCreate' => 200,
            'taskToggle' => 302,
            'taskDelete' => 302
        ];

        try {
            $this->assertStatusCodes($route, 'Failure for ROLE_USER: '.$message, $tag, $expectedStatus, $ownedTaskId);
        } catch (AccessDeniedHttpException $e) {
        }

        //Asserts redirection url for 302 codes
        if ($tag == 'task-toggle' || $tag == 'task-delete') {
            $this->assertsRedirection('/tasks');
        } elseif ($tag == 'logout') {
            $this->assertsRedirection('homepage');
        }
    }

    /**
     * @dataProvider nonOwnedTasksRouteProvider
     * Tests routes Response status for non-authenticated user.
     */
    public function testRoleUserResponseStatusForNonOwnedTasks(string $route, string $message, string $tag)
    {
        $this->client->followRedirects(false);

        //Logs in user "unique" with ROLE_USER
        $this->authenticateClient();
        
        //Gets the session user and a task he owns
        /**@var User $otherUser */
        $otherUser = $this->getEntityRepo('App:User')->findOneBy(['email' => 'x@anonymous.com']);
        $nonOwnedTaskId = $this->getEntityRepo('App:Task')->findOneBy(['user' => $otherUser])->getId();

        $expectedStatus = [
            'nonOwnedTaskEdit' => 403,
            'nonOwnedTaskToggle' => 403,
            'nonOwnedTaskDelete' => 403
        ];

        try {
            $this->assertStatusCodes($route, 'Failure for ROLE_USER: '.$message, $tag, $expectedStatus, $nonOwnedTaskId);
        } catch (AccessDeniedHttpException $e) {
        }
    }

    /**
     * @dataProvider routeProvider
     * Tests routes Response status for ROLE_ADMIN user.
     */
    public function testRoleAdminResponseStatusForOwnedTasks(string $route, string $message, string $tag)
    {
        $this->client->followRedirects(false);

        //Logs in user "admin" with ROLE_ADMIN
        $this->authenticateClient('admin@admin.com');
        
        //Gets the session user and a task he owns
        /**@var User $currentUser */
        $currentUser = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com']);
        $ownedTaskId = $this->getEntityRepo('App:Task')->findOneBy(['user' => $currentUser])->getId();

        $expectedStatus = [
            'home' => 200,
            'login' => 200,
            'logout' => 302,
            'userList' => 200,
            'userCreate' => 200,
            'userEdit' => 200,
            'taskList' => 200,
            'taskEdit' => 200,
            'taskCreate' => 200,
            'taskToggle' => 302,
            'taskDelete' => 302
        ];

        $this->assertStatusCodes($route, 'Failure for ROLE_ADMIN: '.$message, $tag, $expectedStatus, $ownedTaskId);
        
        //Asserts redirection url for 302 codes
        if ($tag == 'task-toggle' || $tag == 'task-delete') {
            $this->assertsRedirection('/tasks');
        }
    }

    /**
     * @dataProvider nonOwnedTasksRouteProvider
     * Tests routes Response status for non-authenticated user.
     */
    public function testRoleAdminResponseStatusForNonOwnedTasks(string $route, string $message, string $tag)
    {
        $this->client->followRedirects(false);

        //Logs in user "admin" with ROLE_ADMIN
        $this->authenticateClient('admin@admin.com');
        
        //Gets the session user and a task he owns
        /**@var User $otherUser */
        $otherUser = $this->getEntityRepo('App:User')->findOneBy(['email' => 'unique@unique.com']);
        $nonOwnedTaskId = $this->getEntityRepo('App:Task')->findOneBy(['user' => $otherUser])->getId();

        $expectedStatus = [
            'nonOwnedTaskEdit' => 200,
            'nonOwnedTaskToggle' => 302,
            'nonOwnedTaskDelete' => 302
        ];

        $this->assertStatusCodes($route, 'Failure for ROLE_ADMIN: '.$message, $tag, $expectedStatus, $nonOwnedTaskId);
        
        //Asserts redirection url for 302 codes
        if ($tag == 'non-owned-task-toggle' || $tag == 'non-owned-task-delete') {
            $this->assertsRedirection('/tasks');
        }
    }

    /**
     * Tests the route redirects to login page if user is not authenticated
     */
    public function assertsRedirection(string $locationRoute)
    {
        if ($locationRoute == 'login' || $locationRoute == 'homepage') {

            //Uses the url generator as Guard Authenticator sets the location header with absolute path.
            $this->urlGenerator = static::$container->get('router');

            //Builds the absolute URL to compare with the location header
            $locationRoute = $this->urlGenerator->generate($locationRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->assertResponseRedirects($locationRoute);
    }

    /**
     * Provides a list of routes to test authorizations
     */
    public function routeProvider()
    {
        return [
                [
                    'route' => '/',
                    'message' => 'on homepage',
                    'tag' => 'home'
                ],
                [
                    'route' => '/login',
                    'message' => 'on login',
                    'tag' => 'login'
                ],
                [
                    'route' => '/logout',
                    'message' => 'on logout',
                    'tag' => 'logout'
                ],
                [
                    'route' => '/users',
                    'message' => 'on users list',
                    'tag' => 'user-list'
                ],
                [
                    'route' => '/users/create',
                    'message' => 'on user create',
                    'tag' => 'user-create'
                ],
                [
                    'route' => '/users/userId/edit',
                    'message' => 'on user edit',
                    'tag' => 'user-edit'
                ],
                [
                    'route' => '/tasks',
                    'message' => 'on task list',
                    'tag' => 'task-list'
                ],
                [
                    'route' => '/tasks/create',
                    'message' => 'on task create',
                    'tag' => 'task-create'
                ],
                [
                    'route' => '/tasks/taskId/edit',
                    'message' => 'on task edit',
                    'tag' => 'task-edit'
                ],
                [
                    'route' => '/tasks/taskId/toggle',
                    'message' => 'on task toggle',
                    'tag' => 'task-toggle'
                ],
                [
                    'route' => '/tasks/taskId/delete',
                    'message' => 'on task delete',
                    'tag' => 'task-delete'
                ]
            ];
    }

    public function nonOwnedTasksRouteProvider()
    {
        return [
                [
                    'route' => '/tasks/taskId/edit',
                    'message' => 'on non-owned task edit',
                    'tag' => 'non-owned-task-edit'
                ],
                [
                    'route' => '/tasks/taskId/toggle',
                    'message' => 'on non-owned task toggle',
                    'tag' => 'non-owned-task-toggle'
                ],
                [
                    'route' => '/tasks/taskId/delete',
                    'message' => 'on non-owned task delete',
                    'tag' => 'non-owned-task-delete'
                ]
            ];
    }

    /**
     * Handles the different cases for asserting routes Response status codes
     */
    protected function assertStatusCodes(string $route, string $message, string $tag, array $expectedStatus, int $taskId)
    {
        $this->client->followRedirects(false);
        
        //Gets a user id different from the user session to test the edit action route
        $userId = $this->getEntityRepo('App:User')->findAll()[0]->getId();

        //Replaces the userId and taskId parameters in the route
        $route = str_replace('userId', $userId, $route);
        $route = str_replace('taskId', $taskId, $route);


        //Sets the expected status codes
        switch ($tag) {
            case 'home': $assertionCode = $expectedStatus['home'];
            break;

            case 'login': $assertionCode = $expectedStatus['login'];
            break;

            case 'logout': $assertionCode = $expectedStatus['logout'];
            break;

            case 'user-list': $assertionCode = $expectedStatus['userList'];
            break;

            case 'user-edit': $assertionCode = $expectedStatus['userEdit'];
            break;

            case 'user-create': $assertionCode = $expectedStatus['userCreate'];
            break;

            case 'task-list': $assertionCode = $expectedStatus['taskList'];
            break;

            case 'task-create': $assertionCode = $expectedStatus['taskCreate'];
            break;

            case 'task-edit': $assertionCode = $expectedStatus['taskEdit'];
            break;

            case 'task-toggle': $assertionCode = $expectedStatus['taskToggle'];
            break;

            case 'task-delete':  $assertionCode = $expectedStatus['taskDelete'];
            break;

            case 'non-owned-task-edit': $assertionCode = $expectedStatus['nonOwnedTaskEdit'];
            break;

            case 'non-owned-task-toggle': $assertionCode = $expectedStatus['nonOwnedTaskToggle'];
            break;

            case 'non-owned-task-delete':  $assertionCode = $expectedStatus['nonOwnedTaskDelete'];
            break;

            default: $assertionCode = 500;
            break;
        }

        $this->client->request('GET', $route);
        
        $this->assertResponseStatusCodeSame($assertionCode, $message);
    }
}
