<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
    <script src="js/jquery.inputmask.min.js"></script>
    <script src="js/bindings/inputmask.binding.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style type="text/css">
      .datepicker.active {
        z-index: 100000;
      }
      body {
        background-image: url('/img/back.jpg');
        background-repeat: no-repeat;
        background-attachment: fixed;
      }

    </style>
    <style type="text/css">
                      .existFlight {
                        text-decoration:line-through;
                        color: red;
                      }

    @media (min-width: 1600px) {
        .container {
            max-width: 1540px;
        }
    }
                    </style>
    <script type="text/javascript">
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
    </script>
    <style>
      .HaPay {
        width: fit-content;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
      }

      .HaPay * {
        font-family: "Open Sans", "Trebuchet MS", "Lucida Sans Unicode",
          "Lucida Grande", "Lucida Sans", Arial, sans-serif;
        transition: all 0.3s ease-out;
      }

      .HaPayButton {
        align-items: stretch;
        -webkit-box-pack: stretch;
        -ms-flex-pack: stretch;
        background: none;
        border: none;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        padding: 0;
        border-radius: 8px;
      }

      .HaPayButton:hover {
        cursor: pointer;
      }

      .HaPayButton:not(:disabled):focus {
        box-shadow: 0 0 0 0.25rem rgba(73, 211, 138, 0.25);
        -webkit-box-shadow: 0 0 0 0.25rem rgba(73, 211, 138, 0.25);
      }

      .HaPayButton:not(:disabled):hover .HaPayButtonLabel,
      .HaPayButton:not(:disabled):focus .HaPayButtonLabel {
        background-color: #483dbe;
      }

      .HaPayButton:not(:disabled):hover .HaPayButtonLogo,
      .HaPayButton:not(:disabled):focus .HaPayButtonLogo,
      .HaPayButton:not(:disabled):hover .HaPayButtonLabel,
      .HaPayButton:not(:disabled):focus .HaPayButtonLabel {
        border: 1px solid #483dbe;
      }

      .HaPayButton:disabled {
        cursor: not-allowed;
      }

      .HaPayButton:disabled .HaPayButtonLogo,
      .HaPayButton:disabled .HaPayButtonLabel {
        border: 1px solid #d1d6de;
      }

      .HaPayButtonLogo {
        background-color: #ffffff;
        border: 1px solid #4c40cf;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
        padding: 10px 16px;
        width: 30px;
        box-sizing: content-box!important;
      }

      .HaPayButtonLabel {
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: space-between;
        column-gap: 5px;
        background-color: #4c40cf;
        border: 1px solid #4c40cf;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        padding: 0 16px;
      }

      .HaPayButton:disabled .HaPayButtonLabel {
        background-color: #d1d6de;
        color: #505870;
      }

      .HaPaySecured {
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: space-between;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        column-gap: 5px;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #2e2f5e;
      }

      .HaPay svg {
        fill: currentColor;
      }

      /* Styles pour les autres éléments HelloAsso */
      .helloasso-amount-display {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
      }

      .helloasso-security-info {
        background: #e8f5e8;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #c3e6c3;
      }

      .helloasso-security-info i {
        font-size: 1.5rem;
        margin-bottom: 5px;
      }

      /* Styles pour les champs en lecture seule */
      .form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
        cursor: not-allowed;
      }

      .form-control[readonly]:focus {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        box-shadow: none;
        color: #6c757d;
      }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->

                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('tarifs-public') }}">
                                    <i class="fas fa-euro-sign"></i> Tarifs
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('public.payment') }}">
                                    <i class="fas fa-heart text-danger"></i> Faire un don
                                </a>
                            </li>
                            <!--
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                          -->
                        @else
                            @include('layouts.menu')
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    @can('admin')
       <!-- Modal add user -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Ajouter un utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                    <label for="addUserMailInput">Adresse e-mail</label>
                    <input type="email" class="form-control" id="addUserMailInput" aria-describedby="emailHelp" placeholder="email">
                  </div>
                  <div class="mb-3">
                    <label for="addUserNameInput">Nom Complet</label>
                    <input type="text" class="form-control" id="addUserNameInput" placeholder="Nom Prénom">
                  </div>
                  <div class="mb-3">
                    <label for="addUserLicNumberInput">Numéro Licence</label>
                    <input type="text" class="form-control" id="addUserLicNumberInput" placeholder="XXXXX">
                  </div>
                  <div class="alert alert-danger" role="alert" id="addUserHelpName" style="display: none;">
                      Merci de remplir tout les champs ci-dessus!
                  </div>
                  <hr>
                  <h3>Statut du membre</h3>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="addUserStateaccomp">
                    <label class="form-check-label" for="addUserStateaccomp">Licence associative</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="addUserStateeleve">
                    <label class="form-check-label" for="addUserStateeleve">Elève</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="addUserStatepilote">
                    <label class="form-check-label" for="addUserStatepilote">Pilote</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="addUserStateinstructeurplaneur">
                    <label class="form-check-label" for="addUserStateinstructeurplaneur">Instructeur (Planeur)</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="addUserStateinstructeurULM">
                    <label class="form-check-label" for="addUserStateinstructeurULM">Instructeur (ULM)</label>
                  </div>
                  <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="addUserStateremorqueur">
                    <label class="form-check-label" for="addUserStateremorqueur">Remorqueur</label>
                  </div>
                  <div class="alert alert-danger" role="alert" id="addUserHelpState" style="display: none;">
                      Merci de selectionner au moins une case!
                  </div>
                  <div class="alert alert-danger" role="alert" id="addUserHelpServerError" style="display: none;"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveNewUser();">Enregistrer</button>
              </div>
            </div>
          </div>
        </div>

      <!-- Modal Control Data -->
      <div class="modal fade" id="controlData" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" >Controle & mise à jour de la base de données </h5>
            </div>
            <div class="modal-body">
              <div id="controlDataResult">
                <div class="text-center">
                  <h5>Controle de la base de données en cours ...</h5>
                  <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeControlDataModal" onclick="window.location = window.location.href.split('#')[0];" disabled>Fermer</button>
            </div>
          </div>
        </div>
      </div>
    @endcan

    <!-- Modal HelloAsso-->
    <div class="modal fade" id="helloAssoModal" tabindex="-1" role="dialog" aria-labelledby="helloAssoModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="helloAssoModalLabel">Approvisionner mon compte par carte bancaire</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <!--<iframe id="haWidget" src="https://www.helloasso.com/associations/cvvt/paiements/compte-pilote/widget" style="width: 350px; height: 450px; border: none;" scrolling="auto" ></iframe>-->
          </div>
        </div>
      </div>
    </div>

    @if(request()->get('iframe') != '1')
    <footer class="text-center text-muted py-3 mt-4 border-top small" style="background-color: rgba(255,255,255,0.75);">
        {{ config('app.name') }} &mdash; &copy; {{ date('Y') }}
        @if($gitCommitMessage)
            &mdash; {{ $gitCommitMessage }}
            @if($gitCommitDate)
                <span class="text-muted">({{ $gitCommitDate }})</span>
            @endif
        @endif
    </footer>
    @endif

    <script src="https://kit.fontawesome.com/9724d9dada.js" crossorigin="anonymous"></script>
    <script src="js/jquery.mask.js"></script>
    <script src="js/function.js"></script>
    <script type="text/javascript">
       document.querySelectorAll('input[type="file"].form-control').forEach(function(input) {
          input.addEventListener('change', function() {
              var fileData = this.value.split('\\');
              var fileName = fileData[fileData.length - 1];
              var label = this.nextElementSibling;
              if (label) label.textContent = fileName;
          });
       });
    </script>
    @stack('scripts')

</body>
</html>
