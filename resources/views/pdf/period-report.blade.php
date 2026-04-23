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
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .summary td { padding: 5px 0; vertical-align: top; border-bottom: 1px solid #f3f4f6; }
        .summary .lbl { font-size: 8pt; font-weight: bold; }
        .summary .val { font-size: 8pt; text-align: right; width: 140px; }
        .summary .main td { padding-top: 8px; }
        .summary .main .lbl { font-size: 9pt; }
        .summary .main .val { font-size: 10pt; font-weight: bold; }
        .summary .detail .lbl { font-weight: normal; padding-left: 16px; color: #6b7280; font-size: 7.5pt; }
        .summary .detail .val { color: #6b7280; font-size: 7.5pt; }
        .summary .detail td { padding: 2px 0; border-bottom: none; }
        .summary .balance td { border-top: 1px solid #d1d5db; border-bottom: none; padding-top: 8px; }
        .summary .balance .lbl { font-size: 10pt; }
        .summary .balance .val { font-size: 11pt; font-weight: bold; }
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
    <table class="summary">
        <tr class="main">
            <td class="lbl">Entradas</td>
            <td class="val">{{ $formatCurrency($summary['total_inflow']) }}</td>
        </tr>
        <tr class="detail">
            <td class="lbl">Manuais</td>
            <td class="val">{{ $formatCurrency($summary['inflow_manual'] ?? 0) }}</td>
        </tr>
        <tr class="detail">
            <td class="lbl">Transferências</td>
            <td class="val">{{ $formatCurrency($summary['inflow_transfer'] ?? 0) }}</td>
        </tr>

        <tr class="main">
            <td class="lbl">Saídas</td>
            <td class="val">{{ $formatCurrency($summary['total_outflow']) }}</td>
        </tr>
        <tr class="detail">
            <td class="lbl">Despesas Fixas</td>
            <td class="val">{{ $formatCurrency($summary['total_fixed_expenses'] ?? 0) }}</td>
        </tr>
        <tr class="detail">
            <td class="lbl">Parcelas de Cartão</td>
            <td class="val">{{ $formatCurrency($summary['total_credit_card_installments'] ?? 0) }}</td>
        </tr>
        <tr class="detail">
            <td class="lbl">Manuais</td>
            <td class="val">{{ $formatCurrency($summary['total_manual'] ?? 0) }}</td>
        </tr>
        <tr class="detail">
            <td class="lbl">Transferências</td>
            <td class="val">{{ $formatCurrency($summary['total_transfer'] ?? 0) }}</td>
        </tr>

        @if(count($cardBreakdown['cards']) > 0)
            <tr class="main">
                <td class="lbl">Cartões de Crédito</td>
                <td class="val">{{ $formatCurrency($cardBreakdown['grand_total']) }}</td>
            </tr>
            @foreach($cardBreakdown['cards'] as $card)
                <tr class="detail">
                    <td class="lbl">{{ $card['credit_card_name'] }}</td>
                    <td class="val">{{ $formatCurrency($card['total']) }}</td>
                </tr>
            @endforeach
        @endif

        <tr class="balance">
            <td class="lbl">Saldo</td>
            <td class="val">{{ $formatCurrency($summary['balance']) }}</td>
        </tr>
    </table>

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
