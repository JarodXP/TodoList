<?php

declare(strict_types = 1);

namespace App\Tests\EventListener;

use App\Entity\Task;
use App\Tests\Controller\ControllerUtilsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AnonymousUserListenerTest extends KernelTestCase
{
    use ControllerUtilsTrait;
    use RecreateDatabaseTrait;

    protected EntityManagerInterface $em;

    public function setUp():void
    {
        static::bootKernel();

        /** @var EntityManagerInterface $this->em */
        $this->em = static::$container->get('doctrine.orm.default_entity_manager');

        //Checks if the anonymous user exists and possibly removes it to setup the test
        $anonymous = $this->getEntityRepo('App:User')->findOneBy(['email' => 'anonymous@anonymous.com']);
        
        if (!is_null($anonymous)) {
            $this->em->remove($anonymous);
            $this->em->flush();
        }
    }
    
    public function testAnonymousUserIsCreatedOnTaskCreate()
    {
        //Asserts the anonymous user doesn't exists before testing its creation
        $this->assertNull($this->getEntityRepo('App:User')->findOneBy(['email' => 'anonymous@anonymous.com']));

        //Creates a new task that should lead to the anonymous user creation on persist
        /** @var Task $testTask */
        $testTask = new Task();
        $testTask->setTitle('Test');
        $testTask->setContent('TestContent');

        $this->em->persist($testTask);
        
        $this->em->flush();

        $this->assertAnonymousBinding($testTask);
    }

    public function testAnonymousUserIsCreatedOnTaskUpdate()
    {
        //Asserts the anonymous user doesn't exists before testing its creation
        $this->assertNull($this->getEntityRepo('App:User')->findOneBy(['email' => 'anonymous@anonymous.com']));

        //Updating an existing task without user should lead to to the anonymous user creation on update
        /** @var Task $testTask */
        $testTask = $this->getEntityRepo('App:Task')->findOneBy(['title' => 'Task X']);
        $testTask->setUser(null);

        $this->em->flush();

        $this->assertAnonymousBinding($testTask);
    }

    private function assertAnonymousBinding(Task $testTask)
    {
        //Asserts Anonymous user has been created
        $anonymous = $this->getEntityRepo('App:User')->findOneBy(['email' => 'anonymous@anonymous.com']);
        $this->assertNotNull($anonymous);

        //Asserts Anonymous user has been set as Task's user
        $this->assertSame($anonymous, $testTask->getUser());
    }
}
