<?php

use App\Models\User;

class TaskControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test()
    {
        $user = factory(User::class)->state('approved')->create();

        $task = factory(\App\Models\Task::class)->make();

        $response = $this->post('/tasks', $task->toArray(), [
            'Authorization' => $this->getAuthorizationHeader($user)
        ]);
        $response->seeJsonContains([
            'title' => $task->title,
        ]);

        $user->refresh();
        $this->assertEquals(9, $user->credits);

        $response = $this->get('/tasks', [
            'Authorization' => $this->getAuthorizationHeader($user)
        ]);
        $response->seeJsonStructure([
            '*' => ['title'],
        ]);
    }

}
