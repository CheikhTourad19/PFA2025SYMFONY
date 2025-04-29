<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            '_username' => 'ali@gmail.com',
            '_password' => '12345678',
        ]);

        $this->assertResponseRedirects('/patient');
        $crawler = $client->followRedirect();
        $this->assertSelectorExists('button:contains("Déconnexion")');
        $this->assertSelectorExists('a:contains("Accueil")');
    }

    public function testLoginWithInvalidPassword(): void
    {
        // Create client with reboot
        $client = static::createClient();

        // Access login page
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        // Submit form with invalid credentials
        $client->submitForm('Se connecter', [ // Changed to match the button text in first test
            '_username' => 'ali@gmail.com',
            '_password' => 'wrong_password'
        ]);

        // Verify redirect after failed login
        $this->assertResponseRedirects('/login');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.error-m', 'Identifiants invalides.');

    }
}