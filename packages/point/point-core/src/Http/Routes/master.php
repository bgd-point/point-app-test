<?php
Route::group(['middleware' => 'auth', 'prefix' => 'master', 'namespace' => 'Point\Core\Http\Controllers\Master'], function () {
    Route::get('/', function () {
        return view('core::app.master.menu');
    });
    
    // User
    Route::post('/user/delete', 'UserController@delete');
    Route::get('/user/role/toggle', 'UserController@toggleRole');
    Route::get('/user/{user_id}/role', 'UserController@role');
    Route::resource('/user', 'UserController', ['except' => ['delete']]);

    // Role
    Route::get('/role/permission-all', 'RoleController@permissionAll');
    Route::post('/role/delete', 'RoleController@delete');
    Route::get('/role/permission/toggle', 'RoleController@togglePermission');
    Route::get('/role/{role_id}/permission/{group_id}', 'RoleController@permission');
    Route::get('/role/{role_id}/user-access', 'RoleController@userAccess');
    Route::post('/role/user-access/toggle', 'RoleController@toggleUserAccess');
    Route::resource('/role', 'RoleController', ['except' => ['delete', 'create']]);
});
