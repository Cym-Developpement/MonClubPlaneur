<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      @include('wiki.pageNav')
    </ul>
    @if($page->is_writable)
      @include('wiki.pageWrite')
    @endif
  </div>
</nav>