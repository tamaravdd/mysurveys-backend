<?php

namespace Tests\Feature;

use Tests\TestCase;

//use Database
//
//use DatabaseMigrations { runDatabaseMigrations as runMigration; }


class InviteResearcherTest extends TestCase
{



    /**
     * A basic feature test example.
     * @group 1
     * @return void
     */
    public function testInviteResearcher()
    {

        $this->faker = \Faker\Factory::create();

        $admin = \App\User::find(1);
        $this->actingAs($admin, 'api');
        //fake researcher info
        $name = $this->faker->firstName;
        $email = $this->faker->email;
        $response = $this->postJson('api/invite_researcher', [
            'nickname' => $name,
            'email' => $email
        ]);
        $response
            ->assertStatus(201)
            ->assertJson([
                'user' => true,
            ]);

        $researcher = \App\User::where("email", $email)->first();
        //R verification code
        $r_code = $researcher->verification_code;

        //        $this->actingAs(\Illuminate\Support\Facades\Auth::Guest());
        $response2 = $this->postJson('api/auth/check_verification_code', [
            'code' => $r_code
        ]);

        $response2
            ->assertStatus(200)
            ->assertJson([
                'user' => true,
            ]);
    }
}
