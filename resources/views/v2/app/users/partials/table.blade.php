@php
    $headings = $usersV2Config['headings'] ?? [];
@endphp

<div class="v2-users-table-wrap">
    <table id="usersV2Table" class="v2-users-table table table-condensed">
        <thead>
        <tr>
            @foreach($headings as $heading)
                <th>{{ $heading }}</th>
            @endforeach
        </tr>
        </thead>
    </table>
</div>
