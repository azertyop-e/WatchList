<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SerieController;

Route::get('/', [MovieController::class, 'getMediaStored'])->name('home');
Route::get('/popular', [MovieController::class, 'getPopularList'])->name('popular');
Route::get('/top', [MovieController::class, 'getTopList'])->name('top');

Route::controller(MovieController::class)->prefix('movie')->name('movie.')->group(function () {
    Route::get('/search', 'getSearch')->name('search');
    Route::get('/{id}', 'getMovieDetails')->name('detail')->where('id', '[0-9]+');

    Route::post('/save', 'saveMovie')->name('save');
    
    Route::post('/mark-seen', 'markAsSeen')->name('mark-seen');
    Route::post('/mark-unseen', 'markAsUnseen')->name('mark-unseen');

    Route::delete('/delete', 'deleteMovie')->name('delete');
});

Route::controller(SerieController::class)->prefix('series')->name('series.')->group(function () {
    Route::get('/search', 'getSearch')->name('search');
    Route::get('/{id}', 'getMediaDetails')->name('detail')->where('id', '[0-9]+');
    
    Route::post('/save', 'saveSeries')->name('save');

    Route::post('/mark-unseen', 'markAsUnseen')->name('mark-unseen');
    Route::post('/episodes/mark-watched', 'markEpisodeAsWatched')->name('episodes.mark-watched');
    Route::post('/episodes/mark-unwatched', 'markEpisodeAsUnwatched')->name('episodes.mark-unwatched');
    Route::post('/{seriesId}/mark-episode-watched', 'markEpisodeProgress')->name('mark-episode-watched')->where('seriesId', '[0-9]+');
    
    Route::delete('/delete', 'deleteSeries')->name('delete');
});

