<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\FacturaSdkService;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoicePayloadBuilder;

class BillingController extends Controller
{
    protected $facturaSdk;

    public function __construct(FacturaSdkService $facturaSdk)
    {
        $this->facturaSdk = $facturaSdk;
    }

    public function createInvoice(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $payload = InvoicePayloadBuilder::build($request->validated());
            $result  = $this->facturaSdk->createInvoice($payload);

            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getLastVoucher($ptoVta, $cbteTipo): JsonResponse
    {
        try {
            $result = $this->facturaSdk->getLastVoucher((int) $ptoVta, (int) $cbteTipo);

            return response()->json([
                'status' => 'success',
                'data'   => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadPdf(string $id)
    {
        try {
            $pdf = $this->facturaSdk->getPdf($id);

            return response($pdf, 200, [
                'Content-Type'        => 'application/pdf',
                // 'Content-Disposition' => 'inline; filename="factura-' . $id . '.pdf"',
                'Content-Disposition' => 'attachment; filename="factura-' . $id . '.pdf"',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
