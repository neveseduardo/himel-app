<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        /* Reset & Base */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4 portrait;
            margin: 20mm 15mm 25mm 15mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.5;
            background: #ffffff;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #1f2937;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            background-color: #e5e7eb;
            border-radius: 8px;
            text-align: center;
            line-height: 60px;
            font-size: 9pt;
            font-weight: bold;
            color: #9ca3af;
            letter-spacing: 1px;
        }

        .header-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .header-subtitle {
            font-size: 9pt;
            color: #6b7280;
        }

        /* Section Headers */
        .section-header {
            background-color: #1f2937;
            color: #ffffff;
            padding: 8px 14px;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 22px;
            margin-bottom: 0;
            border-radius: 4px 4px 0 0;
            letter-spacing: 0.3px;
        }

        .section-header-green {
            background-color: #16a34a;
        }

        .section-header-red {
            background-color: #dc2626;
        }

        .section-header-blue {
            background-color: #2563eb;
        }

        /* Summary Cards */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        .summary-card {
            width: 25%;
            vertical-align: top;
            padding: 12px 14px;
            border-right: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .summary-card:last-child {
            border-right: none;
        }

        .summary-card-label {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .summary-card-value {
            font-size: 14pt;
            font-weight: bold;
        }

        .summary-card-detail {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 6px;
            line-height: 1.6;
        }

        .summary-detail-row {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-detail-row td {
            border: none;
            padding: 1px 0;
            font-size: 8pt;
            color: #6b7280;
        }

        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        .data-table thead th {
            background-color: #f3f4f6;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #4b5563;
            border-bottom: 2px solid #d1d5db;
        }

        .data-table thead th.text-right {
            text-align: right;
        }

        .data-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .data-table .text-right {
            text-align: right;
        }

        .data-table .subtotal-row td {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 9pt;
            padding: 9px 10px;
            border-top: 2px solid #d1d5db;
        }

        /* Card Breakdown */
        .card-breakdown-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            border: 1px solid #e5e7eb;
            border-top: none;
            margin-bottom: 0;
        }

        .card-breakdown-table td {
            padding: 7px 14px;
            border-bottom: 1px solid #f3f4f6;
        }

        .card-breakdown-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .card-breakdown-table .grand-total td {
            background-color: #f3f4f6;
            font-weight: bold;
            border-top: 2px solid #d1d5db;
            padding: 9px 14px;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-paid { background-color: #dcfce7; color: #16a34a; }
        .badge-pending { background-color: #fef3c7; color: #d97706; }
        .badge-overdue { background-color: #fee2e2; color: #dc2626; }

        .badge-installment {
            background-color: #e0e7ff;
            color: #4338ca;
            font-size: 7pt;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 16px 14px;
            color: #9ca3af;
            font-style: italic;
            font-size: 9pt;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }

        /* Utility */
        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    <!-- Footer with page numbers (DOMPDF inline PHP) -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("DejaVu Sans");
            $width = $fontMetrics->getTextWidth($text, $font, $size) / 2;
            $x = ($pdf->get_width() / 2) - ($width / 2);
            $y = $pdf->get_height() - 32;
            $pdf->page_text($x, $y, $text, $font, $size, array(0.61, 0.64, 0.69));
        }
    </script>

    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="70">
                    <div class="logo-placeholder">LOGO</div>
                </td>
                <td>
                    <div class="header-title">{{ $title }}</div>
                    <div class="header-subtitle">Gerado em {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Financial Summary -->
    <div class="section-header">Resumo Financeiro</div>
    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="summary-card-label">Entradas</div>
                <div class="summary-card-value text-green">{{ $formatCurrency($summary['total_inflow']) }}</div>
                <div class="summary-card-detail">
                    <table class="summary-detail-row">
                        <tr>
                            <td>Manuais</td>
                            <td class="text-right">{{ $formatCurrency($summary['inflow_manual'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Transferências</td>
                            <td class="text-right">{{ $formatCurrency($summary['inflow_transfer'] ?? 0) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Saídas</div>
                <div class="summary-card-value text-red">{{ $formatCurrency($summary['total_outflow']) }}</div>
                <div class="summary-card-detail">
                    <table class="summary-detail-row">
                        <tr>
                            <td>Despesas Fixas</td>
                            <td class="text-right">{{ $formatCurrency($summary['total_fixed_expenses'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Parcelas Cartão</td>
                            <td class="text-right">{{ $formatCurrency($summary['total_credit_card_installments'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Manuais</td>
                            <td class="text-right">{{ $formatCurrency($summary['total_manual'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td>Transferências</td>
                            <td class="text-right">{{ $formatCurrency($summary['total_transfer'] ?? 0) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Cartões de Crédito</div>
                <div class="summary-card-value text-red">{{ $formatCurrency($cardBreakdown['grand_total']) }}</div>
                <div class="summary-card-detail">
                    @if(count($cardBreakdown['cards']) > 0)
                        <table class="summary-detail-row">
                            @foreach($cardBreakdown['cards'] as $card)
                                <tr>
                                    <td>{{ $card['credit_card_name'] }}</td>
                                    <td class="text-right">{{ $formatCurrency($card['total']) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        <span>Sem parcelas neste período</span>
                    @endif
                </div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Saldo</div>
                <div class="summary-card-value {{ $summary['balance'] >= 0 ? 'text-green' : 'text-red' }}">
                    {{ $formatCurrency($summary['balance']) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Fixed Expenses -->
    <div class="section-header no-break">Despesas Fixas</div>
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
    <div class="section-header section-header-blue no-break">Parcelas de Cartão</div>
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
    <div class="section-header section-header-green no-break">Entradas</div>
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
                @php
                    $inflowSubtotal = collect($inflowTransactions)->sum(fn ($t) => (float) $t->amount);
                @endphp
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
    <div class="section-header section-header-red no-break">Saídas</div>
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
                @php
                    $outflowSubtotal = collect($outflowTransactions)->sum(fn ($t) => (float) $t->amount);
                @endphp
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
