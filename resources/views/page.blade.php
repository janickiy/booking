@extends('layouts.app', ['news' => true, 'page'=>'main'])

@section('meta_description', $page->meta_description())

@section('meta_keywords', $page->meta_keywords())

@section('content')

    <h2>{{ $page->title_page() }}</h2>

    {!!  $page->content() !!}

@endsection
