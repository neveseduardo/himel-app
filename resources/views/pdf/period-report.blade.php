<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 20mm 30mm 20mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #374151;
            line-height: 1.5;
            background: #ffffff;
        }

        /* Header */
        .header {
            padding-bottom: 14px;
            margin-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }

        .logo-placeholder {
            width: 44px;
            height: 44px;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            line-height: 44px;
            font-size: 7pt;
            font-weight: bold;
            color: #9ca3af;
            letter-spacing: 1px;
        }

        .header-title {
            font-size: 15pt;
            font-weight: bold;
            color: #111827;
        }

        .header-subtitle {
            font-size: 8pt;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Section titles */
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #111827;
            padding: 6px 0;
            margin-top: 24px;
            margin-bottom: 8px;
            border-bottom: 2px solid #111827;
        }

        .section-title-green { border-bottom-color: #16a34a; color: #15803d; }
        .section-title-red { border-bottom-color: #dc2626; color: #b91c1c; }
        .section-title-blue { border-bottom-color: #2563eb; color: #1d4ed8; }

        /* Summary grid */
        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 4px;
        }

        .summary-cell {
            width: 25%;
            vertical-align: top;
            padding: 12px 14px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .summary-label {
            font-size: 7pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .summary-detail { width: 100%; border-collapse: collapse; }
        .summary-detail td {
            border: none;
            padding: 1px 0;
            font-size: 7.5pt;
            color: #6b7280;
        }

        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-right { text-align: right; }

        /* Data tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        .data-table thead th {
            padding: 7px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #6b7280;
            border-bottom: 1px solid #d1d5db;
            background: transparent;
        }

        .data-table thead th.text-right { text-align: right; }

        .data-table tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            color: #374151;
        }

        .data-table tbody tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .data-table .subtotal-row td {
            font-weight: bold;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            border-bottom: none;
            background: transparent !important;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-paid { background-color: #dcfce7; color: #15803d; }
        .badge-pending { background-color: #fef9c3; color: #a16207; }
        .badge-overdue { background-color: #fee2e2; color: #b91c1c; }
        .badge-installment { background-color: #ede9fe; color: #6d28d9; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 20px 0;
            color: #9ca3af;
            font-size: 8.5pt;
        }

        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    <!-- Page numbers -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 7;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->getTextWidth($text, $font, $size) / 2;
            $x = ($pdf->get_width() / 2) - ($width / 2);
            $y = $pdf->get_height() - 36;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.62, 0.65, 0.70));
        }
    </script>

    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="56">
                    <div class="logo-placeholder">LOGO</div>
                </td>
                <td style="padding-left: 10px;">
                    <div class="header-title">{{ $title }}</div>
                    <div class="header-subtitle">Gerado em {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Financial Summary -->
    <table class="summary-grid">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Entradas</div>
                <div class="summary-value text-green">{{ $formatCurrency($summary['total_inflow']) }}</div>
                <table class="summary-detail">
                    <tr><td>Manuais</td><td class="text-right">{{ $formatCurrency($summary['inflow_manual'] ?? 0) }}</td></tr>
                    <tr><td>Transferências</td><td class="text-right">{{ $formatCurrency($summary['inflow_transfer'] ?? 0) }}</td></tr>
                </table>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Saídas</div>
                <div class="summary-value text-red">{{ $formatCurrency($summary['total_outflow']) }}</div>
                <table class="summary-detail">
                    <tr><td>Desp. Fixas</td><td class="text-right">{{ $formatCurrency($summary['total_fixed_expenses'] ?? 0) }}</td></tr>
                    <tr><td>Parcelas</td><td class="text-right">{{ $formatCurrency($summary['total_credit_card_installments'] ?? 0) }}</td></tr>
                    <tr><td>Manuais</td><td class="text-right">{{ $formatCurrency($summary['total_manual'] ?? 0) }}</td></tr>
                    <tr><td>Transferências</td><td class="text-right">{{ $formatCurrency($summary['total_transfer'] ?? 0) }}</td></tr>
                </table>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Cartões</div>
                <div class="summary-value text-red">{{ $formatCurrency($cardBreakdown['grand_total']) }}</div>
                @if(count($cardBreakdown['cards']) > 0)
                    <table class="summary-detail">
                        @foreach($cardBreakdown['cards'] as $card)
                            <tr><td>{{ $card['credit_card_name'] }}</td><td class="text-right">{{ $formatCurrency($card['total']) }}</td></tr>
                        @endforeach
                    </table>
                @else
                    <div style="font-size: 7.5pt; color: #9ca3af;">Sem parcelas</div>
                @endif
            </td>
            <td class="summary-cell">
                <div class="summary-label">Saldo</div>
                <div class="summary-value {{ $summary['balance'] >= 0 ? 'text-green' : 'text-red' }}">
                    {{ $formatCurrency($summary['balance']) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Fixed Expenses -->
    <div class="section-title no-break">Despesas Fixas</div>
    @if(count($fixedExpenses['items']) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Valor</th>
                    <th>Categoria</th>
                    <th>Dia Venc.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fixedExpenses['items'] as $item)
                    <tr>
                        <td>{{ $item['description'] ?? '—' }}</td>
                        <td class="text-right text-red">{{ $formatCurrency($item['amount']) }}</td>
                        <td>{{ $item['category_name'] ?? '—' }}</td>
                        <td>{{ $item['due_day'] ?? '—' }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td>Subtotal</td>
                    <td class="text-right text-red">{{ $formatCurrency($fixedExpenses['subtotal']) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="empty-state">Nenhum registro neste período.</div>
    @endif

    <!-- Card Installments -->
    <div class="section-title section-title-blue no-break">Parcelas de Cartão</div>
    @if(count($installments['items']) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th class="text-right">Valor</th>
                    <th>Vencimento</th>
                    <th>Cartão</th>
                </tr>
            </thead>
            <tbody>
                @foreach($installments['items'] as $item)
                    <tr>
                        <td>
                            {{ $item['charge_description'] ?? '—' }}
                            @if($item['installment_number'] !== null && $item['total_installments'] !== null)
                                <span class="badge badge-installment">{{ $item['installment_number'] }}/{{ $item['total_installments'] }}</span>
                            @endif
                        </td>
                        <td class="text-right text-red">{{ $formatCurrency($item['amount']) }}</td>
                        <td>{{ $item['due_date'] ? $formatDate($item['due_date']) : '—' }}</td>
                        <td>{{ $item['credit_card_name'] ?? '—' }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td>Subtotal</td>
                    <td class="text-right text-red">{{ $formatCurrency($installments['subtotal']) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="empty-state">Nenhum registro neste período.</div>
    @endif

    <!-- Inflow Transactions -->
    <div class="section-title section-title-green no-break">Entradas</div>
    @if(count($inflowTransactions) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Conta</th>
                    <th class="text-right">Valor</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inflowTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->description ?? '—' }}</td>
                        <td>{{ $transaction->account?->name ?? '—' }}</td>
                        <td class="text-right text-green">{{ $formatCurrency((float) $transaction->amount) }}</td>
                        <td>{{ $transaction->occurred_at ? $formatDate($transaction->occurred_at->toDateString()) : '—' }}</td>
                    </tr>
                @endforeach
                @php $inflowSubtotal = collect($inflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                <tr class="subtotal-row">
                    <td>Subtotal</td>
                    <td></td>
                    <td class="text-right text-green">{{ $formatCurrency($inflowSubtotal) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="empty-state">Nenhum registro neste período.</div>
    @endif

    <!-- Outflow Transactions -->
    <div class="section-title section-title-red no-break">Saídas</div>
    @if(count($outflowTransactions) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Conta</th>
                    <th class="text-right">Valor</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($outflowTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->description ?? '—' }}</td>
                        <td>{{ $transaction->category?->name ?? '—' }}</td>
                        <td>{{ $transaction->account?->name ?? '—' }}</td>
                        <td class="text-right text-red">{{ $formatCurrency((float) $transaction->amount) }}</td>
                        <td>{{ $transaction->due_date ? $formatDate($transaction->due_date->toDateString()) : '—' }}</td>
                        <td>
                            @if($transaction->status === 'PAID')
                                <span class="badge badge-paid">Pago</span>
                            @elseif($transaction->status === 'PENDING')
                                <span class="badge badge-pending">Pendente</span>
                            @elseif($transaction->status === 'OVERDUE')
                                <span class="badge badge-overdue">Atrasado</span>
                            @else
                                {{ $transaction->status }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                @php $outflowSubtotal = collect($outflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                <tr class="subtotal-row">
                    <td>Subtotal</td>
                    <td colspan="2"></td>
                    <td class="text-right text-red">{{ $formatCurrency($outflowSubtotal) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="empty-state">Nenhum registro neste período.</div>
    @endif

</body>
</html>
