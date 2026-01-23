@extends('wiki.layout')
@section('content')
    <style type="text/css">
        body {
            background-color: #ffffff!important;
        }
        @page {
            size: 7in 9.25in;
            margin: 27mm 16mm 27mm 16mm;
        }
    </style>
    @isset($page)
    <div class="row justify-content-center">
        <div class="col-12 text-center">
            <h1>{{ $page->pageName }}</h1>
        </div>
    </div>
    <br>
    <div id="wiki-content">
        {!! $page->html !!}
    </div>
    <hr>
    <small class="text-muted">Auteur : {{ $page->userName }} le {{ $page->last_update_date_fr }}</small>
    @endisset
    @empty($page)
    <h3>Page Inconnu!</h3>
    @endempty
    
    <script type="text/javascript">
        $(document).ready(function(){
            window.print();
        });
    </script>    
@endsection