<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Log;

class RegisterTest extends TestCase
{
    //   public function setUp() {
    //    parent::setUp();
    // Your code here
    //}

    /**
     * Test registration
     * @group 0
     * @return void
     */
    public function testRegister()
    {
        $faker = \Faker\Factory::create();
        //        $this->withoutExceptionHandling();

        $email = $faker->email;
        $pass = "Testpass12!";

        $response = $this->postJson('api/auth/register', ['email' => $email, 'password' => $pass]);
        $response->assertStatus(201)->assertJson([
            'user' => true,
        ]);


        //
        $error_response = $this->postJson('api/auth/login', [
            'email' => 'researcher@*TEST*mysurveys.santafe.edu',
            'password' => 'Testpass12!2'
        ]);

        $error_response
            ->assertStatus(401);
    }

    /**
     * Test registration with qualification form
     * @group 0
     * @return void
     */
    public function testRegisterWithQualificationForm()
    {
        $faker = \Faker\Factory::create();

        $email = $faker->email;
        $pass = "Testpass12!";

        $form = [
            "parents" => "true",
            "gm" => "2",
            "vac" => "3",
            "us" => "true",
            "friends" => "true",
            "seed" => true
        ];

        $response = $this->postJson('api/auth/register', [
            'email' => $email, 'password' => $pass,
            'qualificationForm' => $form
        ]);

        $response->assertStatus(201)->assertJson([
            'user' => true,
        ]);

        $response2 = $this->postJson('api/auth/login', [
            'email' => $email,
            'password' => $pass,
        ]);

        $response2
            ->assertStatus(200)
            ->assertJson([
                'access_token' => true,
            ]);
    }

    /**
     * Test login
     * @group 9
     * @return void
     */
    public function testLogin()
    {
        $response2 = $this->postJson('api/auth/login', [
            'email' => $email,
            'password' => $pass,
        ]);

        $response2
            ->assertStatus(200)
            ->assertJson([
                'access_token' => true,
            ]);
    }
}
