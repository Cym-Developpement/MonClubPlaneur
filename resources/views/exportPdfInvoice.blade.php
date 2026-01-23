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
	#total {
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
	<img src="../storage/app/img/logo-pdf.png">
	<h3>Facture</h3>
	<h4>{{$selectedUser->name}}</h4>
	<h4>Période : {{$year}}</h4>
	<h4>Date d'émission : 
	@php
		echo date('d-m-Y');
	@endphp
	</h4>
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
	  </tr>
	</thead>
	<tbody>
	  @php
	    $totalAmount = 0;
	  @endphp
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
	        <td class="tdNumbers">{{ number_format(abs(floatval(str_replace(',', '.', $transaction['value']))), 2) }}€</td>
	      </tr>
	      @php
	        $totalAmount += abs(floatval(str_replace(',', '.', $transaction['value'])));
	      @endphp
	  @endforeach
	</tbody>
	</table>
	@endif
</div>
<hr>
@if (count($transactions) > 0)
<div id="total">
	Montant total à payer : <span>{{ number_format($totalAmount, 2) }}€</span>
	<br><small style="font-style: italic; font-size: 0.7em; 
	@if($currentBalance < 0)
		color: #d32f2f;
	@else
		color: #666;
	@endif
	">Votre compte comporte un solde 
	@if($currentBalance >= 0)
		créditeur de {{ number_format($currentBalance, 2) }}€
	@else
		débiteur de {{ number_format(abs($currentBalance), 2) }}€
	@endif
	</small>
</div>

<div id="requirePayment">
	<p><b>Pour régler cette facture : </b></p>
	<p> - Par Chèques à l'ordre du CVVT.</p>
	<p> - Par virement en indiquant votre nom dans le libélé du virement.<br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i><small>IBAN  : FR76 1333 5004 0108 9253 9002 919 </small></i></p>
	<p> - Par carte bancaire : <a href="https://compte.cvvt.fr/don" target="_blank">https://compte.cvvt.fr/don</a></p>
	<p><b>Merci de votre confiance.</b></p>
</div>
@else
<div id="total">
	<p>Aucune transaction négative trouvée pour cette période.</p>
</div>
@endif
