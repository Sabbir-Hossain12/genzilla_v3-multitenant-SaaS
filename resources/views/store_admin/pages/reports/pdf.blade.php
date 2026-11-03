<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportData['title'] }}</title>
    <style>
        @page {
            margin: 25px 25px 35px 25px;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #2563eb;
            margin: 2px 0 0 0;
        }
        .meta-text {
            font-size: 8px;
            color: #64748b;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 15px;
        }
        .summary-table {
            width: 100%;
        }
        .summary-cell {
            vertical-align: top;
            padding: 2px 6px;
        }
        .summary-label {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            display: block;
        }
        .summary-val {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        .data-table td {
            font-size: 8px;
            padding: 5px;
            border: 1px solid #e2e8f0;
            color: #334155;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            font-size: 7.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-danger { background-color: #fee2e2; color: #b91c1c; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <h1 class="store-name">{{ $tenant->name ?? 'Pharmacy Store' }}</h1>
                <div class="report-title">{{ $reportData['title'] }}</div>
                <div class="meta-text" style="margin-top: 3px;">
                    Domain: {{ $tenant->domain ?? 'N/A' }} &bull; Store Admin Business Intelligence
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="meta-text"><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                <div class="meta-text"><strong>Filtered Period:</strong> {{ $reportData['filters']['start_date'] }} &mdash; {{ $reportData['filters']['end_date'] }}</div>
                <div class="meta-text"><strong>Total Matched:</strong> {{ $reportData['total_count'] }} records</div>
            </td>
        </tr>
    </table>

    <!-- Executive Summary Box -->
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                @foreach(array_slice($reportData['summary'], 0, 5) as $lbl => $val)
                    <td class="summary-cell">
                        <span class="summary-label">{{ ucwords(str_replace('_', ' ', $lbl)) }}</span>
                        <span class="summary-val">{{ $val }}</span>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                @foreach($reportData['headers'] as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['rows'] as $row)
                <tr>
                    @foreach($row as $idx => $cell)
                        <td class="{{ is_numeric(str_replace([',', ' '], '', $cell)) && !str_starts_with($cell, '#') ? 'text-right' : '' }}">
                            @if(is_string($cell) && in_array(strtolower($cell), ['paid', 'successful', 'approved', 'delivered', 'in stock']))
                                <span class="badge badge-success">{{ $cell }}</span>
                            @elseif(is_string($cell) && in_array(strtolower($cell), ['pending', 'unpaid', 'low stock', 'pending review']))
                                <span class="badge badge-warning">{{ $cell }}</span>
                            @elseif(is_string($cell) && in_array(strtolower($cell), ['cancelled', 'failed', 'refunded', 'rejected', 'out of stock', 'spam']))
                                <span class="badge badge-danger">{{ $cell }}</span>
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($reportData['headers']) }}" class="text-center" style="padding: 20px;">
                        No records found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">GenZilla Multi-Tenant SaaS &bull; Confidential Merchant Report</td>
                <td style="text-align: right;">{{ $tenant->name ?? 'Store' }} &bull; Page 1</td>
            </tr>
        </table>
    </div>

</body>
</html>
