@if(count($page->child_list) > 0 && $page->active)
@foreach($page->child_list as $child)
    <a href="{{ $child->url }}">{{ $child->pageName }}</a>&nbsp; 
@endforeach
<hr>
@endif
{!! $page->html !!}