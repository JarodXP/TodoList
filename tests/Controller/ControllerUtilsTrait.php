<?php

declare(strict_types = 1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

trait ControllerUtilsTrait
{
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * Initiates the kernel and gets the user repository
     */
    protected static function getEntityRepo(string $entityClassName):EntityRepository
    {
        //static::bootKernel();
        $manager = static::$container->get('doctrine.orm.default_entity_manager');
        return static::$container->get('doctrine.orm.container_repository_factory')->getRepository($manager, $entityClassName);
    }
    
    /**
     * Simulates an authenticated user
     */
    protected function authenticateClient(string $email = 'unique@unique.com')
    {
        //Gets the repository and the authenticated user.
        //The RepoGetter method can't be used here as the $container property of the TestCase class
        //is not recognized and leads to an exception with the loginUser() method.
        $manager = static::$container->get('doctrine.orm.default_entity_manager');
        $userRepository = static::$container->get('doctrine.orm.container_repository_factory')->getRepository($manager, 'App:User');

        $authenticatedUser = $userRepository->findOneBy(['email' => $email]);
        
        $this->client->loginUser($authenticatedUser);
    }

    /**
     * Possibly sets a default value for user form and submits
     */
    protected function submitUserForm(string $btnText, array $formValues = null)
    {
        //Sets default values
        if (is_null($formValues)) {
            $formValues = [
                'username' => 'Beber',
                'firstPassword' => 'azerty',
                'secondPassword' => 'azerty',
                'email' => 'beber@gmail.com',
                'roles' => ['ROLE_USER']
            ];
        }

        //Sets the form roles choice type options
        array_search('ROLE_USER', $formValues['roles']) !== false ? $formValues['roleUser'] = true : $formValues['roleUser'] = false;
        array_search('ROLE_ADMIN', $formValues['roles']) !== false ? $formValues['roleAdmin'] = true : $formValues['roleAdmin'] = false;


        //Submits the form by setting the form fields values
        $this->crawler = $this->client->submitForm($btnText, [
                'user[username]' => $formValues['username'],
                'user[plainPassword][first]' => $formValues['firstPassword'],
                'user[plainPassword][second]' => $formValues['secondPassword'],
                'user[email]' => $formValues['email'],
                'user[roles][0]' => $formValues['roleAdmin'],
                'user[roles][1]' => $formValues['roleUser']
            ]);
    }
}
