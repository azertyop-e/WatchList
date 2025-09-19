<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('series_roles');
        Schema::dropIfExists('series_spoken_languages');
        Schema::dropIfExists('series_production_countries');
        Schema::dropIfExists('series_production_companies');
        Schema::dropIfExists('series_genders');
        Schema::dropIfExists('series_networks');
        Schema::dropIfExists('series_creators');
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('series');
        Schema::dropIfExists('networks');
        Schema::dropIfExists('creators');
        
        Schema::dropIfExists('movie_spoken_languages');
        Schema::dropIfExists('movie_production_countries');
        Schema::dropIfExists('movie_production_companies');
        Schema::dropIfExists('gender_movie');
        Schema::dropIfExists('movie');
        Schema::dropIfExists('gender');
        Schema::dropIfExists('production_companies');
        Schema::dropIfExists('production_countries');
        Schema::dropIfExists('spoken_languages');
        Schema::dropIfExists('collections');

        Schema::create('gender', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('production_companies', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('origin_country', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('production_countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso_3166_1', 10)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('spoken_languages', function (Blueprint $table) {
            $table->id();
            $table->string('iso_639_1', 10)->unique();
            $table->string('name');
            $table->string('english_name');
            $table->timestamps();
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->timestamps();
        });

        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('profile_path')->nullable();
            $table->date('birthday')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->text('biography')->nullable();
            $table->string('known_for_department')->nullable();
            $table->decimal('popularity', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('movie', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('release_date')->nullable();
            $table->boolean('is_seen')->default(0);
            
            $table->string('original_title')->nullable();
            $table->string('tagline')->nullable();
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->integer('vote_count')->nullable();
            $table->integer('runtime')->nullable();
            $table->string('original_language', 10)->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('budget')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->decimal('popularity', 10, 2)->nullable();
            $table->unsignedBigInteger('collection_id')->nullable();
            
            $table->timestamps();
            
            $table->foreign('collection_id')->references('id')->on('collections')->onDelete('set null');
        });

        Schema::create('gender_movie', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('gender_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movie')->onDelete('cascade');
            $table->foreign('gender_id')->references('id')->on('gender')->onDelete('cascade');
        });

        Schema::create('movie_production_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('production_company_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movie')->onDelete('cascade');
            $table->foreign('production_company_id')->references('id')->on('production_companies')->onDelete('cascade');
            
            $table->unique(['movie_id', 'production_company_id']);
        });

        Schema::create('movie_production_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('production_country_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movie')->onDelete('cascade');
            $table->foreign('production_country_id')->references('id')->on('production_countries')->onDelete('cascade');
            
            $table->unique(['movie_id', 'production_country_id']);
        });

        Schema::create('movie_spoken_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('spoken_language_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movie')->onDelete('cascade');
            $table->foreign('spoken_language_id')->references('id')->on('spoken_languages')->onDelete('cascade');
            
            $table->unique(['movie_id', 'spoken_language_id']);
        });

        Schema::create('movie_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('actor_id');
            $table->string('character_name');
            $table->integer('order')->nullable();
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movie')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
            
            $table->unique(['movie_id', 'actor_id', 'character_name']);
        });

        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('original_name')->nullable();
            $table->string('profile_path')->nullable();
            $table->integer('gender')->nullable();
            $table->timestamps();
        });

        Schema::create('networks', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('origin_country', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->string('name');
            $table->string('original_name')->nullable();
            $table->longText('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();
            $table->date('first_air_date')->nullable();
            $table->date('last_air_date')->nullable();
            $table->string('status')->nullable(); 
            $table->string('type')->nullable(); 
            $table->string('tagline')->nullable();
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->integer('vote_count')->nullable();
            $table->decimal('popularity', 10, 2)->nullable();
            $table->string('original_language', 10)->nullable();
            $table->string('homepage')->nullable();
            $table->boolean('in_production')->default(false);
            $table->integer('number_of_episodes')->nullable();
            $table->integer('number_of_seasons')->nullable();
            $table->json('episode_run_time')->nullable(); 
            $table->json('languages')->nullable(); 
            $table->json('origin_country')->nullable(); 
            $table->boolean('is_watched')->default(false);
            $table->timestamps();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->unsignedBigInteger('series_id');
            $table->string('name');
            $table->longText('overview')->nullable();
            $table->string('poster_path')->nullable();
            $table->date('air_date')->nullable();
            $table->integer('episode_count')->nullable();
            $table->integer('season_number');
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
        });

        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->integer('tmdb_id')->unique();
            $table->unsignedBigInteger('season_id');
            $table->string('name');
            $table->longText('overview')->nullable();
            $table->string('still_path')->nullable();
            $table->date('air_date')->nullable();
            $table->integer('episode_number');
            $table->string('episode_type')->nullable(); 
            $table->string('production_code')->nullable();
            $table->integer('runtime')->nullable();
            $table->decimal('vote_average', 3, 1)->nullable();
            $table->integer('vote_count')->nullable();
            $table->boolean('is_watched')->default(false);
            $table->timestamps();
            
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
        });

        Schema::create('series_creators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('creator_id');
            $table->string('credit_id')->nullable();
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('creators')->onDelete('cascade');
            
            $table->unique(['series_id', 'creator_id'], 'series_creators_unique');
        });

        Schema::create('series_networks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('network_id');
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('network_id')->references('id')->on('networks')->onDelete('cascade');
            
            $table->unique(['series_id', 'network_id'], 'series_networks_unique');
        });

        Schema::create('series_genders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('gender_id');
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('gender_id')->references('id')->on('gender')->onDelete('cascade');
            
            $table->unique(['series_id', 'gender_id'], 'series_genders_unique');
        });

        Schema::create('series_production_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('production_company_id');
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('production_company_id')->references('id')->on('production_companies')->onDelete('cascade');
            
            $table->unique(['series_id', 'production_company_id'], 'series_prod_comp_unique');
        });

        Schema::create('series_production_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('production_country_id');
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('production_country_id')->references('id')->on('production_countries')->onDelete('cascade');
            
            $table->unique(['series_id', 'production_country_id'], 'series_prod_country_unique');
        });

        Schema::create('series_spoken_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('spoken_language_id');
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('spoken_language_id')->references('id')->on('spoken_languages')->onDelete('cascade');
            
            $table->unique(['series_id', 'spoken_language_id'], 'series_spoken_lang_unique');
        });

        Schema::create('series_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('actor_id');
            $table->string('character_name');
            $table->integer('order')->nullable();
            $table->timestamps();
            
            $table->foreign('series_id')->references('id')->on('series')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
            
            $table->unique(['series_id', 'actor_id', 'character_name'], 'series_roles_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_roles');
        Schema::dropIfExists('movie_spoken_languages');
        Schema::dropIfExists('movie_production_countries');
        Schema::dropIfExists('movie_production_companies');
        Schema::dropIfExists('gender_movie');
        Schema::dropIfExists('movie');
        Schema::dropIfExists('actors');
        Schema::dropIfExists('gender');
        Schema::dropIfExists('production_companies');
        Schema::dropIfExists('production_countries');
        Schema::dropIfExists('spoken_languages');
        Schema::dropIfExists('collections');
        
        Schema::dropIfExists('series_roles');
        Schema::dropIfExists('series_spoken_languages');
        Schema::dropIfExists('series_production_countries');
        Schema::dropIfExists('series_production_companies');
        Schema::dropIfExists('series_genders');
        Schema::dropIfExists('series_networks');
        Schema::dropIfExists('series_creators');
        Schema::dropIfExists('episodes');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('series');
        Schema::dropIfExists('networks');
        Schema::dropIfExists('creators');
    }
};
