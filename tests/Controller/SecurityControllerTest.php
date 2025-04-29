<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginAvecBonneInfo(): void
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

    public function testLoginAvecInvalidMDP(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            '_username' => 'ali@gmail.com',
            '_password' => 'wrong_password'
        ]);

        $this->assertResponseRedirects('/login');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.error-m', 'Identifiants invalides.');

    }
}