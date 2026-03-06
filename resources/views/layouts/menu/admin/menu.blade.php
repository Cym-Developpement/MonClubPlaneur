<hr>
<a class="dropdown-item" href="/manuel">
  <i class="fas fa-question-circle me-2"></i>Gestion club / Aide
</a>

@can('admin:saisie')
<a class="dropdown-item" href="/saisie">
    <i class="fas fa-pencil-alt me-2"></i>Saisie
</a>
<a class="dropdown-item" href="/importGesasso">
    <i class="fas fa-file-import me-2"></i>Import GESASSO
</a>
<a class="dropdown-item" href="/planchesOgn">
    <i class="fas fa-clipboard-list me-2"></i>Planches à saisir ({{ App\Models\ognFlight::getNbNotImported() }})
</a>
<a class="dropdown-item" href="/saisiePeriodique">
    <i class="fas fa-calendar-alt me-2"></i>Saisie Périodique
</a>
@endcan

@can('admin:users')
<a class="dropdown-item" href="#"
   data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="fas fa-user-plus me-2"></i>Nouvelle Utilisateur
</a>
<a class="dropdown-item" href="/usersList">
    <i class="fas fa-users me-2"></i>Liste des utilisateurs
</a>
@endcan

@can('admin:transactions')
<a class="dropdown-item" href="/validTransactions">
    <i class="fas fa-check-circle me-2"></i>Transactions a valider &nbsp;&nbsp;
    @if(App\Models\transaction::getNotValidNumber() > 0)<span class="badge badge-primary">{{ App\Models\transaction::getNotValidNumber() }}</span>@endif
</a>
@endcan

@can('admin:flights')
<a class="dropdown-item" href="/route?filterID=0&year={{ date('Y') }}">
    <i class="fas fa-plane me-2"></i>Carnet de route Appareil
</a>
<a class="dropdown-item" href="/vol?filterID=0&year={{ date('Y') }}">
    <i class="fas fa-book-open me-2"></i>Carnet de vol Pilote
</a>
<a class="dropdown-item" href="/towing">
    <i class="fas fa-link me-2"></i>Remorquage
</a>
@endcan

@can('admin:data')
<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#controlData" data-bs-backdrop="static" onclick="controlBDDData();">
    <i class="fas fa-database me-2"></i>Controle des données
</a>
@endcan

@can('admin:tarifs')
<a class="dropdown-item" href="/tarifs">
    <i class="fas fa-tags me-2"></i>Tarifs
</a>
@endcan

@can('admin:instruction')
<a class="dropdown-item" href="/instruction">
    <i class="fas fa-graduation-cap me-2"></i>Instruction
</a>
@endcan

@can('admin:backups')
<a class="dropdown-item" href="/backups">
    <i class="fas fa-archive me-2"></i>Sauvegardes
</a>
@endcan

@can('admin:audit')
<a class="dropdown-item" href="/audit">
    <i class="fas fa-shield-alt me-2"></i>Journal d'audit
</a>
@endcan

@can('admin:vi')
<a class="dropdown-item" href="{{ route('admin.vi.index') }}">
    <i class="fas fa-plane me-2"></i>Vols d'initiation
</a>
@endcan

@can('admin:super')
<a class="dropdown-item" href="/admin/parametres">
    <i class="fas fa-cog me-2"></i>Paramètres du club
</a>
@endcan

<hr>
<a class="dropdown-item">
    <small>
      <i>
        <b>Informations Système : </b><br>
        lARAVEL : {{ app()->version() }}<br>
        PHP : {{ phpversion() }}
      </i>
    </small>
</a>
