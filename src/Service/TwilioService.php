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
        $this->account_sid ='AC825d525cdf9f2f46b043243099abbb79';
        $this->auth_token = 'bc5f3c4a32f604bf1b4c9e4866cc85d3';
        $this->twilio_number = "+19043641046";

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