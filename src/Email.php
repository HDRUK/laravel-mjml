<?php

namespace Hdruk\LaravelMjml;

use Str;
use Config;

use Hdruk\LaravelMjml\Models\EmailTemplate;
use App\Exceptions\MailSendException;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Http;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    private $modelId = 0;
    private $template = null;
    private $replacements = [];
    public $subject = '';

    /**
     * Create a new message instance.
     */
    public function __construct(int $modelId, EmailTemplate $template, array $replacements)
    {
        $this->modelId = $modelId;
        $this->template = $template;
        $this->replacements = $replacements;
        $this->subject = $this->template['subject'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $this->replaceSubjectText();

        return new Envelope(
            from: new Address(Config::get('mjml.email.from_address')),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content by building the mail.
     */
    public function build()
    {
        return $this->html($this->mjmlToHtml());
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function mjmlToHtml(): string
    {
        $this->replaceBodyText();

        $response = Http::withBasicAuth(
            Config::get('mjml.default.access.mjmlApiApplicationKey'),
            Config::get('mjml.default.access.mjmlApiKey'))->post(
                Config::get('mjml.default.access.mjmlRenderUrl'), [
                    'mjml' => $this->template['body'],
        ]);

        if ($response->successful()) {
            return $response->json()['html'];
        }

        throw new MailSendException('unable to contact mjml api - aborting');
    }

    private function replaceBodyText(): void
    {
        // Find all placeholder strings
        preg_match_all('/\[\[.*?\]\]/', $this->template['body'], $matches);
        if (count($matches) > 0) {
            foreach ($matches[0] as $m) {
                if (strpos($m, '.') !== false) {
                    $toReplace = $m;
                    $m = str_replace('[[', '', str_replace(']]', '', $m));
                    $parts = explode('.', $m);

                    $replacementString = '';
                    
                    try {
                        //Get the model class by string name
                        $modelClass = Str::studly(Str::singular($parts[0]));
                        $modelName = '\\App\\Models\\' . $modelClass;

                        $model = $modelName::find($this->modelId);

                        $replacementString = $model->{$parts[1]};
                    } catch(e) {
                        $replacementString = $this->replacements[$toReplace];
                    } finally {
                        $this->template['body'] = str_replace($toReplace, $replacementString, $this->template['body']);
                    }
                }

                if (strpos($m, 'env(') !== false) {
                    $toReplace = $m;
                    $m = str_replace('[[env(', '', str_replace(')]]', '', $m));

                    $this->template['body'] = str_replace($toReplace, env($m), $this->template['body']);
                }
            }
        }
    }

    private function replaceSubjectText(): void
    {
        foreach ($this->replacements as $k => $v) {
            $this->subject = str_replace($k, $v, $this->subject);
        }
    }
}
