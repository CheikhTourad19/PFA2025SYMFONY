<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class SecurityControllerTest extends WebTestCase
{
        public function testLoginWithValidCredentials(): void
        {
                $client = static::createClient();

                // Récupérer un utilisateur réel depuis la base
                $userRepository = static::getContainer()->get(UserRepository::class);
                $testUser = $userRepository->findOneByEmail('ali@gmail.com');

                $this->assertNotNull($testUser, 'User should exist in database for this test.');

                // Se rendre sur la page de login
                $crawler = $client->request('GET', '/login');
                $this->assertResponseIsSuccessful();

                // Soumettre le formulaire de login
                $form = $crawler->selectButton('Se connecter')->form([
                '_username' => 'ali@gmail.com',
                '_password' => '12345678', // mot de passe réel de ton utilisateur
                ]);
                $client->submit($form);

                // Vérifie la redirection après connexion
                $this->assertResponseRedirects('/patient'); // ou "/" selon ta config

                // Suivre la redirection
                $client->followRedirect();

                // Vérifie que l'utilisateur est connecté (par exemple, présence d'un bouton "Déconnexion")
                $this->assertSelectorExists('a:contains("Accueil")');
                }

    public function testLoginWithInvalidPassword(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user@example.com');

        $this->assertNotNull($testUser, 'User should exist in database for this test.');

        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'user@example.com',
            '_password' => 'wrongpassword', // mauvais mot de passe
        ]);
        $client->submit($form);

        // Devrait revenir sur la page de login avec un message d'erreur
        $this->assertResponseRedirects('/login');

        $client->followRedirect();

        // Vérifie la présence du message d'erreur
        $this->assertSelectorTextContains('.erorr-m', 'Identifiants invalides'); // adapte le sélecteur et le message
    }
        }
?>