<!DOCTYPE html>
<html>
<head>
	<style type="text/css">

		.page-break {
		    page-break-after: always;
		}
		table, tr, td, th, tbody, thead, tfoot {
		    page-break-inside: avoid !important;
		}
		.table-full{
			width: 100%;
			border-collapse: collapse;
		}
		th{
			height: 20px;
		}
		td{
			height: 15px;
		}
		.table-utama{
			border-bottom: 0px solid #fff;
			align-content: center;
			text-align: center;
		}
		.table-content > thead > tr > th{
			font-size: 18px;
			text-align: center;
		}
		.table-content-heder{
			font-size: 18px;
			text-align: left;
			height: 20px;
			font-weight: bold;
		}
		.table-content > thead > tr > th,
		.table-content > tbody > tr > td
		{
			border: 2px solid #000000;
			font-size: 13px;
			padding: 2px;
		}
		.table-tanda-tangan{
			border-bottom: 0px solid #fff;
			width: 100%;
			align-content: center;
			text-align: center;
			font-size: 14px;
		}
	</style>
</head>
<body>
<table class="margin-kertas table-utama table-full">
	<tr>
		<td><strong> {{ $judul }} <br><br></strong></td>
	</tr>
    @foreach( $ticket->toArray() as $key => $value )
	<tr>
		<td> {{ $value }} </td>
	</tr>
    @endforeach
	
</table>
</body>
</html>