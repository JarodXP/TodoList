<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testPasswordEncodeEventIsDispatchedWhileSettingPassword()
    {
        $user = new User();
        
        $user->setUsername('test')
            ->setEmail('test@test.com')
            ->setPassword('azerty');

        $this->assertSame();
    }
}
