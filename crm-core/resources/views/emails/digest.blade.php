<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 8px 8px;
        }

        .card {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 13px;
        }

        th {
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #e5e7eb;
            color: #6b7280;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 style="margin:0; font-size: 24px;">Daily Performance Digest</h1>
            <p style="margin:5px 0 0; opacity: 0.9;">{{ now()->format('l, d M Y') }}</p>
        </div>

        <div class="content">
            <div class="card">
                <h2 style="margin-top:0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Company
                    Overview</h2>
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value">INR {{ number_format($data['total_revenue']) }}</div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $data['calls_made'] }}</div>
                        <div class="stat-label">Calls Made</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="margin-top:0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Top
                    Performers</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th style="text-align: right;">Revenue</th>
                            <th style="text-align: right;">Calls</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_agents'] as $agent)
                            <tr>
                                <td>{{ $agent->name }}</td>
                                <td style="text-align: right; font-weight: bold;">INR {{ number_format($agent->revenue) }}</td>
                                <td style="text-align: right;">{{ $agent->calls_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; font-style: italic;">No activity recorded today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <p>This report was generated automatically by StockIdea CRM.</p>
            </div>
        </div>
    </div>
</body>

</html>
