<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;


class UnsubscribeRequest extends FormRequest
{
public function authorize(): bool
{
return true; // Gate elsewhere if needed
}


public function rules(): array
{
return [
'full_name' => ['required','string','max:120'],
'email' => ['required','email','max:190'],
'mobile' => ['nullable','string','max:40'],
'reasons' => ['nullable','array'],
'reasons.*' => ['string','max:60'],
'message' => ['nullable','string','max:2000'],
'agree' => ['accepted'],
// Honeypot (must be empty)
'website' => ['nullable','size:0'],
];
}


public function attributes(): array
{
return [
'agree' => 'confirmation',
];
}
}