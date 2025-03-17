<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Medicament;
use App\Entity\OrdonnanceMedicament;
use App\Entity\Patient;
use App\Entity\Stock;
use App\Entity\User;
use App\Form\AdresseType;
use App\Form\UserType;
use App\Repository\MedicamentRepository;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\OrdonnanceRepository;
use App\Repository\PatientRepository;
use App\Repository\PharmacieRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PharmaController extends AbstractController
{
    #[Route('/pharmacie',  name: 'app_home_pharmacie')]
    public function index(): Response
    {


        return $this->render('pharmacie/index.html.twig', [
        ]);
    }
    #[Route('/pharmacie/stock',  name: 'app_pharma_stock')]
    public function Stockindex(StockRepository $stockRepository, PharmacieRepository $f): Response
    {
        $user = $this->getUser();
        $stocks = $stockRepository->createQueryBuilder('s')
            ->select('s.id', 's.quantite', 'm.nom', 'm.description', 'm.prix')
            ->join('s.medicament', 'm')
            ->where('s.pharmacie = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        return $this->render('pharmacie/stock.html.twig', [
            'stocks' => $stocks,
        ]);
    }
    #[Route('/pharmacie/stock/update-stock/{id}',  name: 'app_pharma_update_stock')]
    public function updatestock( StockRepository $stockRepository, Request $request ,int $id,EntityManagerInterface $em): Response
    {
        $addes=$request->request->get('additional_quantity');
        $stock = $stockRepository->find($id);

        if ($stock && is_numeric($addes) && $addes > 0) {
            $stock->setQuantite($stock->getQuantite() + $addes);
            $em->persist($stock);
            $em->flush();
            $this->addFlash('success', 'Stock mis à jour avec succès pour  '.$stock->getMedicament()->getNom());
        } else {
            $this->addFlash('error', 'Échec de la mise à jour du stock.');
        }
        return $this->redirectToRoute('app_pharma_stock');

    }

    #[Route('/pharmacie/profil', name: 'app_pharma_profil')]
    public function profilindex(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        PharmacieRepository $pharmacieRepository
    ): Response {
        // Fetch the current user
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Fetch the user's pharmacy and address
        $pharmacie = $pharmacieRepository->findOneBy(['user' => $user]);
        $adresse = $pharmacie ? $pharmacie->getAdresse() : new Adresse();

        // Create the profile form with the current user data
        $profileForm = $this->createForm(UserType::class, $user);
        $profileForm->handleRequest($request);

        // Handle profile form submission
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            // Handle password update if provided
            $currentPassword = $profileForm->get('currentPassword')->getData();
            $newPassword = $profileForm->get('newPassword')->getData();

            if ($currentPassword && $newPassword) {
                if (strlen($newPassword) < 8) {
                    $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
                }
                // Verify the current password
                else if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Le mot de passe actuel est incorrect et information non enregistrés');
                } else {
                    // Hash and set the new password
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
                }
            }

            // Save the updated user to the database
            $entityManager->persist($user); // Ensure the user is persisted
            $entityManager->flush();

            // Add a flash message for success
            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_pharma_profil');
        }

        // Create the address form with the current address data
        $addressForm = $this->createForm(AdresseType::class, $adresse);
        $addressForm->handleRequest($request);

        // Handle address form submission
        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            // Associate the address with the pharmacy if not already associated
            if ($pharmacie && !$pharmacie->getAdresse()) {
                $pharmacie->setAdresse($adresse);
            }

            // Save the updated address to the database
            $entityManager->persist($adresse);
            $entityManager->flush();

            // Add a flash message for success
            $this->addFlash('success', 'Votre adresse a été mise à jour avec succès.');
            return $this->redirectToRoute('app_pharma_profil');
        }
        // Render both forms in the same template
        return $this->render('pharmacie/profile.html.twig', [
            'profileForm' => $profileForm->createView(),
            'addressForm' => $addressForm->createView(),
        ]);
    }

    #[Route('/pharmacie/ordonnance', name: 'app_pharma_ordonnance')]
    public function Ordonnanceindex(Request $request, OrdonnanceMedicamentRepository $om, OrdonnanceRepository $or): Response
    {
        // Initialize variables
        $orm = []; // Holds the list of medications
        $error = null; // Holds error messages
        $id = $request->query->get('ordonnance_id');; // Holds the submitted ID

        // Check if the form is submitted
        if ($id) {


                // Find the ordonnance by ID
                $ordonnance = $or->find($id);

                if (!$ordonnance) {
                    $error = "Aucune ordonnance trouvée pour l'ID $id.";
                    $this->addFlash('error', $error);
                } else {
                    // Fetch the associated medications
                    $orm = $om->findBy(['ordonnance' => $ordonnance]);

                    if (empty($orm)) {
                        $error = "Aucun médicament trouvé pour cette ordonnance.";
                        $this->addFlash('error', $error);
                    }
                }

        }

        // Render the template with data
        return $this->render('pharmacie/ordonnance.html.twig', [
            'orm' => $orm, // List of medications
            'id' => $id, // Submitted ID (to repopulate the form)
        ]);
    }

    #[Route('/pharmacie/stock/add-medicament', name: 'app_pharma_add_medicament')]
    public function addMedicament(Request $request, EntityManagerInterface $entityManager, PharmacieRepository $pharmacieRepository, MedicamentRepository $medicamentRepository, StockRepository $stockRepository): Response
    {
        $pharmacie = $pharmacieRepository->findOneBy(['user' => $this->getUser()]);

        // Récupérer les données du formulaire
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');
        $prix = $request->request->get('prix');
        $quantite = (int)$request->request->get('quantite');

        // Vérifier si le médicament existe déjà
        $med = $medicamentRepository->findOneBy(['nom' => $nom]);
        if ($med == null) {
            // Si le médicament n'existe pas, on le crée
            $med = new Medicament();
            $med->setNom($nom);
            $med->setDescription($description);
            $med->setPrix($prix);
            $entityManager->persist($med);
            $entityManager->flush(); // Flush ici pour obtenir l'ID du médicament
        }

        // Vérifier si un stock pour ce médicament existe déjà dans cette pharmacie
        $existingStock = $stockRepository->findOneBy([
            'pharmacie' => $pharmacie,
            'medicament' => $med
        ]);

        if ($existingStock) {
            // Si le stock existe déjà, mettre à jour la quantité
            $existingStock->setQuantite($quantite); // ou utilisez $existingStock->setQuantite($existingStock->getQuantite() + $quantite); pour ajouter à la quantité existante
            $entityManager->persist($existingStock);
        } else {
            // Si le stock n'existe pas, créer un nouveau
            $stock = new Stock();
            $stock->setMedicament($med);
            $stock->setQuantite($quantite);
            $stock->setPharmacie($pharmacie);
            $entityManager->persist($stock);
        }

        // Persister les changements
        $entityManager->flush();

        // Ajouter un message flash
        $this->addFlash('success', 'Le médicament a été ajouté avec succès.');

        // Rediriger vers la page de stock
        return $this->redirectToRoute('app_pharma_stock');
    }

}
