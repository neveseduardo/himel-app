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
            font-size: 9pt; color: #111827; line-height: 1.4;
            background: #fff; margin: 2cm;
        }
        .text-right { text-align: right; }
        .muted { color: #6b7280; }
        .no-break { page-break-inside: avoid; }
        .header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #d1d5db; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; border: none; padding: 0; }
        .logo-box {
            width: 36px; height: 36px; border: 1px solid #d1d5db; border-radius: 4px;
            text-align: center; line-height: 36px; font-size: 6pt; font-weight: bold; color: #9ca3af;
        }
        .report-title { font-size: 14pt; font-weight: bold; }
        .report-date { font-size: 7.5pt; color: #6b7280; margin-top: 1px; }
        .summary { margin-bottom: 16px; }
        .s-line { padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
        .s-main { padding-top: 8px; font-size: 9pt; font-weight: bold; }
        .s-main span { font-size: 10pt; }
        .s-detail { padding: 2px 0 2px 16px; font-size: 7.5pt; color: #6b7280; border-bottom: none; }
        .s-balance { padding-top: 8px; font-size: 10pt; font-weight: bold; border-top: 1px solid #d1d5db; border-bottom: none; }
        .s-balance span { font-size: 11pt; }
        .section { margin-top: 18px; }
        .section-label { font-size: 9pt; font-weight: bold; padding-bottom: 5px; margin-bottom: 5px; border-bottom: 1px solid #d1d5db; }
        .tbl { width: 100%; border-collapse: collapse; font-size: 8pt; }
        .tbl th { text-align: left; padding: 4px 6px; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        .tbl th.text-right { text-align: right; }
        .tbl td { padding: 4px 6px; border-bottom: 1px solid #f3f4f6; }
        .tbl tr:nth-child(even) td { background-color: #fafafa; }
        .tbl .sub td { font-weight: bold; border-top: 1px solid #d1d5db; border-bottom: none; background: #fff !important; padding-top: 5px; }
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

    <!-- Resumo Financeiro -->
    <div class="summary">
        <div class="s-line s-main">Entradas: <span>{{ $formatCurrency($summary['total_inflow']) }}</span></div>
        <div class="s-line s-detail">Manuais: {{ $formatCurrency($summary['inflow_manual'] ?? 0) }}</div>
        <div class="s-line s-detail">Transferências: {{ $formatCurrency($summary['inflow_transfer'] ?? 0) }}</div>

        <div class="s-line s-main">Saídas: <span>{{ $formatCurrency($summary['total_outflow']) }}</span></div>
        <div class="s-line s-detail">Despesas Fixas: {{ $formatCurrency($summary['total_fixed_expenses'] ?? 0) }}</div>
        <div class="s-line s-detail">Parcelas de Cartão: {{ $formatCurrency($summary['total_credit_card_installments'] ?? 0) }}</div>
        <div class="s-line s-detail">Manuais: {{ $formatCurrency($summary['total_manual'] ?? 0) }}</div>
        <div class="s-line s-detail">Transferências: {{ $formatCurrency($summary['total_transfer'] ?? 0) }}</div>

        @if(count($cardBreakdown['cards']) > 0)
            <div class="s-line s-main">Cartões de Crédito: <span>{{ $formatCurrency($cardBreakdown['grand_total']) }}</span></div>
            @foreach($cardBreakdown['cards'] as $card)
                <div class="s-line s-detail">{{ $card['credit_card_name'] }}: {{ $formatCurrency($card['total']) }}</div>
            @endforeach
        @endif

        <div class="s-line s-balance">Saldo: <span>{{ $formatCurrency($summary['balance']) }}</span></div>
    </div>

    <!-- Despesas Fixas -->
    <div class="section no-break">
        <div class="section-label">Despesas Fixas</div>
        @if(count($fixedExpenses['items']) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th class="text-right">Valor</th><th>Categoria</th><th>Dia Venc.</th></tr></thead>
                <tbody>
                    @foreach($fixedExpenses['items'] as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '—' }}</td>
                            <td class="text-right">{{ $formatCurrency($item['amount']) }}</td>
                            <td>{{ $item['category_name'] ?? '—' }}</td>
                            <td>{{ $item['due_day'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="sub"><td>Subtotal</td><td class="text-right">{{ $formatCurrency($fixedExpenses['subtotal']) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

    <!-- Parcelas de Cartão -->
    <div class="section no-break">
        <div class="section-label">Parcelas de Cartão</div>
        @if(count($installments['items']) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th>Parcela</th><th class="text-right">Valor</th><th>Vencimento</th><th>Cartão</th></tr></thead>
                <tbody>
                    @foreach($installments['items'] as $item)
                        <tr>
                            <td>{{ $item['charge_description'] ?? '—' }}</td>
                            <td>@if($item['installment_number'] !== null && $item['total_installments'] !== null){{ $item['installment_number'] }}/{{ $item['total_installments'] }}@else—@endif</td>
                            <td class="text-right">{{ $formatCurrency($item['amount']) }}</td>
                            <td>{{ $item['due_date'] ? $formatDate($item['due_date']) : '—' }}</td>
                            <td>{{ $item['credit_card_name'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    <tr class="sub"><td>Subtotal</td><td></td><td class="text-right">{{ $formatCurrency($installments['subtotal']) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

    <!-- Entradas -->
    <div class="section no-break">
        <div class="section-label">Entradas</div>
        @if(count($inflowTransactions) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th>Conta</th><th class="text-right">Valor</th><th>Data</th></tr></thead>
                <tbody>
                    @foreach($inflowTransactions as $t)
                        <tr>
                            <td>{{ $t->description ?? '—' }}</td>
                            <td>{{ $t->account?->name ?? '—' }}</td>
                            <td class="text-right">{{ $formatCurrency((float) $t->amount) }}</td>
                            <td>{{ $t->occurred_at ? $formatDate($t->occurred_at->toDateString()) : '—' }}</td>
                        </tr>
                    @endforeach
                    @php $inflowSub = collect($inflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                    <tr class="sub"><td>Subtotal</td><td></td><td class="text-right">{{ $formatCurrency($inflowSub) }}</td><td></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

    <!-- Saídas -->
    <div class="section no-break">
        <div class="section-label">Saídas</div>
        @if(count($outflowTransactions) > 0)
            <table class="tbl">
                <thead><tr><th>Descrição</th><th>Categoria</th><th>Conta</th><th class="text-right">Valor</th><th>Vencimento</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($outflowTransactions as $t)
                        <tr>
                            <td>{{ $t->description ?? '—' }}</td>
                            <td>{{ $t->category?->name ?? '—' }}</td>
                            <td>{{ $t->account?->name ?? '—' }}</td>
                            <td class="text-right">{{ $formatCurrency((float) $t->amount) }}</td>
                            <td>{{ $t->due_date ? $formatDate($t->due_date->toDateString()) : '—' }}</td>
                            <td>{{ $t->status === 'PAID' ? 'Pago' : ($t->status === 'PENDING' ? 'Pendente' : ($t->status === 'OVERDUE' ? 'Atrasado' : $t->status)) }}</td>
                        </tr>
                    @endforeach
                    @php $outflowSub = collect($outflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                    <tr class="sub"><td>Subtotal</td><td colspan="2"></td><td class="text-right">{{ $formatCurrency($outflowSub) }}</td><td colspan="2"></td></tr>
                </tbody>
            </table>
        @else
            <div class="empty">Nenhum registro neste período.</div>
        @endif
    </div>

</body>
</html>
