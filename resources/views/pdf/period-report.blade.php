<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #111827;
            line-height: 1.4;
            background: #fff;
            margin: 2cm;
        }

        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-muted { color: #6b7280; }
        .text-right { text-align: right; }
        .no-break { page-break-inside: avoid; }

        /* Header */
        .header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #d1d5db; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .logo-box {
            width: 36px; height: 36px;
            border: 1px solid #d1d5db; border-radius: 4px;
            text-align: center; line-height: 36px;
            font-size: 6pt; font-weight: bold; color: #9ca3af;
        }
        .report-title { font-size: 14pt; font-weight: bold; color: #111827; }
        .report-date { font-size: 7.5pt; color: #9ca3af; margin-top: 1px; }

        /* Summary (vertical list) */
        .summary-item {
            width: 100%; border-collapse: collapse;
            margin-bottom: 2px;
        }
        .summary-item td {
            padding: 7px 0; border-bottom: 1px solid #f3f4f6; vertical-align: top;
        }
        .summary-item .s-label { font-size: 8pt; color: #6b7280; width: 120px; }
        .summary-item .s-value { font-size: 11pt; font-weight: bold; width: 150px; }
        .summary-item .s-detail { font-size: 7.5pt; color: #6b7280; }
        .detail-line { border-collapse: collapse; }
        .detail-line td { border: none; padding: 0 0 1px 0; font-size: 7.5pt; color: #6b7280; }

        /* Section */
        .section { margin-top: 18px; }
        .section-label {
            font-size: 9pt; font-weight: bold; color: #111827;
            padding-bottom: 5px; margin-bottom: 5px;
            border-bottom: 1px solid #d1d5db;
        }
        .section-label-green { color: #15803d; border-bottom-color: #16a34a; }
        .section-label-red { color: #b91c1c; border-bottom-color: #dc2626; }
        .section-label-blue { color: #1d4ed8; border-bottom-color: #2563eb; }

        /* Tables */
        .tbl { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .tbl th {
            text-align: left; padding: 4px 6px;
            font-size: 7pt; text-transform: uppercase; letter-spacing: 0.3px;
            color: #6b7280; border-bottom: 1px solid #d1d5db;
        }
        .tbl th.text-right { text-align: right; }
        .tbl td { padding: 4px 6px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .tbl tr:nth-child(even) td { background-color: #fafafa; }
        .tbl .sub td {
            font-weight: bold; border-top: 1px solid #d1d5db;
            border-bottom: none; background: #fff !important; padding-top: 5px;
        }

        /* Badges */
        .badge {
            display: inline-block; padding: 1px 5px; border-radius: 6px;
            font-size: 6pt; font-weight: bold; text-transform: uppercase;
        }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef9c3; color: #a16207; }
        .badge-overdue { background: #fee2e2; color: #b91c1c; }
        .badge-inst { background: #ede9fe; color: #6d28d9; }

        .empty { text-align: center; padding: 12px 0; color: #9ca3af; font-size: 8pt; }
    </style>
</head>
<body>

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
                <td width="46"><div class="logo-box">LOGO</div></td>
                <td>
                    <div class="report-title">{{ $title }}</div>
                    <div class="report-date">Gerado em {{ $generatedAt }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary — vertical stacked rows -->
    <table class="summary-item">
        <tr>
            <td class="s-label">Entradas</td>
            <td class="s-value text-green">{{ $formatCurrency($summary['total_inflow']) }}</td>
            <td class="s-detail">
                <table class="detail-line">
                    <tr><td>Manuais: {{ $formatCurrency($summary['inflow_manual'] ?? 0) }}</td></tr>
                    <tr><td>Transferências: {{ $formatCurrency($summary['inflow_transfer'] ?? 0) }}</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="s-label">Saídas</td>
            <td class="s-value text-red">{{ $formatCurrency($summary['total_outflow']) }}</td>
            <td class="s-detail">
                <table class="detail-line">
                    <tr><td>Desp. Fixas: {{ $formatCurrency($summary['total_fixed_expenses'] ?? 0) }}</td></tr>
                    <tr><td>Parcelas: {{ $formatCurrency($summary['total_credit_card_installments'] ?? 0) }}</td></tr>
                    <tr><td>Manuais: {{ $formatCurrency($summary['total_manual'] ?? 0) }}</td></tr>
                    <tr><td>Transferências: {{ $formatCurrency($summary['total_transfer'] ?? 0) }}</td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="s-label">Cartões</td>
            <td class="s-value text-red">{{ $formatCurrency($cardBreakdown['grand_total']) }}</td>
            <td class="s-detail">
                @if(count($cardBreakdown['cards']) > 0)
                    <table class="detail-line">
                        @foreach($cardBreakdown['cards'] as $card)
                            <tr><td>{{ $card['credit_card_name'] }}: {{ $formatCurrency($card['total']) }}</td></tr>
                        @endforeach
                    </table>
                @else
                    Sem parcelas
                @endif
            </td>
        </tr>
        <tr>
            <td class="s-label">Saldo</td>
            <td class="s-value {{ $summary['balance'] >= 0 ? 'text-green' : 'text-red' }}">{{ $formatCurrency($summary['balance']) }}</td>
            <td></td>
        </tr>
    </table>

    <!-- Fixed Expenses -->
    <div class="section no-break">
        <div class="section-label">Despesas Fixas</div>
        @if(count($fixedExpenses['items']) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th class="text-right">Valor</th><th>Categoria</th><th>Dia Venc.</th></tr></thead>
                <tbody>
                    @foreach($fixedExpenses['items'] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '—' }}</td>
                            <td class="text-right text-red">{{ $formatCurrency($item['amount']) }}</td>
                            <td>{{ $item['category_name'] ?? '—' }}</td>
                            <td>{{ $item['due_day'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="sub"><td>Subtotal</td><td class="text-right text-red">{{ $formatCurrency($fixedExpenses['subtotal']) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

    <!-- Card Installments -->
    <div class="section no-break">
        <div class="section-label section-label-blue">Parcelas de Cartão</div>
        @if(count($installments['items']) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th class="text-right">Valor</th><th>Vencimento</th><th>Cartão</th></tr></thead>
                <tbody>
                    @foreach($installments['items'] as $item)
                        <tr>
                            <td>
                                {{ $item['charge_description'] ?? '—' }}
                                @if($item['installment_number'] !== null && $item['total_installments'] !== null)
                                    <span class="badge badge-inst">{{ $item['installment_number'] }}/{{ $item['total_installments'] }}</span>
                                @endif
                            </td>
                            <td class="text-right text-red">{{ $formatCurrency($item['amount']) }}</td>
                            <td>{{ $item['due_date'] ? $formatDate($item['due_date']) : '—' }}</td>
                            <td>{{ $item['credit_card_name'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="sub"><td>Subtotal</td><td class="text-right text-red">{{ $formatCurrency($installments['subtotal']) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

    <!-- Inflow -->
    <div class="section no-break">
        <div class="section-label section-label-green">Entradas</div>
        @if(count($inflowTransactions) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th>Conta</th><th class="text-right">Valor</th><th>Data</th></tr></thead>
                <tbody>
                    @foreach($inflowTransactions as $t)
                        <tr>
                            <td>{{ $t->description ?? '—' }}</td>
                            <td>{{ $t->account?->name ?? '—' }}</td>
                            <td class="text-right text-green">{{ $formatCurrency((float) $t->amount) }}</td>
                            <td>{{ $t->occurred_at ? $formatDate($t->occurred_at->toDateString()) : '—' }}</td>
                        </tr>
                    @endforeach
                    @php $inflowSub = collect($inflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                    <tr class="sub"><td>Subtotal</td><td></td><td class="text-right text-green">{{ $formatCurrency($inflowSub) }}</td><td></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

    <!-- Outflow -->
    <div class="section no-break">
        <div class="section-label section-label-red">Saídas</div>
        @if(count($outflowTransactions) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th>Categoria</th><th>Conta</th><th class="text-right">Valor</th><th>Vencimento</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($outflowTransactions as $t)
                        <tr>
                            <td>{{ $t->description ?? '—' }}</td>
                            <td>{{ $t->category?->name ?? '—' }}</td>
                            <td>{{ $t->account?->name ?? '—' }}</td>
                            <td class="text-right text-red">{{ $formatCurrency((float) $t->amount) }}</td>
                            <td>{{ $t->due_date ? $formatDate($t->due_date->toDateString()) : '—' }}</td>
                            <td>
                                @if($t->status === 'PAID')<span class="badge badge-paid">Pago</span>
                                @elseif($t->status === 'PENDING')<span class="badge badge-pending">Pendente</span>
                                @elseif($t->status === 'OVERDUE')<span class="badge badge-overdue">Atrasado</span>
                                @else{{ $t->status }}@endif
                            </td>
                        </tr>
                    @endforeach
                    @php $outflowSub = collect($outflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                    <tr class="sub"><td>Subtotal</td><td colspan="2"></td><td class="text-right text-red">{{ $formatCurrency($outflowSub) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

</body>
</html>
