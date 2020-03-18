<?php

use App\Models\User;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
    }

    /**
     * @param \App\Models\User $user
     *
     * @return string
     */
    protected function getAuthorizationHeader(User $user)
    {
        return 'Bearer '. Auth::guard()->fromUser($user);
    }
}
