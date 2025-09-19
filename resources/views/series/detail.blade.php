@extends('base')

@section('content')
<x-media-detail :mediaData="$seriesData" mediaType="series" />
@endsection
