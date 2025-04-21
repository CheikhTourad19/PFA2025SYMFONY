<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Pharmacie;
use App\Entity\Adresse;
use App\Enum\Role;
use App\Repository\PharmacieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;

class PharmacieProfilControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;
    private $pharmacieRepository;
    private $user;
    private $pharmacie;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->pharmacieRepository = $this->createMock(PharmacieRepository::class);

        // Create a test user
        $this->user = new User();
        $this->user->setEmail('test@pharmacie.com');
        $this->user->setPassword('oldpassword');
        $this->user->setRole(Role::PHARMACIE);

        // Create a test pharmacie
        $this->pharmacie = new Pharmacie();
        $this->pharmacie->setUser($this->user);
    }

    public function testProfilIndexNotLoggedIn()
    {
        $this->client->request('GET', '/pharmacie/profil');
        $this->assertResponseRedirects('/login');
    }

    public function testProfilIndexRendersCorrectly()
    {
        // Simulate logging in
        $this->client->loginUser($this->user);

        // Mock repository to return our test pharmacie
        $this->pharmacieRepository->method('findOneBy')
            ->willReturn($this->pharmacie);

        // Replace actual services with our mocks in the container
        $container = $this->client->getContainer();
        $container->set('doctrine.orm.entity_manager', $this->entityManager);
        $container->set('security.user_password_hasher', $this->passwordHasher);
        $container->set(PharmacieRepository::class, $this->pharmacieRepository);

        $this->client->request('GET', '/pharmacie/profil');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="user"]');
        $this->assertSelectorExists('form[name="adresse"]');
    }

    public function testProfileUpdateWithValidData()
    {
        $this->client->loginUser($this->user);

        $this->pharmacieRepository->method('findOneBy')
            ->willReturn($this->pharmacie);

        $container = $this->client->getContainer();
        $container->set('doctrine.orm.entity_manager', $this->entityManager);
        $container->set('security.user_password_hasher', $this->passwordHasher);
        $container->set(PharmacieRepository::class, $this->pharmacieRepository);

        // Mock the flush method to expect it to be called
        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->client->request('GET', '/pharmacie/profil');
        $form = $crawler->selectButton('Update Profile')->form([
            'user[email]' => 'updated@pharmacie.com',
            'user[nom]' => 'Updated Name',
            'user[currentPassword]' => '',
            'user[newPassword]' => '',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/pharmacie/profil');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testPasswordUpdateWithValidData()
    {
        $this->client->loginUser($this->user);

        $this->pharmacieRepository->method('findOneBy')
            ->willReturn($this->pharmacie);

        // Mock password hasher to return true for verification and a hashed password
        $this->passwordHasher->method('isPasswordValid')
            ->willReturn(true);
        $this->passwordHasher->method('hashPassword')
            ->willReturn('hashed_new_password');

        $container = $this->client->getContainer();
        $container->set('doctrine.orm.entity_manager', $this->entityManager);
        $container->set('security.user_password_hasher', $this->passwordHasher);
        $container->set(PharmacieRepository::class, $this->pharmacieRepository);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->client->request('GET', '/pharmacie/profil');
        $form = $crawler->selectButton('Update Profile')->form([
            'user[currentPassword]' => 'correct_old_password',
            'user[newPassword]' => 'valid_new_password',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/pharmacie/profil');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testPasswordUpdateWithInvalidCurrentPassword()
    {
        $this->client->loginUser($this->user);

        $this->pharmacieRepository->method('findOneBy')
            ->willReturn($this->pharmacie);

        // Mock password hasher to return false for verification
        $this->passwordHasher->method('isPasswordValid')
            ->willReturn(false);

        $container = $this->client->getContainer();
        $container->set('doctrine.orm.entity_manager', $this->entityManager);
        $container->set('security.user_password_hasher', $this->passwordHasher);
        $container->set(PharmacieRepository::class, $this->pharmacieRepository);

        $crawler = $this->client->request('GET', '/pharmacie/profil');
        $form = $crawler->selectButton('Update Profile')->form([
            'user[currentPassword]' => 'wrong_old_password',
            'user[newPassword]' => 'new_password',
        ]);

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.invalid-feedback', 'Le mot de passe actuel est incorrect.');
    }

    public function testAddressUpdateWithValidData()
    {
        $this->client->loginUser($this->user);

        $this->pharmacieRepository->method('findOneBy')
            ->willReturn($this->pharmacie);

        $container = $this->client->getContainer();
        $container->set('doctrine.orm.entity_manager', $this->entityManager);
        $container->set('security.user_password_hasher', $this->passwordHasher);
        $container->set(PharmacieRepository::class, $this->pharmacieRepository);

        // Expect persist to be called for new address
        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $crawler = $this->client->request('GET', '/pharmacie/profil');
        $form = $crawler->selectButton('Update Address')->form([
            'adresse[rue]' => '123 Test Street',
            'adresse[ville]' => 'Testville',
            'adresse[codePostal]' => '12345',
            'adresse[pays]' => 'Testland',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/pharmacie/profil');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Avoid memory leaks
        $this->client = null;
        $this->entityManager = null;
        $this->passwordHasher = null;
        $this->pharmacieRepository = null;
        $this->user = null;
        $this->pharmacie = null;
    }
}