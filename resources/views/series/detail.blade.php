@extends('base')

@section('content')
<x-media-detail :mediaData="$seriesData" mediaType="series" />

<!-- Affichage des saisons et épisodes -->
<x-seasons-episodes :series="$seriesData" />
@endsection
