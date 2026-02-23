<h3>Statut du membre</h3>
<input type="hidden" name="isSupervisor" id="isSupervisorInput"
@isset($user)
    value="{{ $user->isSupervisor }}"
@endisset
>
<div class="form-check mb-3">
  <input name="userState[]" value="Licence associative" 
  @isset($user)
    @if($user->isAttr('Licence associative'))
      checked
    @endif
  @endisset
  type="checkbox" class="form-check-input" id="{{ $block }}UserStateaccomp">
  <label class="form-check-label" for="{{ $block }}UserStateaccomp">Licence associative</label>
</div>
<div class="form-check mb-3">
  <input name="userState[]" value="Elève" 
  @isset($user)
    @if($user->isAttr('Elève'))
      checked
    @endif
  @endisset
  type="checkbox" class="form-check-input" id="{{ $block }}UserStateeleve">
  <label class="form-check-label" for="{{ $block }}UserStateeleve">Elève</label>
</div>
<div class="form-check mb-3">
  <input name="userState[]" value="Pilote" 
  @isset($user)
    @if($user->isAttr('Pilote'))
      checked
    @endif
  @endisset
  type="checkbox" class="form-check-input" id="{{ $block }}UserStatepilote">
  <label class="form-check-label" for="{{ $block }}UserStatepilote">Pilote</label>
</div>
<div class="form-check mb-3">
  <input name="userState[]" value="Instructeur Planeur" 
  @isset($user)
    @if($user->isAttr('Instructeur Planeur'))
      checked
    @endif
  @endisset
  type="checkbox" class="form-check-input isSupervisorAttr" onchange="updateIsSupervisor();" id="{{ $block }}UserStateinstructeurplaneur">
  <label class="form-check-label" for="{{ $block }}UserStateinstructeurplaneur">Instructeur Planeur</label>
</div>
<div class="form-check mb-3">
  <input name="userState[]" value="Instructeur ULM" 
  @isset($user)
    @if($user->isAttr('Instructeur ULM'))
      checked
    @endif
  @endisset
  type="checkbox" class="form-check-input isSupervisorAttr" onchange="updateIsSupervisor();" id="{{ $block }}UserStateinstructeurULM">
  <label class="form-check-label" for="{{ $block }}UserStateinstructeurULM">Instructeur ULM</label>
</div>
<div class="form-check mb-3">
  <input name="userState[]" value="Remorqueur" 
  @isset($user)
    @if($user->isAttr('Remorqueur'))
      checked
    @endif
  @endisset
  type="checkbox" class="form-check-input" id="{{ $block }}UserStateremorqueur">
  <label class="form-check-label" for="{{ $block }}UserStateremorqueur">Remorqueur</label>
</div>
<div class="alert alert-danger" role="alert" id="{{ $block }}UserHelpState" style="display: none;">
    Merci de selectionner au moins une case!
</div>
<script type="text/javascript">
  function updateIsSupervisor()
  {
    $('#isSupervisorInput').val(0);
    $('.isSupervisorAttr').each(function( index ) {
      console.log( index + ": " + $( this ).prop('checked') );
      if ($( this ).prop('checked') == true) {
        $('#isSupervisorInput').val(1);
      }
    });
  }
</script>