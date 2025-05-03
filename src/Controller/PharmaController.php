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
use Symfony\Component\Form\FormError;
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
    #[Route('/pharmacie/contact',  name: 'app_pharma_contact')]
    public function contact(): Response
    {

        return $this->render('pharmacie/contact.html.twig', [
        ]);
    }
    #[Route('/pharmacie/stock',  name: 'app_pharma_stock')]
    public function Stockindex(StockRepository $stockRepository,MedicamentRepository $medicamentRepository): Response
    {
        $user = $this->getUser();
        $stocks = $stockRepository->createQueryBuilder('s')
            ->select('s.id', 's.quantite', 'm.nom', 'm.description', 'm.prix')
            ->join('s.medicament', 'm')
            ->where('s.pharmacie = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();
        $medicaments=$medicamentRepository->findAll();
        return $this->render('pharmacie/stock.html.twig', [
            'stocks' => $stocks,
            'medicaments'=>$medicaments,
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
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupération de la pharmacie et de l'adresse
        $pharmacie = $pharmacieRepository->findOneBy(['user' => $user]);
        $adresse = $pharmacie ? $pharmacie->getAdresse() ?? new Adresse() : new Adresse();

        // Formulaire du profil utilisateur
        $profileForm = $this->createForm(UserType::class, $user);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $currentPassword = $profileForm->get('currentPassword')->getData();
            $newPassword = $profileForm->get('newPassword')->getData();

            if ($currentPassword && $newPassword) {
                if (strlen($newPassword) < 8) {
                    $profileForm->get('newPassword')->addError(new FormError('Le nouveau mot de passe doit contenir au moins 8 caractères.'));
                    $this->addFlash('error','Mot de passe doit contenir 8 caractere au minimum');
                } elseif (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $profileForm->get('currentPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
                    $this->addFlash('error','Mot de passe invalide');
                } else {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
                }
            }

            $entityManager->flush(); // Pas besoin de persist sur un user déjà managé
            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_pharma_profil');
        }

        // Formulaire de l'adresse
        $addressForm = $this->createForm(AdresseType::class, $adresse);
        $addressForm->handleRequest($request);

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            if ($pharmacie && !$pharmacie->getAdresse()) {
                $pharmacie->setAdresse($adresse);
            }

            $entityManager->persist($adresse); // Utile si nouvelle adresse
            $entityManager->flush();

            $this->addFlash('success', 'Votre adresse a été mise à jour avec succès.');
            return $this->redirectToRoute('app_pharma_profil');
        }

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
                $orm=$om->findBy(['ordonnance'=>$id]);
                if (!$orm || count($orm) == 0) {
                    $error = "Aucune ordonnance trouvée pour l'ID $id.";
                    $this->addFlash('error', $error);
                } else {
                    // Fetch the associated medications
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
        $quantite = (int)$request->request->get('quantite');
        $medicament = $medicamentRepository->find($request->request->get('nom'));
        $existingstock = $stockRepository->findOneBy(['medicament' => $medicament, 'pharmacie' => $pharmacie]);

        if ($existingstock) {
            $this->addFlash('error', 'Vous avez deja ce medicament dans votre stock');;
            return $this->redirectToRoute('app_pharma_stock');
        }
        $stock = new Stock();
        $stock->setQuantite($quantite);
        $stock->setMedicament($medicament);
        $stock->setPharmacie($pharmacie);
        // Persister les changements

        $entityManager->persist($stock);
        $entityManager->flush();

        // Ajouter un message flash
        $this->addFlash('success', 'Le médicament a été ajouté avec succès.');

        // Rediriger vers la page de stock
        return $this->redirectToRoute('app_pharma_stock');
    }


}
