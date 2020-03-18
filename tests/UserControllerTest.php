<?php

use App\Models\User;

class UserControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAdmin()
    {
        $user = factory(User::class)->create();

        $admin = factory(User::class)->state('admin')->create();

        $response = $this->get("/admin/users/{$user->id}", [
            'Authorization' => $this->getAuthorizationHeader($admin)
        ]);
        $response->seeJsonContains(['email' => $user->email]);

        $response = $this->put("/admin/users/{$user->id}/approve", [], [
            'Authorization' => $this->getAuthorizationHeader($admin)
        ]);
        $response->seeJsonContains([
            'email' => $user->email,
            'status' => User::STATUS_APPROVED,
        ]);

        $user->refresh();

        $this->assertNotEmpty($user->stripe_id);

        $response = $this->put("/admin/users/{$user->id}/ban", [], [
            'Authorization' => $this->getAuthorizationHeader($admin)
        ]);
        $response->seeJsonContains([
            'email' => $user->email,
            'status' => User::STATUS_BANNED,
        ]);


        factory(User::class, 5)->create();

        $response = $this->get('/admin/users/pending', [
            'Authorization' => $this->getAuthorizationHeader($admin)
        ]);
        $response->seeJsonStructure([
            '*' => [
                'email', 'id'
            ]
        ]);
    }

}
