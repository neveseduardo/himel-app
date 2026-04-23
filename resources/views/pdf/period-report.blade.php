<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        * { margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #111827; line-height: 1.4; }
        .wrap { padding: 2cm; }
        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        .no-break { page-break-inside: avoid; }

        .hdr td { padding: 0 0 12px 0; border-bottom: 1px solid #d1d5db; vertical-align: middle; }
        .hdr .logo { width: 46px; }
        .logo-box {
            width: 36px; height: 36px; border: 1px solid #d1d5db; border-radius: 4px;
            text-align: center; line-height: 36px; font-size: 6pt; font-weight: bold; color: #9ca3af;
        }
        .report-title { font-size: 14pt; font-weight: bold; }
        .report-date { font-size: 7.5pt; color: #6b7280; margin-top: 1px; }

        .gap td { border: none; height: 16px; }
        .gap-sm td { border: none; height: 10px; }

        /* Summary tables */
        .sum { border: none; margin: 0; padding: 0; border-spacing: 0; font-size: 8pt; }
        .sum th { text-align: left; padding: 0; font-weight: bold; font-size: 9pt; border: none; }
        .sum th.text-right { text-align: right; font-size: 9pt; white-space: nowrap; }
        .sum td { padding: 1px 0; border: none; color: #6b7280; }
        .sum td.text-right { white-space: nowrap; }
        .sum .wide { width: 100%; }

        .sec-label td { padding: 14px 0 5px 0; border-bottom: 1px solid #d1d5db; font-size: 9pt; font-weight: bold; }

        .tbl { font-size: 8pt; }

        .tbl { font-size: 8pt; }
        .tbl th { text-align: left; padding: 4px 6px; font-size: 7pt; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        .tbl th.text-right { text-align: right; }
        .tbl td { padding: 4px 6px; border-bottom: 1px solid #f3f4f6; }
        .tbl tr:nth-child(even) td { background-color: #fafafa; }
        .tbl .sub td { font-weight: bold; border-top: 1px solid #d1d5db; border-bottom: none; background: #fff !important; padding-top: 5px; }

        .empty { padding: 12px 0; color: #9ca3af; font-size: 8pt; }
    </style>
</head>
<body>
<div class="wrap">

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
    <table>
        <tr class="hdr">
            <td>
                <div class="report-title">{{ $title }}</div>
                <div class="report-date">Gerado em {{ $generatedAt }}</div>
            </td>
			<td class="logo"><div class="logo-box">LOGO</div></td>
        </tr>
        <tr class="gap"><td colspan="2"></td></tr>
    </table>

    <!-- Resumo -->
    <table class="sum">
        <thead><tr><th class="wide">Entradas</th><th class="text-right">{{ $formatCurrency($summary['total_inflow']) }}</th></tr></thead>
        <tbody>
            <tr><td>Manuais</td><td class="text-right">{{ $formatCurrency($summary['inflow_manual'] ?? 0) }}</td></tr>
            <tr><td>Transferências</td><td class="text-right">{{ $formatCurrency($summary['inflow_transfer'] ?? 0) }}</td></tr>
        </tbody>
    </table>

    <table class="sum">
        <thead><tr><th class="wide">Saídas</th><th class="text-right">{{ $formatCurrency($summary['total_outflow']) }}</th></tr></thead>
        <tbody>
            <tr><td>Despesas Fixas</td><td class="text-right">{{ $formatCurrency($summary['total_fixed_expenses'] ?? 0) }}</td></tr>
            <tr><td>Parcelas de Cartão</td><td class="text-right">{{ $formatCurrency($summary['total_credit_card_installments'] ?? 0) }}</td></tr>
            <tr><td>Manuais</td><td class="text-right">{{ $formatCurrency($summary['total_manual'] ?? 0) }}</td></tr>
            <tr><td>Transferências</td><td class="text-right">{{ $formatCurrency($summary['total_transfer'] ?? 0) }}</td></tr>
        </tbody>
    </table>

    @if(count($cardBreakdown['cards']) > 0)
        <table class="sum">
            <thead><tr><th class="wide">Cartões de Crédito</th><th class="text-right">{{ $formatCurrency($cardBreakdown['grand_total']) }}</th></tr></thead>
            <tbody>
                @foreach($cardBreakdown['cards'] as $card)
                    <tr><td>{{ $card['credit_card_name'] }}</td><td class="text-right">{{ $formatCurrency($card['total']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="sum">
        <thead><tr><th class="wide">Saldo</th><th class="text-right">{{ $formatCurrency($summary['balance']) }}</th></tr></thead>
    </table>

    <!-- Despesas Fixas -->
    <table><tr class="sec-label"><td colspan="4">Despesas Fixas</td></tr></table>
    @if(count($fixedExpenses['items']) > 0)
        <table class="tbl">
            <thead><tr><th>Descrição</th><th class="text-right">Valor</th><th>Categoria</th><th>Dia Venc.</th></tr></thead>
            <tbody>
                @foreach($fixedExpenses['items'] as $item)
                    <tr><td>{{ $item['description'] ?? '—' }}</td><td class="text-right">{{ $formatCurrency($item['amount']) }}</td><td>{{ $item['category_name'] ?? '—' }}</td><td>{{ $item['due_day'] ?? '—' }}</td></tr>
                @endforeach
                <tr class="sub"><td>Subtotal</td><td class="text-right">{{ $formatCurrency($fixedExpenses['subtotal']) }}</td><td colspan="2"></td></tr>
            </tbody>
        </table>
    @else
        <table><tr><td class="empty">Nenhum registro neste período.</td></tr></table>
    @endif

    <!-- Parcelas de Cartão -->
    <table><tr class="sec-label"><td colspan="5">Parcelas de Cartão</td></tr></table>
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
        <table><tr><td class="empty">Nenhum registro neste período.</td></tr></table>
    @endif

    <!-- Entradas -->
    <table><tr class="sec-label"><td colspan="4">Entradas</td></tr></table>
    @if(count($inflowTransactions) > 0)
        <table class="tbl">
            <thead><tr><th>Descrição</th><th>Conta</th><th class="text-right">Valor</th><th>Data</th></tr></thead>
            <tbody>
                @foreach($inflowTransactions as $t)
                    <tr><td>{{ $t->description ?? '—' }}</td><td>{{ $t->account?->name ?? '—' }}</td><td class="text-right">{{ $formatCurrency((float) $t->amount) }}</td><td>{{ $t->occurred_at ? $formatDate($t->occurred_at->toDateString()) : '—' }}</td></tr>
                @endforeach
                @php $inflowSub = collect($inflowTransactions)->sum(fn ($t) => (float) $t->amount); @endphp
                <tr class="sub"><td>Subtotal</td><td></td><td class="text-right">{{ $formatCurrency($inflowSub) }}</td><td></td></tr>
            </tbody>
        </table>
    @else
        <table><tr><td class="empty">Nenhum registro neste período.</td></tr></table>
    @endif

    <!-- Saídas -->
    <table><tr class="sec-label"><td colspan="6">Saídas</td></tr></table>
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
        <table><tr><td class="empty">Nenhum registro neste período.</td></tr></table>
    @endif

</div>
</body>
</html>
