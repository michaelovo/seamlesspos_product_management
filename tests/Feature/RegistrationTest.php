<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    //use RefreshDatabase;
    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter RegistrationTest::test_signup_with_valid_payload
     */

    public function test_signup_with_valid_payload(): void
    {
        $userPayload = [
            "first_name" => "Joshua",
            "last_name" => "Jerry",
            "email" => "emikeovo@yopmail.com",
            "phone" => "08032089624",
            "password" => "P@assword1",
            "confirmPassword" => "P@assword1",
        ];
        $this->POST('api/v1/register', $userPayload)
            ->assertStatus(200);
    }

    /**
     * to test this function, run the command below in your terminal
     * php artisan test --filter RegistrationTest::test_signup_with_unmatched_password_and_confirmPassword
     */
    public function test_signup_with_unmatched_password_and_confirmPassword(): void
    {
        $userPayload = [
            "first_name" => "Joshua",
            "last_name" => "Jerry",
            "email" => "emikeovo@yopmail.com",
            "phone" => "08032089624",
            "password" => "P@assword1",
            "confirmPassword" => "P@assword145",
        ];
        $this->POST('api/v1/register', $userPayload)
            ->assertStatus(400);

    }
}
