@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">

          <iframe id="wikiFrame" src="https://compte.cvvt.fr/wiki/Accueil" style="width: 100%;min-height: 600px;border: none;"></iframe>

        </div>
    </div>
</div>
<script type="application/javascript">
  function resizeIFrameToFitContent() {
      var iFrame = document.getElementById( 'wikiFrame' );
      /*iFrame.width  = iFrame.contentWindow.document.body.scrollWidth;*/
      if (iFrame.contentWindow.document.body.scrollHeight > 600) {
        iFrame.height = iFrame.contentWindow.document.body.scrollHeight;
      } else {
        iFrame.height = 600;
      }
      
  }

  $(document).ready(function(){
    resizeIFrameToFitContent();
  });

</script>

@endsection
