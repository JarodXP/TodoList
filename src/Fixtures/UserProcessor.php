<?php

declare(strict_types = 1);

namespace App\Fixtures;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserProcessor implements ProcessorInterface
{
    private $_passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->_passwordEncoder = $passwordEncoder;
    }
        
    /**
     * preProcess
     *
     * @param  mixed $fixtureId
     * @param  mixed $user
     * @return void
     */
    public function preProcess(string $fixtureId, $user): void
    {
        if (false === $user instanceof User) {
            return;
        }

        $user->setPassword($this->_passwordEncoder->encodePassword($user, $user->getPassword()));
    }
    
    /**
     * postProcess
     *
     * @param  mixed $id
     * @param  mixed $user
     * @return void
     */
    public function postProcess(string $id, $user): void
    {
    }
}
