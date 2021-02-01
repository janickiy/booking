@extends('layouts.app', ['news' => true, 'page'=>'main'])



@section('meta_description', $path->meta_description())

@section('meta_keywords', $path->meta_keywords())

@section('content')

    <h2>{{ $path->title_page() }}</h2>

    {{ $path->content() }}

    @foreach($path->children as $row)

        <a href="{{ url($row->urlPath) }}" >{{ url($row->urlPath) }}</a><br>

    @endforeach

@endsection
