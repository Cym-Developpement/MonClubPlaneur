@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span><i class="fas fa-shield-alt me-2"></i>Journal d'audit</span>
                    <form method="get" action="/audit" class="d-flex align-items-center gap-2 mb-0">
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
                        <a href="/audit" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                    </form>
                </div>

                <div class="card-body p-0">
                    @if($search !== '')
                        <div class="px-3 pt-2 pb-1 text-muted small border-bottom">
                            {{ count($lines) }} résultat(s) pour « {{ $search }} »
                        </div>
                    @endif

                    @if(count($lines) === 0)
                        <p class="text-muted p-3 mb-0">
                            {{ $search !== '' ? 'Aucun résultat.' : 'Aucune entrée pour cette journée.' }}
                        </p>
                    @else
                        <table class="table table-sm table-hover mb-0 font-monospace">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:170px;">Date / Heure</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $entry)
                                <tr>
                                    <td class="text-muted small text-nowrap">{{ $entry['time'] }}</td>
                                    <td class="small">
                                        @if($search !== '')
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
