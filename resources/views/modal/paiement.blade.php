    <!-- Modal add payments-->
    <div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
      <div id="modalDialogBlockPayModal" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="payModalLabel">Approvisionner mon compte</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label for="payModalType" class="form-label">Type de paiement</label>
                <select class="form-select" id="payModalType" aria-describedby="payModalTypeHelp" onchange="selectTypePaid(this.value);">
                    <option value="CB">Carte Bancaire</option>
                    <!--<option value="CH">Chèque</option>-->
                    <option value="VI">Virement</option>
                </select>
                <small id="payModalTypeHelp" class="form-text text-muted">Pour les chèques et les virements, la transaction sera validé par le trésorerier.<br>Les paiement Carte Bancaire sont validés immédiatement.</small>
            </div>
            <div class="paidNoCB" style="display: none;">
              <div class="mb-3">
                <label for="payModalAmount" class="form-label">Montant</label>
                <input type="number" min="10" max="3000" class="form-control" id="payModalAmount" aria-describedby="payModalAmountHelp" placeholder="20,00">
                <small id="payModalAmountHelp" class="form-text text-muted">Montant minimum 10€.</small>
              </div>
                <div class="alert alert-danger" id="payModalErrorAmount" role="alert" style="display: none;">
                    Veuillez indiquer un montant correct (ex:150.00).
                </div>

              <div class="mb-3">
                <label for="payModalText" class="form-label">Observation</label>
                <input type="text" class="form-control" id="payModalText">
              </div>
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="payModalSendMail" checked>
                <label class="form-check-label" for="payModalSendMail">Recevoir un reçu par e-mail</label>
              </div>
              <button type="submit" class="btn btn-primary float-end" onclick="pay();">Payer</button>
            </div>
            <div class="paidCB">
              @include('modal.cb')
            </div>

          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      function selectTypePaid(type)
      {
        console.log(type);
        if (type == 'CB') {
          $('.paidNoCB').fadeOut();
          $('.paidCB').fadeIn();
        } else {
          $('.paidNoCB').fadeIn(0);
          $('.paidCB').fadeOut(0);
        }
      }
    </script>
