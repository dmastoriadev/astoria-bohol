<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccessFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $allowed = [
            'Access my data',
            'Rectify/Update my data',
            'Delete my data (erasure)',
            'Restrict processing',
            'Object to processing',
            'Data portability (copy/transfer)',
            'Marketing preferences (unsubscribe)',
            'Other',
        ];

        return [
            'full_name'        => ['required','string','max:120'],
            'email'            => ['required','email','max:190'],
            'mobile'           => ['nullable','string','max:40'],
            'agreement_number' => ['nullable','string','max:60'],

            'requests'         => ['required','array','min:1'],
            'requests.*'       => ['string', Rule::in($allowed)],

            'description'      => ['required','string','max:4000'],
            'prefer_contact'   => ['required', Rule::in(['email','phone'])],

            // Files (optional)
            'id_document'      => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:5120'],     // 5MB
            'supporting_files' => ['nullable','array','max:5'],
            'supporting_files.*'=> ['file','mimes:jpg,jpeg,png,pdf,doc,docx','max:5120'],

            // Consent + Honeypot
            'consent'          => ['accepted'],
            'website'          => ['nullable','size:0'], // honeypot
        ];
    }

    public function attributes(): array
    {
        return [
            'agreement_number' => 'agreement number',
            'requests'         => 'request type',
            'description'      => 'request details',
            'prefer_contact'   => 'preferred contact method',
            'consent'          => 'declaration & consent',
        ];
    }
}
