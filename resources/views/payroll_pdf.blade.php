@php use NumberFormatter; $fmt = new NumberFormatter('en_PK', NumberFormatter::CURRENCY); @endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Salary Slips</title>
  <style>
    body{font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:12px}
    table{width:100%;border-collapse:collapse}
    th,td{border:1px solid #ddd;padding:6px}
    th{background:#f5f5f5}
  </style>
</head>
<body>
  <h3>Salary Slips</h3>
  <p>Total Payroll: PKR {{ number_format($total ?? 0) }}</p>
  <table>
    <thead>
      <tr>
        <th>Worker</th>
        <th>Month</th>
        <th class="text-end">Basic</th>
        <th class="text-end">OT Hours</th>
        <th class="text-end">Deductions</th>
        <th class="text-end">Net</th>
      </tr>
    </thead>
    <tbody>
      @foreach($payrolls as $p)
      <tr>
        <td>{{ optional(optional($p->worker)->user)->name ?? ('#'.$p->worker_id) }}</td>
        <td>{{ $p->month }}</td>
        <td class="text-end">{{ number_format($p->basic_salary) }}</td>
        <td class="text-end">{{ $p->overtime_hours }}</td>
        <td class="text-end">{{ $p->deductions }}</td>
        <td class="text-end">{{ number_format($p->net_salary) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>