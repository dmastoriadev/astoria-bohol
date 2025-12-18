<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessFormRequest;
use Illuminate\Http\RedirectResponse;
use SendGrid\Mail\Mail as SendGridMail;
use SendGrid;

class AccessFormController extends Controller
{
    public function store(AccessFormRequest $request): RedirectResponse
    {
        // Honeypot
        if ($request->filled('website')) {
            return back()->withErrors(['general'=>'Something went wrong.'])->withInput();
        }

        $data = [
            'full_name'        => $request->string('full_name')->toString(),
            'email'            => $request->string('email')->toString(),
            'mobile'           => $request->string('mobile')->toString(),
            'agreement_number' => $request->string('agreement_number')->toString(),
            'requests'         => $request->input('requests', []),
            'description'      => $request->string('description')->toString(),
            'prefer_contact'   => $request->string('prefer_contact')->toString(),
            'ip'               => $request->ip(),
            'ua'               => (string) $request->header('User-Agent'),
        ];

        $html  = view('emails.access-form-notification', $data)->render();
        $plain = self::plainTextFromHtml($html);

        $to          = env('ACCESS_FORM_TO', 'jarric.creencia@astoria.com.ph');
        $fromAddress = config('mail.from.address');
        $fromName    = config('mail.from.name', 'AVLCI Access Form');

        $mail = new SendGridMail();
        $mail->setFrom($fromAddress, $fromName);
        $mail->setSubject('Data Access Request — ' . $data['full_name']);
        $mail->addTo($to);
        $mail->addContent('text/plain', $plain);
        $mail->addContent('text/html',  $html);

        // Attachments: ID + optional supporting files
        $files = [];
        if ($request->hasFile('id_document')) {
            $files[] = $request->file('id_document');
        }
        if ($request->hasFile('supporting_files')) {
            $files = array_merge($files, $request->file('supporting_files'));
        }
        foreach ($files as $file) {
            $content = base64_encode(file_get_contents($file->getRealPath()));
            $mail->addAttachment(
                $content,
                $file->getMimeType(),
                $file->getClientOriginalName(),
                'attachment'
            );
        }

        try {
            $sg = new SendGrid(env('SENDGRID_API_KEY'));
            $response = $sg->send($mail);
            if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
                \Log::error('SendGrid error (access form)', [
                    'status'  => $response->statusCode(),
                    'body'    => method_exists($response, 'body') ? $response->body() : null,
                    'headers' => method_exists($response, 'headers') ? $response->headers() : null,
                ]);
                return back()->withErrors(['general'=>'We could not send your request right now. Please try again later.'])->withInput();
            }
        } catch (\Throwable $e) {
            \Log::error('SendGrid exception (access form)', ['message'=>$e->getMessage()]);
            return back()->withErrors(['general'=>'We could not send your request right now. Please try again later.'])->withInput();
        }

        return redirect()->route('access-form.show')->with('ok','Thanks! Your request was sent.');
    }

    private static function plainTextFromHtml(string $html): string
    {
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html);
        $text = preg_replace('/<\/(p|div|li|h[1-6])>/i', "\n", $text);
        $text = strip_tags($text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }
}
