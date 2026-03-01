@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-shield-alt me-2"></i>Journal d'audit</span>
                    <form method="get" action="/audit" class="d-flex align-items-center gap-2 mb-0">
                        <label for="dateSelect" class="mb-0 text-muted small">Jour :</label>
                        <select name="date" id="dateSelect" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            @forelse($dates as $date)
                                <option value="{{ $date }}" @if($date === $selectedDate) selected @endif>{{ $date }}</option>
                            @empty
                                <option>Aucun log</option>
                            @endforelse
                        </select>
                    </form>
                </div>

                <div class="card-body p-0">
                    @if(count($lines) === 0)
                        <p class="text-muted p-3 mb-0">Aucune entrée pour cette journée.</p>
                    @else
                        <table class="table table-sm table-hover mb-0 font-monospace">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:160px;">Heure</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lines as $entry)
                                <tr>
                                    <td class="text-muted small">{{ $entry['time'] }}</td>
                                    <td class="small">{{ $entry['message'] }}</td>
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
