<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChannelCredential;

use Symfony\Component\Mime\Part\TextPart;


class DynamicMailerService
{
    // protected ChannelCredential $credential;


    protected ?ChannelCredential $credential = null;


 



    public function __construct(Model $credentialable, string $channel = 'email', string $provider = null)
    {
        $this->credential = $credentialable->channelCredentials()
            ->forChannel($channel)
            // ->forProvider($provider)
            ->where('status', 'active')
            ->first();

        if (app()->environment('local') || config('app.debug')) {
            Log::info('Loaded Mail Credentials', [
                'credential' => $this->credential?->toArray(),
                'for_model' => class_basename($credentialable),
                'model_id' => $credentialable->id ?? null
            ]);
        }

        if (!$this->credential) {
            $type = class_basename($credentialable);
            $id = $credentialable->id ?? 'unknown';
            Log::warning("No active mail credentials found for {$type} ID: {$id}");
            throw new \Exception("No active mail credentials found for {$type} ID: {$id}");
        }

        $this->configureMailer();
    }


    protected function configureMailer(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $this->credential->getMetaValue('host') ?? 'smtp.mailtrap.io',
            'mail.mailers.smtp.port' => $this->credential->getMetaValue('port') ?? 587,
            'mail.mailers.smtp.encryption' => $this->credential->getMetaValue('encryption') ?? 'tls',
            'mail.mailers.smtp.username' => $this->credential->user_name,
            'mail.mailers.smtp.password' => $this->credential->password,
            'mail.from.address' => $this->credential->email_address,
            'mail.from.name' => $this->credential->description ?? config('app.name', 'No Name'),
        ]);
    }

    public function sendMail(array $mailData): void
    {
        try {
            Mail::send([], [], function ($message) use ($mailData) {
                $message->to($mailData['to'])
                    ->subject($mailData['subject']);
                $message->setBody(new TextPart($mailData['body'], 'utf-8', 'html'));


                if (!empty($mailData['cc'])) {
                    $message->cc($mailData['cc']);
                }

                if (!empty($mailData['bcc'])) {
                    $message->bcc($mailData['bcc']);
                }

                if (!empty($mailData['attachments'])) {
                    foreach ($mailData['attachments'] as $filePath => $name) {
                        $message->attach($filePath, ['as' => $name]);
                    }
                }
            });

            if (config('app.debug')) {
                Log::info("Email sent successfully to {$mailData['to']}");
            }
        } catch (\Throwable $e) {
            Log::error("Dynamic Mail Send Error: " . $e->getMessage(), [
                'to' => $mailData['to'] ?? null,
                'subject' => $mailData['subject'] ?? null
            ]);
            throw $e;
        }
    }
}
