<li class="nav-item active">
    <a class="nav-link" href="javascript:history.back()"><i class="fas fa-arrow-circle-left"></i></a>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle"  style="padding-right: .1rem;padding-left: .1rem;" href="#" id="navbarDropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Accueil </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <h6 class="dropdown-header">Navigation</h6>
        <li><a class="dropdown-item" href="{{ $page->start_menu_array[2] }}">{{ $page->start_menu_array[0] }}</a></li>
        <div class="dropdown-divider"></div>
        {!!$page->nav_menu_thread !!}
    </ul>
</li>

@foreach($page->nav_thread as $link)
  <li class="nav-item">
    <a class="nav-link" style="padding-right: .1rem;padding-left: .1rem;" href="{{ $link[2] }}">/&nbsp;{{ $link[0] }}</a>
  </li>
@endforeach
<li class="nav-item active">
    <a class="nav-link" style="padding-right: .1rem;padding-left: .1rem;" href="{{ $page->url }}">/&nbsp;{{ $page->pageName }}</a>
</li>
@if($page->active)
<li class="nav-item active">
    <a class="nav-link" style="padding-right: .1rem;padding-left: .1rem;" target="_blank" href="{{ $page->url }}?print=1">&nbsp;<i class="fas fa-print"></i></a>
</li>
@endif
