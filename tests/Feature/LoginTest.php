<?php

namespace Tests\Feature;

use App\Models\User;
use App\Traits\Helpers;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use Helpers;

    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter LoginTest::test_verified_user_account_login_with_the_valid_email_and_password
     */

    public function test_verified_user_account_login_with_the_valid_email_and_password()
    {
        $user = User::where('email', 'emikeovo@yopmail.com')->first();

        $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'P@assword1',
            'status_id' => LoginTest::getStatusId('Active'),
        ])->assertStatus(200);
    }

    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter LoginTest::test_unverified_user_account_login_with_invalid_password
     */

    public function test_unverified_user_account_login_with_invalid_password()
    {
        $user = User::where('email', 'emikeovo@yopmail.com')->first();

        $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'Password134',
            'status_id' => LoginTest::getStatusId('Active'),
        ])->assertStatus(400);

    }
}
