</p>
<div class="row justify-content-center">
    <div class="encryptedpassword col-md-8 col-lg-6 col-xl-4" 
        onclick="this.getElementsByClassName('form-encrypted')[0].style.display = 'flex';this.getElementsByClassName('text-encrypted')[0].style.display = 'none';" 
        style="cursor: pointer;text-align: center;">
        <b class="text-encrypted bg-light" id="text-encrypted-{{ $id }}" style="font-size: 1.3em;padding: 10px;border-radius: 3px;">Cliquez ici pour afficher le mot de passe : ********</b>
        <div class="input-group mb-3 form-encrypted" id="form-encrypted-{{ $id }}" style="display: none;">
            <input type="hidden" id="encrypted-{{ $id }}" value="{{ $encrypted }}">
          <input type="password" autocomplete="off" class="form-control" id="masterPass-{{ $id }}" placeholder="Tapez ici le mot de passe principal ..." aria-describedby="unlockPass-{{ $id }}" readonly onfocus="this.removeAttribute('readonly');">
            <button class="btn btn-outline-success" id="unlockPass-{{ $id }}" onclick="decryptWikiPassword({{ $id }});" type="button"><i class="fas fa-unlock"></i></button>
        </div>
    </div>
</div>

<p>


