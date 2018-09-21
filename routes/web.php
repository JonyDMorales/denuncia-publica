<?php

Route::get('/', function () { return view('welcome'); });
Auth::routes();

/*
 * Frontend
 */
Route::get('/denuncia', 'DenunciaController@formulario')->name('formulario');


Route::prefix('denuncia')->group(function(){
    //Denuncia
    Route::post('insertar', 'DenunciaController@insertarDenuncia')->name('insertar');
    Route::get('lista', 'DenunciaController@generarTokens')->name('tokens');
    Route::get('postal', 'DenunciaController@buscaCodigoPostal')->name('postal');
});