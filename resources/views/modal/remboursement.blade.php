    
<!-- Modal remboursement-->
    <div class="modal fade" id="remboursementModal" tabindex="-1" role="dialog" aria-labelledby="remboursementModallLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="remboursementModallLabel">Achat Club</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="post" action="ajoutDepense"  enctype="multipart/form-data">
              @csrf
              @can('admin')
              <input type="hidden" name="idUser" value="{{ Auth::user()->id }}">
              <div class="mb-3">
                <label for="remboursementModalTypidUsereHelp">Adhérent</label>
                <select class="form-control" id="remboursementModalidUser" aria-describedby="remboursementModalTypidUsereHelp" name="idUser">
                    @foreach($allUsers as $userList)
                    <option value="{{ $userList->id }}" @if($userList->id == Auth::user()->id) selected @endif>{{ $userList->name }}</option>
                    @endforeach
                </select>
              </div>
              @endcan
              @cannot('admin')
              <input type="hidden" name="idUser" value="{{ Auth::user()->id }}">
              @endcannot
              <div class="mb-3">
                <label for="remboursementModalAmount">Montant</label>
                <input type="number" min="10" max="300" step="0.01" class="form-control" id="remboursementModalAmount" aria-describedby="payModalAmountHelp" placeholder="20,00" name="amount" required>
                <small id="payModalAmountHelp" class="form-text text-body-secondary">Montant indiqué sur le ticket ou la preuve d'achat.</small>
              </div>
                <div class="alert alert-danger" id="remboursementModalErrorAmount" role="alert" style="display: none;">
                    Veuillez indiquer un montant correct (ex:150.00).
                </div>
              <div class="mb-3">
                <label for="remboursementModalText">Observation</label>
                <input type="text" name="observation" class="form-control" id="remboursementModalText">
              </div>

              <div class="mb-3">
                <label for="inputGroupFile01" class="form-label">Preuve d'achat</label>
                <input type="file" name="facture" class="form-control" id="inputGroupFile01" required>
              </div>
              
              <div class="mb-3">
                <label for="remboursementModalDep">Type de Dépense</label>
                <select name="categorie" class="form-control" id="remboursementModalType" aria-describedby="remboursementModalTypeHelp" required>
                    <option value="">Choisissez le type de dépense</option>
                    @foreach($refundCategory as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->number }})</option>
                    @endforeach
                    <option value="0">Autres (précisez dans le champ observation)</option>
                </select>
              </div>

               <div class="mb-3">
                <label for="remboursementModalType">Type de remboursement</label>
                <select name="type" class="form-control" id="remboursementModalType" aria-describedby="remboursementModalTypeHelp">
                    <option value="0">Remboursement sur compte pilote</option>
                    <option value="1">Chéque</option>
                </select>
              </div>
              <small class="form-text text-body-secondary">Les achats sont validé par le trésorier.</small>
              <button type="submit" class="btn btn-primary float-end">Enregistrer</button>
            </form>
          </div>
        </div>
      </div>
    </div>