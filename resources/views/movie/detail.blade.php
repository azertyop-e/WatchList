@extends('base')

@section('content')
<x-media-detail :mediaData="$movieData" mediaType="movie" />
@endsection
