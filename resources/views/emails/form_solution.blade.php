<body>

<p>Dear Team,</p>

<p>A customer requested to schedule a meeting. </p>

<div>
<p>Company Name: {{$data['company_name']}}
<br>
Email : {{$data['email']}}
<br>
Contact Name : {{$data['contact_name']}}
<br>
Phone Number : {{$data['phone_number']}}
<br>
Solution : {{$data['solution']}}
<br>
Solution Detail : 
@if($data['solution'] =='Software') 
{{$data['solution_detail']}}
<br>
Type of Project : {{$data['type_project']}}
<br>
Budget : Rp {{number_format($data['budget_from'],2)}} - Rp {{number_format($data['budget_to'],2)}}
@endif
<br>

@if($data['solution'] =='Talents')  
<table style="border:1px solid black;border-collapse:collapse;">
    <tr>
        <th style="border:1px solid black;">Kind Talent</th>
        <th style="border:1px solid black;">List Produk</th>
        <th style="border:1px solid black;">Level Employee</th>
        <th style="border:1px solid black;">Many Talent</th>
        <th style="border:1px solid black;">Urgently</th>
        <th style="border:1px solid black;">Time Used</th>
        <th style="border:1px solid black;">Open Remote</th>
        <th style="border:1px solid black;">Maximum Budget</th>
        <th style="border:1px solid black;">Details</th>
    </tr>
@foreach($data['solution_detail'] as $detail)
    <tr>
        <td style="border:1px solid black;">{{$detail['kind_of_product']}}</td>
        <td style="border:1px solid black;">{{$detail['list_product']}}</td>
        <td style="border:1px solid black;">{{$detail['level_employee']}}</td>
        <td style="border:1px solid black;">{{$detail['many_product']}}</td>
        <td style="border:1px solid black;">{{$detail['urgently']}}</td>
        <td style="border:1px solid black;">{{$detail['time_used']}} month</td>
        <td style="border:1px solid black;">{{$detail['open_remote']}}</td>
        <td style="border:1px solid black;">Rp {{number_format($detail['maximum_budget'],2) }}</td>
        <td style="border:1px solid black;">{{$detail['details']}}</td>
    </tr>
@endforeach

</table>
@endif

@if($data['solution'] =='Hardware')  
<table style="border:1px solid black;border-collapse:collapse;">
    <tr>
        <th style="border:1px solid black;">Kind Product</th>
        <th style="border:1px solid black;">List Produk</th>
        <th style="border:1px solid black;">Many Product</th>
        <th style="border:1px solid black;">Urgently</th>
        <th style="border:1px solid black;">Time Used</th>
        <th style="border:1px solid black;">Maximum Budget</th>
        <th style="border:1px solid black;">Details</th>
    </tr>
@foreach($data['solution_detail'] as $detail)
    <tr>
        <td style="border:1px solid black;">{{$detail['kind_of_product']}}</td>
        <td style="border:1px solid black;">{{$detail['list_product']}}</td>
        <td style="border:1px solid black;">{{$detail['many_product']}}</td>
        <td style="border:1px solid black;">{{$detail['urgently']}}</td>
        <td style="border:1px solid black;">{{$detail['time_used']}} month</td>
        <td style="border:1px solid black;">Rp {{number_format($detail['maximum_budget'],2) }}</td>
        <td style="border:1px solid black;">{{$detail['details']}}</td>
    </tr>
@endforeach

</table>
@endif

<br>
Meeting Schedule : {{$data['meeting_schedule']}}
</p>
<p>
Kindly reach out to the customer to confirm the meeting within 24 hours.
</p>
<p>
<br>
Thank you.
</p>
</div>
</body>