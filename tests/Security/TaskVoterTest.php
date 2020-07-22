<?php

declare(strict_types = 1);

namespace App\Tests\Security;

use App\Entity\Task;
use App\Entity\User;
use App\Security\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TaskVoterTest extends TestCase
{
    /**
     * @dataProvider voterParametersProvider
     */
    public function testVoteGrantsAccessToTaskAction(bool $isAdmin, User $sessionUser, User $taskOwner, int $expectedVote, string $message)
    {
        $security = $this->createMock(Security::class);

        $security->method('isGranted')
                ->willReturn($isAdmin);

        $taskVoter = new TaskVoter($security);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
                ->willReturn($sessionUser);

        $task = $this->createMock(Task::class);
        $task->method('getUser')
                ->willReturn($taskOwner);

        $this->assertEquals(1, $taskVoter->vote($token, $task, ['edit']), $message);

        //Asserts wrong attribute and subject
        if ($isAdmin) {
            $this->assertEquals(0, $taskVoter->vote($token, $taskOwner, ['edit']), "Failure on taskVoter with wrong subject");
            $this->assertEquals(0, $taskVoter->vote($token, $task, ['view']), "Failure on taskVoter with wrong attribute");
        }
    }

    /**
     * Provides several cases to test the task voter
     */
    public function voterParametersProvider()
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);
        
        return[
                [
                    'isAdmin' => false,
                    'sessionUser' => $user1,
                    'taskOwner' => $user1,
                    'expectedVote' => 1,
                    'message' => 'Failure on TaskVoter with USER_ROLE and task owned'
                ],
                [
                    'isAdmin' => false,
                    'sessionUser' => $user1,
                    'taskOwner' => $user2,
                    'expectedVote' => 0,
                    'message' => 'Failure on TaskVoter with USER_ROLE and task not owned'
                ],
                [
                    'isAdmin' => true,
                    'sessionUser' => $user1,
                    'taskOwner' => $user2,
                    'expectedVote' => 1,
                    'message' => 'Failure on TaskVoter with ADMIN_ROLE and task not owned'
                ]
            ];
    }
}
