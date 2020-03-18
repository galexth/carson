<?php

use App\Models\User;

class CreditControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test()
    {
        $user = factory(User::class)->create();

        $admin = factory(User::class)->state('admin')->create();

        /**
         * @see \App\Http\Controllers\UserController::approve()
         */
        $response = $this->put("/admin/users/{$user->id}/approve", [], [
            'Authorization' => $this->getAuthorizationHeader($admin)
        ]);
        $response->seeJsonContains([
            'email' => $user->email,
            'status' => User::STATUS_APPROVED,
        ]);

        $user->refresh();

        $this->assertNotEmpty($user->stripe_id);

        /**
         * @see \App\Http\Controllers\CreditController::buy()
         */
        $response = $this->post('/credits/buy', [
            'credits' => 10
        ], [
            'Authorization' => $this->getAuthorizationHeader($user)
        ]);
        $response->seeJsonContains([
            'credits' => 10,
        ]);

        /**
         * @see \App\Http\Controllers\CreditController::index()
         */
        $response = $this->get('/credits/history', [
            'Authorization' => $this->getAuthorizationHeader($user)
        ]);
        $response->seeJsonStructure([
            'data',
        ]);
    }

}
