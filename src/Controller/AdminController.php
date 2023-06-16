<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Commande;
use App\Entity\Vehicule;
use App\Form\MembreType;
use App\Form\VoitureType;
use App\Form\EditCommandeType;
use App\Repository\MembreRepository;
use App\Repository\CommandeRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

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

   


    #[Route('/admin/membre/supprimer/{id}', name: 'membre_supprimer')]
    public function supprimerMembre($id, EntityManagerInterface $manager, MembreRepository $repo)
    {
        $user = $repo->find($id);
        $manager->remove($user);
        $manager->flush();
        $this->addFlash('danger',"Le Membre à bien été supprimé !!!");

        return $this->redirectToRoute('gestion_membre');

    }

    #[Route('/admin/gestion/commande', name: "gestion_commandes_admin")]
    public function gestionCommandesAdmin(CommandeRepository $repo , EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Commande::class)->getFieldNames();

        $commandes = $repo->findAll();
        return $this->render('admin/gestionCommandes.html.twig', [
            "colonnes" => $colonnes,
            "commandes" => $commandes
        ]);
    }



#[Route('/admin/editCommande/{id}', name: 'edit_commande_admin')]
public function editCommande(EntityManagerInterface $manager, Request $request, Commande $commande, VehiculeRepository $vehiculeRepository): Response
{
    if ($commande == null) 
        {
            $commande = new Commande;
        }

        $commande;
        $user = $this->getUser();

    $form = $this->createForm(EditCommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $commande
        ->setDateEnregistrement(new \DateTime())
        // ->setVehicule($vehicule)
        ->setMembre($user);

        $manager->persist($commande);
        $manager->flush();

        return $this->redirectToRoute('gestion_commandes_admin');

    }

    return $this->render('admin/editCommande.html.twig', [
        'commande' => $commande,
        'commandeForm' => $form->createView(),
    ]);
}


#[Route('/admin/membre/edit/{id}' , name: "admin_membre_edit")]
    public function editMembre(Request $request, EntityManagerInterface $manager, Membre $user = null) : Response
    {
        if($user == null)
        {
            // $user = new Membre;
            return $this->redirectToRoute('gestion_membre'); 
        }

        $form = $this->createForm(MembreType::class, $user); 
        $form->handleRequest($request); 
        if($form->isSubmitted() &&$form->isValid())
        {
            $user->setDateEnregistrement(new \DateTime); 
            $manager->persist($user); 
            $manager->flush();
            $this->addFlash('success',"Le rôle de l'utilisateur à bien été modifié"); 
            return $this->redirectToRoute('gestion_membre'); 
        }



        return $this->render('admin/gestionRolesAdmin.html.twig', [
        'form' => $form, 
        'editMode' => $user->getId()!=null
    ]);
    }



   

}

