<li class="nav-item dropdown">

                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

                                    <a class="dropdown-item" href="/home"
                                       onclick="">
                                        Mon Compte Pilote
                                    </a>
                                    <a class="dropdown-item" href="/carnet">
                                        Mon carnet de vol
                                    </a>
                                    <a class="dropdown-item" href="/planches">
                                        Planches de vol
                                    </a>
                                    <a class="dropdown-item" href="{{ route('todolist.index') }}">
                                        <i class="fas fa-tasks me-2"></i>Todolist partagée
                                    </a>

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Se déconnecter') }}
                                    </a>

                                    
                                    @can('admin')
                                    @include('layouts.menu.admin.menu')
                                    @endcan

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>