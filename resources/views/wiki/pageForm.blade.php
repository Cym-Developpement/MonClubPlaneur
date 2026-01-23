<div class="card">
  	<div class="card-body">
  		<h5 class="card-title">Modifier la page</h5>
		<div  class="quilljs-editor-auto" id="wiki-content-editor" style="min-height: 300px;" >{!! $page->content !!}</div>
	</div>
</div>
<hr>
<form action="/wiki/update/{{ $page->id }}" method="post">
	@csrf
	<textarea style="display: none;" data-typeinput="text" id="content-editor" class="webFormQuill" name="content"></textarea>
	<div class="row justify-content-center">
		<div class="col-md-3">
			<button type="submit" class="btn btn-primary btn-block">Enregistrer</button>
		</div>
	</div>
</form>