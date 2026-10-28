<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacturaSdkService
{
    protected $token;
    protected $baseUrl;
    protected $cfg;

    public function __construct()
    {
        $this->cfg = config('services.afip');
        $this->token = $this->cfg['access_token'];
        $this->baseUrl =  $this->cfg['arca_url'];
    }

    /**
     * Emitir una factura a través de la API REST
     */
    public function createInvoice(array $data)
    {
        if (!isset($data['issuer_cuit'])) {
            $data['issuer_cuit'] = (int) $this->cfg['cuit'];
        }

        $response = Http::withToken($this->token)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->baseUrl . 'invoices', $data);

        if ($response->failed()) {
            throw new \Exception('Error en Factura SDK: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Consultar el último comprobante (si el endpoint REST lo provee)
     */
    public function getLastVoucher(int $salesPoint, int $invoiceType)
    {
        $cuit = (int) $this->cfg['cuit'];

        $response = Http::withToken($this->token)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->get($this->baseUrl . "issuers/{$cuit}/last-voucher", [
                "sales_point" => $salesPoint,
                "invoice_type" => $invoiceType
            ]);

        if ($response->failed()) {
            // Si la API usa otra ruta para esto, lo ajustamos o retornamos simulado
            throw new \Exception('Error al consultar último comprobante: ' . $response->body());
        }

        return $response->json();
    }

    public function getInvoiceStatus(string $id): array
    {
        $response = Http::withToken($this->token)
            ->timeout(30)
            ->get("{$this->baseUrl}invoices/{$id}");

        if ($response->failed()) {
            throw new \Exception('Error al consultar la factura: ' . $response->body());
        }

        return $response->json();
    }

    public function getPdf(string $id): string
    {
        // 1) Endpoint directo del PDF
        $res = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/pdf'])
            ->timeout(30)
            ->get("{$this->baseUrl}invoices/{$id}/pdf");

        if ($res->successful() && str_contains((string) $res->header('Content-Type'), 'application/pdf')) {
            return $res->body();
        }

        // 2) Alternativa: URL del PDF dentro del detalle de la factura
        $detail  = $this->getInvoiceStatus($id);
        $invoice = $detail['data'] ?? $detail;
        $url     = $invoice['pdf_url'] ?? $invoice['pdf'] ?? null;

        if (!$url) {
            throw new \Exception('La API no devolvió el PDF ni una URL para esta factura.');
        }

        $pdf = Http::timeout(30)->get($url);

        if ($pdf->failed()) {
            throw new \Exception('No se pudo descargar el PDF: ' . $pdf->status());
        }

        return $pdf->body();
    }

}
