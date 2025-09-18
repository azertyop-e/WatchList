<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;

Route::get('/', [MovieController::class, 'getMovieStored'])->name('home');

Route::controller(MovieController::class)->prefix('movie')->name('movie.')->group(function () {
    Route::get('/popular', 'getPopular')->name('popular');
    Route::get('/top', 'getTop')->name('top');
    Route::get('/search', 'getSearch')->name('search');
    Route::get('/seen', 'getSeenMovies')->name('seen');

    Route::post('/save', 'saveMovie')->name('save');
    Route::post('/mark-seen', 'markAsSeen')->name('mark-seen');
    Route::post('/mark-unseen', 'markAsUnseen')->name('mark-unseen');
    Route::get('/{id}', 'getMovieDetails')->name('detail')->where('id', '[0-9]+');
});


