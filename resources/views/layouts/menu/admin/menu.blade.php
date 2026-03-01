<hr>
<a class="dropdown-item" href="/manuel">
  Gestion club / Aide
</a>
<a class="dropdown-item" href="/saisie"
   onclick="">
    Saisie
</a>
<a class="dropdown-item" href="/importGesasso"
   onclick="">
    Import GESASSO
</a>
<a class="dropdown-item" href="/planchesOgn"
   onclick="">
    Planches à saisir ({{ App\Models\ognFlight::getNbNotImported() }})
</a>
<a class="dropdown-item" href="/saisiePeriodique"
   onclick="">
    Saisie Périodique
</a>
<a class="dropdown-item" href="#"
   data-bs-toggle="modal" data-bs-target="#addUserModal">
    Nouvelle Utilisateur
</a>
<a class="dropdown-item" href="/usersList">
    Liste des utilisateurs
</a>
<a class="dropdown-item" href="/validTransactions">
    Transactions a valider &nbsp;&nbsp;
    @if(App\Models\transaction::getNotValidNumber() > 0)<span class="badge badge-primary">{{ App\Models\transaction::getNotValidNumber() }}</span>@endif
</a>
<a class="dropdown-item" href="/route?filterID=0&year={{ date('Y') }}">
    Carnet de route Appareil
</a>
<a class="dropdown-item" href="/vol?filterID=0&year={{ date('Y') }}">
    Carnet de vol Pilote
</a>
<a class="dropdown-item" href="/towing">
    Remorquage
</a>
<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#controlData" data-bs-backdrop="static" onclick="controlBDDData();">
    Controle des données
</a>

<a class="dropdown-item" href="/tarifs" >
    Tarifs
</a>

<a class="dropdown-item" href="/instruction" >
    Instruction
</a>

<a class="dropdown-item" href="/backups">
    Sauvegardes
</a>

<hr>
<a class="dropdown-item" >
    <small>
      <i>
        <b>Informations Système : </b><br>
        lARAVEL : {{ app()->version() }}<br>
        PHP : {{ phpversion() }}
      </i>
    </small>
</a>
