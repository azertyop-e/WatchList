<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SerieController;

Route::get('/', [MovieController::class, 'getMediaStored'])->name('home');
Route::get('/seen', [MovieController::class, 'getAllSeenMedia'])->name('seen');
Route::get('/popular', [MovieController::class, 'getPopularList'])->name('popular');
Route::get('/top', [MovieController::class, 'getTopList'])->name('top');

Route::controller(MovieController::class)->prefix('movie')->name('movie.')->group(function () {
    Route::get('/search', 'getSearch')->name('search');
    Route::get('/seen', 'getSeenMovies')->name('seen');

    Route::post('/save', 'saveMovie')->name('save');
    Route::post('/mark-seen', 'markAsSeen')->name('mark-seen');
    Route::post('/mark-unseen', 'markAsUnseen')->name('mark-unseen');
    Route::delete('/delete', 'deleteMovie')->name('delete');
    Route::get('/{id}', 'getMovieDetails')->name('detail')->where('id', '[0-9]+');
});

Route::controller(SerieController::class)->prefix('series')->name('series.')->group(function () {
    Route::get('/search', 'getSearch')->name('search');
    Route::get('/seen', 'getSeenMedia')->name('seen');

    Route::post('/save', 'saveSeries')->name('save');
    Route::post('/save-complete/{tmdbId}', 'saveCompleteSeries')->name('save-complete')->where('tmdbId', '[0-9]+');
    Route::put('/update-complete/{tmdbId}', 'updateCompleteSeries')->name('update-complete')->where('tmdbId', '[0-9]+');
    Route::post('/mark-seen', 'markAsSeen')->name('mark-seen');
    Route::post('/mark-unseen', 'markAsUnseen')->name('mark-unseen');
    Route::delete('/delete', 'deleteSeries')->name('delete');
    
    Route::post('/{seriesTmdbId}/seasons/save-all', 'saveAllSeasons')->name('seasons.save-all')->where('seriesTmdbId', '[0-9]+');
    Route::post('/{seriesTmdbId}/seasons/{seasonNumber}/save', 'saveSeason')->name('seasons.save')->where(['seriesTmdbId' => '[0-9]+', 'seasonNumber' => '[0-9]+']);
    Route::get('/{seriesTmdbId}/seasons/{seasonNumber}', 'getSeasonDetails')->name('seasons.details')->where(['seriesTmdbId' => '[0-9]+', 'seasonNumber' => '[0-9]+']);
    
    Route::post('/episodes/mark-watched', 'markEpisodeAsWatched')->name('episodes.mark-watched');
    Route::post('/episodes/mark-unwatched', 'markEpisodeAsUnwatched')->name('episodes.mark-unwatched');
    Route::post('/{seriesId}/mark-episode-watched', 'markEpisodeProgress')->name('mark-episode-watched')->where('seriesId', '[0-9]+');
    
    Route::get('/{id}', 'getMediaDetails')->name('detail')->where('id', '[0-9]+');
});

