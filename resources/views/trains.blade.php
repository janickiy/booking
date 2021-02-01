@extends('layouts.app', ['news' => true, 'page'=>'main'])

@section('title', isset($title) ? $title : '' )

@section('meta_description', '')

@section('meta_keywords', '')

@section('content')
<railway></railway>
@endsection
