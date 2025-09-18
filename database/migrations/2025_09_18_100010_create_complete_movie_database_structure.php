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
        Schema::dropIfExists('movie_spoken_languages');
        Schema::dropIfExists('movie_production_countries');
        Schema::dropIfExists('movie_production_companies');
        Schema::dropIfExists('gender_movie');
        Schema::dropIfExists('movieModel');
        Schema::dropIfExists('gendersModel');
        Schema::dropIfExists('production_companies');
        Schema::dropIfExists('production_countries');
        Schema::dropIfExists('spoken_languages');
        Schema::dropIfExists('collections');

        Schema::create('gendersModel', function (Blueprint $table) {
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

        Schema::create('movieModel', function (Blueprint $table) {
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
            
            $table->foreign('movie_id')->references('id')->on('movieModel')->onDelete('cascade');
            $table->foreign('gender_id')->references('id')->on('gendersModel')->onDelete('cascade');
        });

        Schema::create('movie_production_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('production_company_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movieModel')->onDelete('cascade');
            $table->foreign('production_company_id')->references('id')->on('production_companies')->onDelete('cascade');
            
            $table->unique(['movie_id', 'production_company_id']);
        });

        Schema::create('movie_production_countries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('production_country_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movieModel')->onDelete('cascade');
            $table->foreign('production_country_id')->references('id')->on('production_countries')->onDelete('cascade');
            
            $table->unique(['movie_id', 'production_country_id']);
        });

        Schema::create('movie_spoken_languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->unsignedBigInteger('spoken_language_id');
            $table->timestamps();
            
            $table->foreign('movie_id')->references('id')->on('movieModel')->onDelete('cascade');
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
            
            $table->foreign('movie_id')->references('id')->on('movieModel')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
            
            $table->unique(['movie_id', 'actor_id', 'character_name']);
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
        Schema::dropIfExists('movieModel');
        Schema::dropIfExists('actors');
        Schema::dropIfExists('gendersModel');
        Schema::dropIfExists('production_companies');
        Schema::dropIfExists('production_countries');
        Schema::dropIfExists('spoken_languages');
        Schema::dropIfExists('collections');
    }
};
