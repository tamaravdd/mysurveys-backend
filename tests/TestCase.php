<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
//use DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Generator as Faker;

abstract class TestCase extends BaseTestCase {

    use CreatesApplication;
    use DatabaseMigrations;
//        DatabaseTransactions;
//

    /**
     * Set up the test
     */
    public function setUp(): void {

//        parent::setUp();
//          $this->createApplication();
             parent::setUp();
//        \DB::beginTransaction();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');

//        \Artisan::call('migrate:fresh', ['--database' => 'sqlite_testing']);
//
//        \Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--database' => 'sqlite_testing']);
//        \Artisan::call('db:seed', ['--class' => 'UserConfig', '--database' => 'sqlite_testing']);
//
//
//        \Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--database' => 'sqlite_testing']);



//       $this->faker = \Faker\Factory::create();
//        $this->withoutExceptionHandling();
    }

    /**
     * Reset the migrations
     */
    public function tearDown(): void {
//        $this->artisan('migrate:reset');
        parent::tearDown();
    }
}
