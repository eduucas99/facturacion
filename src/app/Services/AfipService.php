<?php

namespace App\Services;

use Afip;

class AfipService
{
    protected Afip $afip;

    public function __construct()
    {
        $cfg = config('services.afip');

        $opts = [
            'CUIT'         => (int) $cfg['cuit'],
            'access_token' => $cfg['access_token'],
            'production'   => filter_var($cfg['production'], FILTER_VALIDATE_BOOLEAN),
        ];
        if ($cfg['cert_path'] && $cfg['key_path']) {
            $opts['cert'] = file_get_contents($cfg['cert_path']);
            $opts['key']  = file_get_contents($cfg['key_path']);
        }

        $this->afip = new Afip($opts);
    }

    public function getClient(): Afip
    {
        return $this->afip;
    }

    public function getLastVoucher(int $ptoVta, int $tipo): int
    {
        return $this->afip->ElectronicBilling->GetLastVoucher($ptoVta, $tipo);
    }

    public function createVoucher(array $data): array
    {
        return $this->afip->ElectronicBilling->CreateNextVoucher($data);
    }
}
