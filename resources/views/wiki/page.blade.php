@extends('wiki.layout')
    @section('content')
        @isset($page)
        @include('wiki.menus')
        <h3>{{ $page->pageName }}</h3>
        <hr>
        <div id="wiki-content">
            @include('wiki.pageHtml')
        </div>
        @if($page->is_writable)
            <div id="wiki-form" style="display: none;">
            @include('wiki.pageForm')
            </div>
        @endif
        <hr>
        <small class="text-muted">Derniére modification : {{ $page->userName }} le {{ $page->last_update_date_fr }}</small>
        @if(!$page->active)
        <small><a href="/wikiRestore/{{ $page->id }}">Restaure cette version de la page</a></small>
        @endif
        @endisset
        @empty($page)
        <h3>Page Inconnu!</h3>
        @endempty
@endsection