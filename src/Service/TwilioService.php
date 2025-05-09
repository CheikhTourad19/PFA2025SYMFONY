<?php

namespace App\Service;

use Twilio\Rest\Client;
class TwilioService
{
    private string $account_sid;
    private string $auth_token;
    private string $twilio_number;
    private Client $client;

    public function __construct()
    {
        $this->account_sid =$_ENV['TWILIO_ACCOUNT_SID'];
        $this->auth_token = $_ENV['TWILIO_AUTH_TOKEN'];
        $this->twilio_number = $_ENV['TWILIO_PHONE_NUMBER'];

        // Initialisation du client Twilio
        $this->client = new Client($this->account_sid, $this->auth_token);
    }

    public function sendSms(string $to, string $message): void
    {
        try {
            $this->client->messages->create(
                '+216'.$to,
                [
                    'from' => $this->twilio_number,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            // Gérer ou journaliser l'erreur
            throw new \RuntimeException(sprintf('Erreur lors de l\'envoi du SMS : %s', $e->getMessage()));
        }
    }
}