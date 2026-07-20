Hello Manager {{ isset($manager_name) ?  $manager_name : "" }}!,<br><br>

For {{ isset($retailer_name) ? $retailer_name : "" }} Having Less Daily Limit,Please Update Soon.<br><br>

<table border="1">
    <tr>
        <th>Retailer Name</th>
        <th>Manager</th>
        <th>Current Balance</th>
        <th>Total Limit</th>
        <th>Current Limit</th>
    </tr>
    <tr>
        <td>{{ isset($retailer_name) ? $retailer_name : "" }}</td>
        <td>{{ isset($manager_name) ?  $manager_name : "" }}</td>
        <td>{!! isset($current_bal) ? $current_bal : "" !!}</td>
        <td>{!! isset($total_limit) ? $total_limit : "" !!}</td>
        <td>{!! isset($current_limit) ? $current_limit : "" !!}</td>
    </tr>
</table>

<br><br>
Regards.,<br>
Team DEMAT PRO.


