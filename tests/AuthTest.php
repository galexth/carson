<?php

use App\Models\User;

class AuthTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test()
    {
        $userData = factory(User::class)->make()->toArray();
        $userData['password'] = $userData['password_confirmation'] = 'qweqweqwe';

        /**
         * @see \App\Http\Controllers\RegisterController::signUp()
         */
        $response = $this->post('/auth/sign-up', $userData);

        $response->assertResponseStatus(201);

        $user = User::where('email', $userData['email'])->first();

        $this->assertNotEmpty($user);

        /**
         * @see \App\Http\Controllers\LoginController::signIn()
         */
        $response = $this->post('/auth/sign-in', [
            'email' => $user->email,
            'password' => $userData['password'],
        ]);

        $response->assertResponseOk();

        $response->seeHeader('Authorization');
    }

}
