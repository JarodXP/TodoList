<?php

declare(strict_types = 1);

namespace App\Tests\EventListener;

use App\Entity\User;
use App\EventListener\PrePersistPasswordHasher;
use App\EventListener\PreUpdatePasswordHasher;
use App\EventListener\UserPasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordHasherTest extends KernelTestCase
{
    public function testPrePersistPasswordEncoding()
    {
        // Sets a plain password on a new user
        $user = new User();
        $user->setUsername('test')
            ->setEmail('test@test.com')
            ->setPlainPassword('azerty');

        //Gets the password encoder from the container
        /**@var UserPasswordEncoderInterface $encoder */
        static::bootKernel();
        $encoder = static::$container->get('security.user_password_encoder.generic');

        $listener = new PrePersistPasswordHasher($encoder);

        //Uses LifeCycleEventArgs as stub for the listener.
        $lyfeCicleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lyfeCicleEventArgs->method('getObject')
            ->willReturn($user);

        $listener->prePersist($user, $lyfeCicleEventArgs);

        $this->assertTrue($encoder->isPasswordValid($user, 'azerty'));
    }

    public function testPreUpdatePasswordEncoding()
    {
        static::bootKernel();

        //Gets the password encoder and the EntityManager from the container

        /**@var UserPasswordEncoderInterface $encoder */
        $encoder = static::$container->get('security.user_password_encoder.generic');
        
        /**@var EntityManagerInterface $entityManager */
        $entityManager = static::$container->get('doctrine.orm.default_entity_manager');

        //Gets an existing user to set a new password
        $user = $entityManager->getRepository('App:User')->findOneBy(['email' => 'unique@unique.com']);
        $user->setPlainPassword('ytreza');

        $listener = new PreUpdatePasswordHasher($encoder);

        //Uses LifeCycleEventArgs as stub for the listener.
        $lyfeCicleEventArgs = $this->createMock(LifecycleEventArgs::class);
        $lyfeCicleEventArgs->method('getObject')
            ->willReturn($user);
        $lyfeCicleEventArgs->method('getObjectManager')
            ->willReturn($entityManager);

        $listener->preUpdate($user, $lyfeCicleEventArgs);

        $this->assertTrue($encoder->isPasswordValid($user, 'ytreza'));
    }
}
