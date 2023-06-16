<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Vehicule;
use App\Form\MembreType;
use App\Form\VoitureType;
use App\Repository\MembreRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }



    #[Route('/admin/vehicule/new', name:'new_vehicule')]
    #[Route('/admin/vehicule/edit/{id}', name:'edit_vehicule')]
    public function formVoiture(Request $globals, EntityManagerInterface $manager, Vehicule $vehicule = null)
    {
        if($vehicule ==null):
            $vehicule = new Vehicule;
        endif;


        $form= $this->createForm(VoitureType::class, $vehicule);
        $form->handleRequest($globals);

        //dump($vehicule);
        if($form->isSubmitted() && $form->isValid())
        {
            $vehicule->setDateEnregistrement(new \DateTime);
            $manager->persist($vehicule);
            $manager->flush();
            $this->addFlash('success',"le vehicule à bien été enregistré");
           
            return $this->redirectToRoute('vehicules');
        }

        return $this->render("admin/form.html.twig", [
            "form" => $form,
            "editMode" => $vehicule->getId() !== null
        ]);
    }


    #[Route('/admin/vehicule/gestion', name: 'gestion_vehicule')]
    public function gestionVehiucle(VehiculeRepository $repo, EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Vehicule::class)->getFieldNames(); //* récupere les colonnes pour faire le tableau (ex: id , prenom, nom, etc )
        $vehicule = $repo->findAll();
        return $this->render('admin/gestionVehicule.html.twig', [
            "colonnes" => $colonnes,
            "vehicules" => $vehicule
        ]);
    }

    #[Route('/admin/vehicule/delete/{id}', name: "vehicule_delete")]
    public function deleteVehicule(Vehicule $vehicule, EntityManagerInterface $manager)
    {
        $manager->remove($vehicule);
        $manager->flush();
        $this->addFlash('success',"Le Vehicule à bien été supprimé !!!");
        return $this->redirectToRoute('gestion_vehicule');
    }

    #[Route('/admin/membre/gestion', name: 'gestion_membre')]
    public function gestionMembre(MembreRepository $repo, Request $request): Response
    {
        
        $user = $repo->findAll();

        
        
        return $this->render('admin/gestionMembre.html.twig', [
            'membre' => $user
        ]);
    }

    #[Route("/admin/membre/edit/{id}", name:"edit_membre")]
    public function formMembre(Request $request, EntityManagerInterface $entityManager, Membre $user = null): Response
    {
        if($user == null)
        {
            $user = new Membre();
        }       
        $form = $this->createForm(MembreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $statut = $form->get('statut')->getData();

            if ($statut == 1) {
                $role = 'ROLE_ADMIN';
            } elseif ($statut == 2) {
                $role = 'ROLE_USER';
            } else {
                
                $role = 'ROLE_USER';
            }

            $user->setRoles([$role]);

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('gestion_membre');
        }

        return $this->render('admin/gestionRolesAdmin.html.twig', [
            'membreForm' => $form->createView(),
            "editMode" => $user->getId() !== null,
            'user' => $user
        ]);
    }


    #[Route('/admin/membre/supprimer/{id}', name: 'membre_supprimer')]
    public function supprimerMembre($id, EntityManagerInterface $manager, MembreRepository $repo)
    {
        $user = $repo->find($id);
        $manager->remove($user);
        $manager->flush();
        $this->addFlash('danger',"Le Membre à bien été supprimé !!!");

        return $this->redirectToRoute('gestion_membre');

    }
}

