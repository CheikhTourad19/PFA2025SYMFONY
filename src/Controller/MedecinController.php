<?php

namespace App\Controller;


use App\Entity\Ordonnance;
use App\Entity\OrdonnanceMedicament;
use App\Repository\MedecinRepository;
use App\Repository\MedicamentRepository;
use App\Repository\OrdonnanceMedicamentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/medecin')]
final class MedecinController extends AbstractController
{
    #[Route('/', name: 'app_medecin_home')]
    public function indexMedecin(): Response
    {
        // Redirection vers la page des tâches
        return $this->redirectToRoute('app_medecin_taches');
    }
    #[Route('/ordonnance', name: 'app_medecin_ordonnace', methods: ['GET'])]
    public function ordonnanceMedecin(MedicamentRepository $medicamentRepository,UserRepository $userRepository): Response
    {
        $medicaments=$medicamentRepository->findAll();
        $patients=$userRepository->findBy(['role'=>'patient']);
        return $this->render('medecin/ordonnance.html.twig',['medicaments'=>$medicaments,'patients'=>$patients]);
    }
    #[Route('/ordonnance', name: 'app_medecin_sauvegarder_ordonnance', methods: ['POST'])]
    public function save(EntityManagerInterface $entityManager,UserRepository $userRepository,MedicamentRepository $medicamentRepository,Request $request): Response
    {
        $medecin=$this->getUser()->getMedecin();
        $patient=$userRepository->find($request->request->get('patient'));
        $date=$request->request->get('date');
        $medicaments = $request->request->all('medicaments');
        $quantites = $request->request->all('quantites');
        $instructions = $request->request->all('instructions');
        $ordonnance=new Ordonnance();
        $ordonnance->setPatient($patient);
        $ordonnance->setMedecin($medecin);
        $ordonnance->setDateCreation(new \DateTime($date));
        for($i=0;$i<count($medicaments);$i++){
            $med=$medicamentRepository->find($medicaments[$i]);
            $ordmed=new OrdonnanceMedicament();
            $ordmed->setMedicament($med);
            $ordmed->setOrdonnance($ordonnance);
            $ordmed->setQuantite($quantites[$i]);
            $ordmed->setInstructions($instructions[$i]);
            $entityManager->persist($ordmed);
        }
        $entityManager->persist($ordonnance);
        $entityManager->flush();
        $this->addFlash('success','Ordonnance cree avec succes');
        return $this->redirectToRoute('app_medecin_ordonnance');
    }


    #[Route('/taches', name: 'app_medecin_taches')]
    public function taches(): Response
    {
        return $this->render('medecin/task.html.twig');
    }

    #[Route('/dossier-medical', name: 'app_medecin_dossier_medical')]
    public function dossierMedical(): Response
    {
        return $this->render('medecin/dossier_medical.html.twig');
    }

    #[Route('/messages', name: 'app_medecin_messages')]
    public function messages(): Response
    {
        return $this->render('medecin/messages.html.twig');
    }

    #[Route('/rendez-vous', name: 'app_medecin_rendez_vous')]
    public function rendezVous(): Response
    {
        return $this->render('medecin/rendez_vous.html.twig');
    }

    #[Route('/profil', name: 'app_medecin_profil')]
    public function profil(): Response
    {
        return $this->render('medecin/profil.html.twig');
    }

    #[Route('/ordonnance', name: 'app_medecin_ordonnance')]
    public function ordonnance(): Response
    {
        return $this->render('medecin/ordonnance.html.twig');
    }

    #[Route('/calendrier', name: 'app_medecin_calendrier')]
    public function calendrier(): Response
    {
        return $this->render('medecin/calendrier.html.twig');
    }

    #[Route('/deconnexion', name: 'app_medecin_deconnexion')]
    public function logout(): void
    {
        throw new \LogicException('This should be intercepted by the firewall.');
    }
}
