<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>Approval Request</title>
	
	<style>
	a {
		text-decoration: none;
	}

	.invoice-box{
		max-width:800px;
		margin:auto;
		padding:30px;
		border:1px solid #eee;
		box-shadow:0 0 10px rgba(0, 0, 0, .15);
		font-size:16px;
		line-height:24px;
		font-family:'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
		color:#555;
	}
	
	.invoice-box table{
		width:100%;
		line-height:inherit;
		text-align:left;
	}
	
	.invoice-box table td{
		padding:5px;
		vertical-align:top;
	}
	
	.invoice-box table tr td:nth-child(2){
		text-align:right;
	}
	
	.invoice-box table tr.top table td{
		padding-bottom:20px;
	}
	
	.invoice-box table tr.top table td.title{
		font-size:45px;
		line-height:45px;
		color:#333;
	}
	
	.invoice-box table tr.information table td{
		padding-bottom:40px;
	}
	
	.invoice-box table tr.heading td{
		background:#eee;
		border-bottom:1px solid #ddd;
		font-weight:bold;
	}
	
	.invoice-box table tr.details td{
		padding-bottom:20px;
	}
	
	.invoice-box table tr.item td{
		border-bottom:1px solid #eee;
	}
	
	.invoice-box table tr.item.last td{
		border-bottom:none;
	}
	
	.invoice-box table tr.total td{
		border-top:2px solid #eee;
		font-weight:bold;
	}

	.btn {
		display: inline-block;
		font-weight: 300;
		line-height: 1;
		text-align: center;
		white-space: nowrap;
		vertical-align: middle;
		cursor: pointer;
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
		border: 1px solid transparent;
		padding: .5rem 1rem;
		font-size: 1rem;
		border-radius: .25rem;
	}

	.btn-check {
		color: #fff;
		background-color: #000;
		border-color: #000;
	}

	.btn-success {
		color: #fff;
		background-color: #5cb85c;
		border-color: #5cb85c;
	}

	.btn-danger {
		color: #fff;
		background-color: #d9534f;
		border-color: #d9534f;
	}

	.text-right {
		text-align: right;
	}
	
	@media only screen and (max-width: 600px) {
		.invoice-box table tr.top table td{
			width:100%;
			display:block;
			text-align:center;
		}
		
		.invoice-box table tr.information table td{
			width:100%;
			display:block;
			text-align:center;
		}
	}
	</style>
</head>

<body>
	<div class="invoice-box">
		Hello Mr/Mrs/Ms/ {{ $approver->name }},<br/>You have an approval request for cash from <strong>{{ $requester }}</strong>. We would like to inform the details as follows :

		{{-- <table cellpadding="0" cellspacing="0" style="padding: 20px 0;">
			<tr>
				<td style="width: 20%">
					Form Number
				</td>
				<td>
					:
				</td>
				<td>
					<a href="{{ $url . '/finance/point/payment-order/'.$payment_order->id }}">{{ $payment_order->formulir->form_number}}</a>
				</td>
			</tr>
			<tr>
				<td style="width: 20%">
					Created By
				</td>
				<td>
					:
				</td>
				<td>
					{{ $payment_order->formulir->createdBy->name }}
				</td>
			</tr>
			<tr>
				<td style="width: 20%">
					Form Date
				</td>
				<td>
					:
				</td>
				<td>
					{{ \DateHelper::formatView($payment_order->formulir->form_date) }}
				</td>
			</tr>
			<tr>
				<td style="width: 20%">
					Payment To
				</td>
				<td>
					:
				</td>
				<td>
					{{ $payment_order->person->codeName }}
				</td>
			</tr>
		</table> --}}

		<table cellpadding="0" cellspacing="0" style="width: 800px;">
			<tr class="heading">
				<th style="width: 100%;">DESKRIPSI</th>
				<th style="width: 1px; white-space: nowrap;" class="text-right">RECEIVED</th>
				<th style="width: 1px; white-space: nowrap;" class="text-right">DISBURSED</th>
			</tr>
			<?php
			$total_received = 0;
			$total_disbursed = 0;
			?>
			@foreach($list_report as $report)
				@foreach($report->detail as $report_detail)
					<tr class="item">
						<td>
								Pembayaran {{ \Point\Framework\Models\Master\Coa::find($report_detail->coa_id)->name }}
								{{$report->payment_flow === 'in' ? 'dari' : 'ke' }}
								<strong>{{ ucwords($report->person->codeName) }}</strong>

								<br>

								Nomor formulir 
								<a href="{{ url('finance/point/'.$type.'/'. $report->payment_flow .'/'.$report->id) }}" @if($report->formulir->form_status == -1) style="background:red;color:white !important" @endif>{{ $report->formulir->form_number}}</a>

								<br>

								Tanggal {{ date_format_view($report->formulir->form_date) }}

								<br>

								<strong>NOTES:</strong> {{ strtoupper($report_detail->notes_detail) }}
						</td>
						<td class="text-right" style="width: 1px; white-space: nowrap;">
							@if($report->payment_flow == 'in')
								<b>{{ number_format_price($report_detail->amount) }}</b>
								<?php $total_received += $report_detail->amount; ?>
							@else
								0.00
							@endif
						</td>
						<td class="text-right" style="width: 1px; white-space: nowrap;">
							@if($report->payment_flow == 'out')
								<b>{{ number_format_price($report_detail->amount) }}</b>
								<?php $total_disbursed += $report_detail->amount; ?>
							@else
								0.00
							@endif
						</td>
					</tr>
				@endforeach
			@endforeach
			<tr class="total">
				<td></td>
				<td class="text-right" style="width: 1px; white-space: nowrap;">{{ number_format_price($total_received) }}</td>
				<td class="text-right" style="width: 1px; white-space: nowrap;">{{ number_format_price($total_disbursed) }}</td>
			</tr>
			<tr class="total">
				<td colspan="2" class="text-right">Total</td>
				<td class="text-right" style="width: 1px; white-space: nowrap;">
					{{ number_format_price($total_received - $total_disbursed) }}
				</td>
			</tr>
		
			<tr>
				<td colspan="3">
					<a href="{{ $url . '/formulir/tugasnya-pak-martien/approval/check-status/'.$token }}">
						<input type="button" class="btn btn-check" value="Check">
					</a>
					<a href="{{ $url . '/formulir/tugasnya-pak-martien/approve?token='.$token }}">
						<input type="button" class="btn btn-success" value="Approve">
					</a>
					<a href="{{ $url . '/formulir/tugasnya-pak-martien/reject?token='.$token }}">
						<input type="button" class="btn btn-danger" value="Reject">
					</a>
				</td>
			</tr>
		</table>
	</div>
</body>
</html>
