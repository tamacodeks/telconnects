<!DOCTYPE>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<title>Daily Order(s) Payment Report</title>
<head>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
        }

        table, tbody#alter tr:nth-child(even) {
            background-color: #eee;
        }

        table, tbody#alter tr:nth-child(odd) {
            background-color: #fff;
        }

        table#alter th {
            color: white;
            background-color: gray;
        }
    </style>
</head>
<body>
<h3>Hello admin.,</h3>

<p>Retailers - Daily Payment Report for the day {{ date('d-m-Y') }} .</p>
<br>
<table id="alter">
    <thead>
    <tr>
        <th>{{ trans('common.order_tbl_sl') }}</th>
        <th>{{ trans('common.users') }}</th>
        <th>Total Transaction Amount</th>
        <th>No. Of Transactions</th>

    </tr>
    </thead>
    <tbody>
    @if( $user_data[0]['total_transaction'])
        <tr>
            <td>1</td>
            <td>DEMAT PRO</td>
            <td>{{ $user_data[0]['total_amount'] }}</td>
            <td>{{ $user_data[0]['total_transaction'] }}</td>
        </tr>
        @else
        <tr>
            <td colspan="4" class="text-center">{{ trans('common.payment_no_payments') }}</td>
        </tr>
        @endif
    </tbody>
</table>
<br>
<br><br>
<p>Report By.,</p>
<h4>{{ config('app.name')  }} CRON</h4>
</body>
</html>
