<script src="https://kit.fontawesome.com/9724d9dada.js" crossorigin="anonymous"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var ColorClass = Quill.import('attributors/class/color');
		var SizeStyle = Quill.import('attributors/style/size');
		var AlignStyle = Quill.import('attributors/style/align');
		Quill.register(ColorClass, true);
		Quill.register(SizeStyle, true);
		Quill.register(AlignStyle, true);
		let quill = new Quill('#wiki-content-editor', {
		  modules: {
		    toolbar: [
		      [{ header: [] }],
		      ['bold', 'italic', 'underline', 'link'],
		      [{ 'indent': '-1'}, { 'indent': '+1' }],
		      [{ color: [] }, { background: [] }],
		      [{ list: 'ordered' }, { list: 'bullet' }],
		      [{ 'align': [] }],
		      ['image'],
		      ['clean'],

		    ]
		  },
		  theme: 'snow',
		});

		quill.on('text-change', function(delta, oldDelta, source) {
		  	$('#content-editor').val(quill.root.innerHTML);
		});
	});

	function modPage()
	{
		$('#wiki-content').toggle();
		$('#wiki-form').toggle();
		parent.resizeIFrameToFitContent();
	}

	$(document).ready(function(){
		parent.resizeIFrameToFitContent();
	});

	function decryptWikiPassword(id)
	{
		let password = $('#masterPass-'+id).val();
		let encrypted = $('#encrypted-'+id).val();
		$('#masterPass-'+id).val('');
		$.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	        }
	    });

	    $.post( "/wikipassword", { password: password, encrypted: encrypted } )
	      .done(function( data ) {
	      	$('#text-encrypted-'+id).html(data);
	      	$('#text-encrypted-'+id).fadeIn(0);
	      	$('#form-encrypted-'+id).fadeOut(0);
	      	
	        setTimeout(function(){ $('#text-encrypted-'+id).html('Cliquez ici pour afficher le mot de passe : ********'); }, 60000);
	    });
	}
</script>