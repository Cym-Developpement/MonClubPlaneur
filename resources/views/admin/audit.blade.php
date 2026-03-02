@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-shield-alt me-2"></i>Journal d'audit</span>
                    <div class="d-flex align-items-center gap-2">

                        {{-- Sélecteur de type de log --}}
                        <form method="get" action="/audit" id="typeForm" class="mb-0">
                            <select name="type" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                <option value="audit"  @if($type === 'audit')  selected @endif>Journal d'audit</option>
                                @can('admin:super')
                                <option value="update" @if($type === 'update') selected @endif>Mise à jour</option>
                                <option value="error"  @if($type === 'error')  selected @endif>Erreurs Laravel</option>
                                @endcan
                            </select>
                        </form>

                        {{-- Sélecteur de date (audit uniquement) --}}
                        @if($type === 'audit')
                        <form method="get" action="/audit" class="d-flex align-items-center gap-2 mb-0">
                            <input type="hidden" name="type" value="audit">
                            <label for="dateSelect" class="mb-0 text-muted small">Jour :</label>
                            <select name="date" id="dateSelect" class="form-select form-select-sm" style="width:auto;"
                                    onchange="if(!document.getElementById('searchInput').value) this.form.submit()">
                                @forelse($dates as $date)
                                    <option value="{{ $date }}" @if($date === $selectedDate) selected @endif>{{ $date }}</option>
                                @empty
                                    <option>Aucun log</option>
                                @endforelse
                            </select>

                            <input type="text" name="search" id="searchInput"
                                   class="form-control form-control-sm"
                                   style="width:220px;"
                                   placeholder="Rechercher dans tous les logs…"
                                   value="{{ $search }}">

                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>

                            @if($search !== '')
                            <a href="/audit?type=audit" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </form>
                        @endif

                    </div>
                </div>

                <div class="card-body p-0">
                    @if($type === 'audit' && $search !== '')
                        <div class="px-3 pt-2 pb-1 text-muted small border-bottom">
                            {{ count($lines) }} résultat(s) pour « {{ $search }} »
                        </div>
                    @endif

                    @if(count($lines) === 0)
                        <p class="text-muted p-3 mb-0">Aucune entrée.</p>
                    @else
                        <table class="table table-sm table-hover mb-0 font-monospace">
                            <thead class="table-light">
                                <tr>
                                    @if($type !== 'update')
                                    <th style="width:170px;">Date / Heure</th>
                                    @endif
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $entry)
                                <tr @if(isset($entry['level']) && in_array($entry['level'], ['error','critical','alert','emergency'])) class="table-danger" @elseif(isset($entry['level']) && $entry['level'] === 'warning') class="table-warning" @endif>
                                    @if($type !== 'update')
                                    <td class="text-muted small text-nowrap">{{ $entry['time'] }}</td>
                                    @endif
                                    <td class="small">
                                        @if($type === 'audit' && $search !== '')
                                            {!! preg_replace('/(' . preg_quote(e($search), '/') . ')/i', '<mark>$1</mark>', e($entry['message'])) !!}
                                        @else
                                            {{ $entry['message'] }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
