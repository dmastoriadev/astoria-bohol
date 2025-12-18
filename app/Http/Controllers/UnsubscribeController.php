<?php

namespace App\Http\Controllers;

use App\Http\Requests\UnsubscribeRequest;
use Illuminate\Http\RedirectResponse;
use SendGrid\Mail\Mail as SendGridMail;
use SendGrid;

class UnsubscribeController extends Controller
{
    public function store(UnsubscribeRequest $request): RedirectResponse
    {
        // Honeypot: if bots fill this hidden field, bail out quietly
        if ($request->filled('website')) {
            return back()->withErrors(['general' => 'Something went wrong.'])->withInput();
        }

        $data = [
            'full_name' => $request->string('full_name')->toString(),
            'email'     => $request->string('email')->toString(),
            'mobile'    => $request->string('mobile')->toString(),
            'reasons'   => $request->input('reasons', []),
            'message'   => $request->string('message')->toString(),
            'ip'        => $request->ip(),
            'ua'        => (string) $request->header('User-Agent'),
        ];

        // Build email bodies
        $html  = view('emails.unsubscribe-notification', $data)->render();
        $plain = self::plainTextFromHtml($html);

        // Prepare SendGrid mail
        $to          = config('mail.unsubscribe_to', env('UNSUBSCRIBE_TO', 'jarric.creencia@astoria.com.ph'));
        $fromAddress = config('mail.from.address');
        $fromName    = config('mail.from.name', 'AVLCI Unsubscribe Form');

        if (blank($fromAddress)) {
            // Make sure MAIL_FROM_ADDRESS is set in .env
            abort(500, 'MAIL_FROM_ADDRESS not set.');
        }

        $mail = new SendGridMail();
        $mail->setFrom($fromAddress, $fromName);
        $mail->setSubject('Unsubscribe Request — ' . $data['full_name']);
        $mail->addTo($to);
        $mail->addContent('text/plain', $plain);
        $mail->addContent('text/html', $html);

        try {
            $sg       = new SendGrid(env('SENDGRID_API_KEY'));
            $response = $sg->send($mail);

            if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
                \Log::error('SendGrid error', [
                    'status'  => $response->statusCode(),
                    'body'    => method_exists($response, 'body') ? $response->body() : null,
                    'headers' => method_exists($response, 'headers') ? $response->headers() : null,
                ]);
                return back()->withErrors(['general' => 'We could not send your request right now. Please try again later.'])->withInput();
            }
        } catch (\Throwable $e) {
            \Log::error('SendGrid exception', ['message' => $e->getMessage()]);
            return back()->withErrors(['general' => 'We could not send your request right now. Please try again later.'])->withInput();
        }

        return redirect()->route('unsubscribe.show')->with('ok', 'Thanks! Your unsubscribe request was sent.');
    }

    /**
     * Tiny HTML → plain text fallback for the text part.
     */
    private static function plainTextFromHtml(string $html): string
    {
        $text = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html);
        $text = preg_replace('/<\/(p|div|li)>/i', "\n", $text);
        $text = strip_tags($text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }
}
