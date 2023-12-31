<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Vehicule;
use App\Form\VoitureType;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VoitureController extends AbstractController
{
    #[Route('/', name: 'vehicules')]
    public function index(VehiculeRepository $repo): Response
    {
        $vehicule = $repo->findBy([],['date_enregistrement' => "DESC"]);
        return $this->render('voiture/index.html.twig', [
            'voitures' => $vehicule
        ]);
    }


    #[Route("/vehicule/voir/{id}", name: "voir_vehicule")]
    public function show( Vehicule $vehicule =null) :Response
    {
        if($vehicule == null)
        {
            return $this->redirectToRoute('vehicules');
        }

        return $this->render('voiture/voirVehicule.html.twig', [
            'vehicule' => $vehicule
        ]);
    }


    #[Route('/commande/new/{id}', name: 'form_commande')]
    public function formCommande(EntityManagerInterface $manager, Request $request,Vehicule $vehicule = null): Response 
    {
        if ($vehicule == null) 
        {
            return $this->redirectToRoute('voir_vehicule');
        }

        $commande = new Commande();
        $membre = $this->getUser();

    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $dateDebut = $commande->getDateHeureDepart();
        $dateFin = $commande->getDateHeureFin();
        $nombreJours = $dateFin->diff($dateDebut)->days;

        $prixJournalier = $vehicule->getPrixJournalier();
        $prixTotal = $prixJournalier * $nombreJours;

        $commande
            ->setDateEnregistrement(new \DateTime)
            ->setPrixTotal($prixTotal)
            ->setVehicule($vehicule)
            ->setMembre($membre);

        $manager->persist($commande);
        $manager->flush();

        return $this->redirectToRoute('gestion_commandes');
    }

    return $this->render('voiture/commandeTest.html.twig', [
        'commandeForm' => $form->createView(),
        'vehicule' => $vehicule
    ]);
    }



    #[Route('/commande/edit/{id}', name: 'edit_commande')]
public function formCommandeEdit(EntityManagerInterface $manager, Request $request, Commande $commande = null): Response 
{
    if ($commande == null) 
    {
        return $this->redirectToRoute('vehicules');
    }

    $membre = $this->getUser();

    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $dateDebut = $commande->getDateHeureDepart();
        $dateFin = $commande->getDateHeureFin();
        $nombreJours = $dateFin->diff($dateDebut)->days;

        $prixJournalier = $commande->getVehicule()->getPrixJournalier();
        $prixTotal = $prixJournalier * $nombreJours;

        $commande
            ->setDateEnregistrement(new \DateTime)
            ->setPrixTotal($prixTotal)
            ->setMembre($membre);

        $manager->persist($commande);
        $manager->flush();

        return $this->redirectToRoute('gestion_commandes');
    }

    return $this->render('voiture/commandeAjouter.html.twig', [
        'commandeForm' => $form->createView(),
        'editForm' => $commande->getId() !== null,
        'vehicule' => $commande->getVehicule()
    ]);
}



    
    
    #[Route('/gestion/commandes', name: 'gestion_commandes')]
    public function gestionCommandes(CommandeRepository $commandeRepository): Response
    {
        // Récupérez toutes les commandes de l'utilisateur actuel
        $commandes = $commandeRepository->findBy(['membre' => $this->getUser()]);
        
        // Affichez la page d'affichage des commandes de l'utilisateur
        return $this->render('/voiture/commandeAfficher.html.twig', [
            'commandes' => $commandes
        ]);
    }

    

    #[Route('/admin/commande/delete/{id}', name: 'delete_commande')]
    public function deleteCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($commande);
        $entityManager->flush();
        $this->addFlash('danger', 'La commande a été supprimée avec succès !');

        // Rediriger vers la page de gestion des commandes ou une autre page appropriée
        return $this->redirectToRoute('afficher_commandes');
    }
 
}




        