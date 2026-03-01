<style type="text/css">
	*{
		color: #313131;
		font-family: Arial, Helvetica, sans-serif;
	}
	#header{
		text-align: center;
		font-family: Arial, Helvetica, sans-serif;
		margin-top: 30px;
	}
	#container{
		font-family: Arial, Helvetica, sans-serif;
	}
	table{
		border: 2px solid;
		border-collapse: collapse;
		width: 100%;
	}
	table, th, td {
	  border: 1px solid black;
	}
	th {
	  height: 50px;
	}
	th {
	  text-align: left;
	  padding-left: 10px;
	}
	td {
	  height: 50px;
	  vertical-align: middle;
	  padding-left: 10px;
	}
	.tdNumbers {
		text-align: right;
		padding-right: 5px;
	}
	.dateTr {
		text-align: center;
	}
	.page-break {
	    page-break-after: always;
	}
	#solde {
		margin-top: 50px;
		font-weight: bold;
		font-size: 1.2em;
		width: 100%;
		text-align: right;
	}
	hr {
		margin: 30px 0px 30px 0px;
	}
	#requirePayment{
		margin-top: 30px;
		font-size: 0.9em;
	}
	#requirePayment p{
		margin:3px;
	}
</style>

<div id="header">
	@php $logoParam = \App\Models\parametre::getValue('club-logo', ''); @endphp
	@if($logoParam)
		<img src="{{ $logoParam }}" style="max-width:30%;">
	@else
		<img src="../storage/app/img/logo-pdf.png" style="max-width:30%;">
	@endif
	<h3>Extrait de compte</h3>
	<h4>{{$selectedUser->name}}</h4>
	<h4>date : {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('DD-MM-YYYY') }}</h4>
</div>
<hr>
<div id="container">
	@if (count($transactions) > 0)
	<table class="table table-striped">
	<thead>
	  <tr>
	    <th scope="col">Date</th>
	    <th scope="col">Description</th>
	    <th scope="col">Montant</th>
	    <th scope="col">Solde</th>
	  </tr>
	</thead>
	<tbody>
	  @foreach ($transactions as $transaction)
	      <tr>
	        <th scope="row" class="dateTr">
	          {{ $transaction['time'] }}
	        </th>
	        <td>{{ $transaction['name'] }}
	        @if($transaction['observation'] != '')
	          <br><small style="font-size: 70%;"><i>{{ $transaction['observation'] }}</i></small>
	        @endif
	        </td>
	        <td class="tdNumbers">{{ $transaction['value'] }}€</td>
	        <td class="tdNumbers 
 	        @if($transaction['solde']<0)
	          table-danger 
	        @endif
	        "
	         >{{ $transaction['solde'] }}€</td>
	      </tr>
	  @endforeach
	</tbody>
	</table>
	@endif
</div>
<hr>
@isset($transaction['solde'])
<div id="solde">
	Le solde de votre compte est de : {{ $transaction['solde'] }}€
</div>

<div id="requirePayment">
	<p><b>Pour approvisionner votre compte :</b></p>
	<p> - Par virement en indiquant votre nom dans le libellé du virement.<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><small>IBAN : FR76 1333 5004 0108 9253 9002 919</small></i></p>
	<p> - Par carte bancaire : <i><small>compte.cvvt.fr</small></i></p>
</div>
@endisset