<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // más adelante: restringir con autenticación
    }

    public function rules(): array
    {
        $rules = [
            'invoice_type'           => 'required|integer|in:1,6,11',
            'sales_point'            => 'nullable|integer|min:1',
            'concept'                => 'nullable|integer|in:1',
            'sale_condition'         => 'nullable|string',
            'receiver.doc_type'      => 'nullable|integer|in:80,96,99',
            'receiver.doc_number'    => 'nullable',
            'receiver.vat_condition' => 'nullable|integer',
            'receiver.name'          => 'nullable|string|max:255',
            'receiver.address'       => 'nullable|string|max:255',
            'items'                  => 'required|array|min:1',
            'items.*.description'    => 'required|string|max:255',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.vat_rate'       => 'nullable|integer|in:3,4,5,6,8,9',
        ];

        // La Factura A exige receptor con CUIT y condición compatible
        if ((int) $this->input('invoice_type') === 1) {
            $rules['receiver.doc_type']      = 'required|integer|in:80';
            $rules['receiver.doc_number']    = 'required|digits:11';
            $rules['receiver.vat_condition'] = 'required|integer|in:1,6,13,16';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'invoice_type.in'                    => 'Tipo de comprobante inválido (1 = A, 6 = B, 11 = C).',
            'receiver.doc_number.digits'         => 'El CUIT del receptor debe tener 11 dígitos.',
            'receiver.vat_condition.in'          => 'Condición de IVA del receptor no válida para Factura A.',
            'items.required'                     => 'Tenés que enviar al menos un ítem.',
        ];
    }
}
