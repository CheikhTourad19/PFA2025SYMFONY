<?php

namespace App\Controller;

use App\Entity\OrdonnanceMedicament;
use App\Entity\Patient;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\PatientRepository;
use App\Repository\PharmacieRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    #[Route('/pharmacie/ordonnance',  name: 'app_pharma_ordonnance')]
    public function Ordonnanceindex(): Response
    {
        $user = $this->getUser();

        return $this->render('pharmacie/index.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/pharmacie/profil',  name: 'app_pharma_profil')]
    public function profilindex(): Response
    {
        $user = $this->getUser();

        return $this->render('pharmacie/index.html.twig', [
            'user' => $user,
        ]);
    }

}
