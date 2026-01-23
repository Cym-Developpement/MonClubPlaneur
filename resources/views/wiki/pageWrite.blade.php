@if($page->active)
<ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a href="#" class="nav-link" onclick="$('#newPageForm').toggle();">Nouvelle Page</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" onclick="modPage();">Modifier</a>
      </li>
      @if($page->is_deletable)
      <li class="nav-item">
        <a class="nav-link text-danger" onclick="return confirm('{{ $page->delete_alert }}');" href="/wiki/delete/{{ $page->id }}">Supprimer</a>
      </li>
      @endif
</ul>
<form id="newPageForm" class="form-inline my-2 my-lg-0" action="/wiki/new" method="POST" style="display: none;">
  @csrf
  <input type="hidden" name="current" value="{{ $page->id }}">
  <input class="form-control mr-sm-2" name="name" type="text" placeholder="Nouvelle page" aria-label="Nouvelle Page">
  <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Ajouter</button>
</form>
@endif
<div class="dropdown dropleft">
  <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
    Versions
  </button>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
    @foreach($page->revision_list as $revision)
      <a class="dropdown-item" href="{{ $revision->url }}?revision=1">{{ $loop->iteration }} - {{ $revision->userName }} le {{ $revision->last_update_date_fr }}</a>
    @endforeach
  </div>
</div>