<?php
/**
 * Created by PhpStorm.
 * User: lumin
 * Date: 16-12-13
 * Time: 下午10:13
 */

Route::group(['middleware' => 'token'],function(){
    Route::post('/problem/create','ProblemController@addProblem');
    Route::post('/problem/{id}/submit','ProblemController@submitProblem');
    Route::get('problem/{id}/rundata','ProblemController@getRunData');
});

Route::get('/problem/{id}','ProblemController@getProblem');
Route::get('/problems','ProblemController@getProblems');
Route::get('/problems/search','ProblemController@searchProblems');
