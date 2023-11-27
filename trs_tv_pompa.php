<?php
Route::group(['namespace' => 'Trs\Tv', 'middleware' => ['web', 'auth']], function () {
    // DASHBOARD IOT POMPA SUMUR WATER LEVEL
    Route::resource('/trs_tv_pompa', 'TvpompaController');
	Route::get('/trs_tv_pompa_list', 'TvpompaController@getList');
	Route::get('/trs_tv_pompa_lookup', 'TvpompaController@getLookup');
    Route::get('/trs_tv_pompa', 'TvpompaController@index');
    Route::post('/trs_tv_pompa_data', 'TvpompaController@TvPompaData');
    Route::get('/trs_tv_pompa_waktu', 'TvpompaController@TotalWaktu');
    Route::get('/trs_tv_pompa_notif', 'TvpompaController@SendNotif');
    Route::get('/trs_tv_pompa_notification', 'TvpompaController@SendNotification');
    Route::get('/trs_tv_pompa_action', 'TvpompaController@ActionNotif');
    

});