<?php

namespace App\Services;

class InvoicePayloadBuilder
{
    private const IVA = [3 => 0, 4 => 10.5, 5 => 21, 6 => 27, 8 => 5, 9 => 2.5];

    public static function build(array $in): array
    {
        $type  = (int) $in['invoice_type'];
        $items = [];
        $net   = 0.0;
        $bases = [];

        foreach ($in['items'] as $it) {
            $subtotal = round($it['quantity'] * $it['unit_price'], 2);
            $net     += $subtotal;

            $item = [
                'description' => $it['description'],
                'quantity'    => $it['quantity'],
                'unit_price'  => $it['unit_price'],
                'subtotal'    => $subtotal,
            ];

            if ($type !== 11) {
                $id = (int) ($it['vat_rate'] ?? 5);
                $bases[$id] = ($bases[$id] ?? 0) + $subtotal;
                $item['vat_rate'] = $id;
            }

            $items[] = $item;
        }

        $vatItems = [];
        $vat      = 0.0;

        foreach ($bases as $id => $base) {
            $amount     = round($base * self::IVA[$id] / 100, 2);
            $vat       += $amount;
            $vatItems[] = ['id' => $id, 'base' => round($base, 2), 'amount' => $amount];
        }

        $net = round($net, 2);

        return [
            'issuer_cuit'  => (int) config('services.afip.cuit'),
            'invoice_type' => $type,
            'sales_point'  => (int) ($in['sales_point'] ?? 1),
            'concept'      => (int) ($in['concept'] ?? 1),
            'vat_items'    => $type === 11 ? null : $vatItems,
            'receiver'     => [
                'doc_type'      => (int) ($in['receiver']['doc_type'] ?? 99),
                'doc_number'    => (string) ($in['receiver']['doc_number'] ?? '0'),
                'vat_condition' => (int) ($in['receiver']['vat_condition'] ?? 5),
                'name'          => $in['receiver']['name'] ?? 'Consumidor Final',
                'address'       => $in['receiver']['address'] ?? 'Sin domicilio',
            ],
            'amounts' => [
                'net'   => $net,
                'vat'   => round($vat, 2),
                'total' => round($net + $vat, 2),
            ],
            'items'          => $items,
            'sale_condition' => (string) ($in['sale_condition'] ?? '1'),
        ];
    }
}
