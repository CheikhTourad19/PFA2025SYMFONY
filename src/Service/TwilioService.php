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
        $this->account_sid ='ACd4dfc19fc022d1da1b26a143cb6f38a5';
        $this->auth_token = '0bc8c0ba904551f1acd1c801b40e3567';
        $this->twilio_number = "+19346473529";

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