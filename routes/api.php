<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/**
 * Registration and Authentication
 *
 *
 */
Route::group([
    'middleware' => 'api',
    // 'namespace' => 'App\Http\Controllers',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'RegisterController@register');
    Route::post('user_submit_qualification_form', 'RegisterController@user_submit_qualification_form');
    Route::post('resend_verification_email', 'RegisterController@resend_verification_email');
    Route::post('check_change_password_code', 'RegisterController@check_change_password_code');
    Route::post('check_verification_code', 'RegisterController@check_verification_code');
    Route::post('reset_password_request', 'RegisterController@reset_password_request')->name('password.reset');
    Route::post('reset_password', 'RegisterController@reset_password');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('change_email_request', 'EmailChangeController@change_email_request');
    Route::post('change_email_verify', 'EmailChangeController@change_email_verify');
    Route::post('change_password_request', 'PasswordChangeController@change_password_request');
});
//working
//working
/**
 * Public routes
 * TODO / distinguish verified/nonverified?
 *
 */
//TODO auth
Route::get('omni', 'OmniController@omni');
Route::get('logs', 'LogController@logs');
Route::get('refresh', 'AuthController@refresh')->name('api.jwt.refresh');
Route::post('validate_paypal', 'PaypalController@validate_paypal');
Route::get('test', function (Request $request) {
    return 'response';
});
Route::resource('users', 'UserController');
Route::resource('projectparticipant', 'ProjectParticipantController');
Route::resource('participants', 'ParticipantController');

Route::post('quota', 'AdminController@quota');
Route::post('create_selection', 'ProjectParticipantController@create_selection');
Route::post('get_selection', 'ProjectParticipantController@get_selection');
Route::post('get_advanced_selection', 'ParticipantController@get_advanced_selection');
/**
 * ONLY Administrator
 *
 */
Route::group(['middleware' => ['role:administrator'],    'middleware' => 'api'], function () {
    Route::post('invite_researcher', 'AdminController@invite_researcher');
    Route::resource('settings', 'SettingsController');
    Route::post('backup', 'SettingsController@backup');
});
/**
 * Administrators, Researcher
 *
 *
 */
Route::group(['middleware' => ['role:administrator|researcher']], function () {
    Route::resource('email_templates', 'EmailTemplateController');
    Route::post('email_templates_with_project', 'EmailTemplateController@email_templates_with_project');

    Route::post('update_participant_validation', 'PaymentValidationController@update_participant_validation');
    Route::resource('projects', 'ProjectController');
    Route::post('send_project_reminders', 'ProjectInvitationController@send_project_reminders');
    Route::post('send_project_invitations', 'ProjectInvitationController@send_project_invitations');
    Route::post('send_custom_message', 'ProjectInvitationController@send_custom_message');
    Route::get('project_participants', 'ProjectParticipantController@project_participants');
});
/**
 * Verified Participant
 *
 *
 */
Route::group(['middleware' => ['role:participant', 'verified']], function () {
    Route::post('invite_friend', 'RegisterController@invite_participant');

    Route::get('my_projects', 'MyProjectsController@my_projects');
    Route::post('start_project', 'MyProjectsController@start_project');
    Route::post('verify_project_code', 'MyProjectsController@verify_project_code');
});



//all
Route::group(['middleware' => ['role:administrator|researcher|participant']], function () {
    Route::get('profile', 'ParticipantController@show_profile');

    Route::resource('projects', 'ProjectController');
    Route::get('motd', 'ProfileController@motd');
});
//verified all
Route::group(['middleware' => ['role:administrator|researcher|participant', 'verified']], function () {
});
