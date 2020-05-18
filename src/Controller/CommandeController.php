<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommandeRepository;
use App\Repository\EtatRepository;
use App\Repository\DetailCommandeRepository;
use Symfony\Component\HttpFoundation\Request; 
use Doctrine\ORM\EntityManagerInterface as EntityManager;

class CommandeController extends AbstractController
{
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commande", name="commande")
     */
    public function index(CommandeRepository $cr,EtatRepository $er)
    {
        // On envoi sous form d'array la liste de toutes les commandes et des etats
        return $this->render('commande/index.html.twig', [
            'liste_commande'=> $cr->findAll(),
            'etats' => $er->findAll()
        ]);
    }
    

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commande/etat/verif", name="commande_verif")
     */
    public function etat( EntityManager $em,Request $rq,EtatRepository $er,CommandeRepository $cr)
    {
        // On recupére lors de la validation du formulaire dans la liste des commandes
        // l'id de la commande
        $idCommande = $rq->request->get('idCommande');
        // si on recupéré un array dans la base
        if ($cr->find($idCommande)) {
            // alors on defini $commande l'array de la commande
            $commande = $cr->find($idCommande);
        }
        
        // l'id de l'etat
        $idEtat = $rq->request->get('idEtat');
        // si on recupéré un array dans la base
        if ($er->find($idEtat)) {
            // alors on defini $etat l'array de la etat selectionné dans le form
            $etat = $er->find($idEtat);
        }

        // si on a bien un $etat et une $commande alors
        if (isset($etat) && isset($commande)) {
            // on defini le nouvelle etat a la commande et on l'envoi en bdd
            $commande->setEtat($etat);
            $em->persist($commande);
            $em->flush();
            // et on revien a la liste des commandes
            return $this->redirectToRoute("commande");
        }
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commande/detail/{id}", name="commande_detail",  requirements={"id"="\d+"})
     */
    public function detail($id,CommandeRepository $cr,DetailCommandeRepository $dcr)
    {
        // On veut afficher sur cette page la liste de tout les produit de la commande 
        // on recupére la commande
        $commande = $cr->find($id);
        // on recupére le detail de la commande 
        $details = $dcr->findDetailFromCom($commande);
        // On envoi les donnée sur la page de la fiche
        return $this->render('commande/fiche.html.twig', [
            'commande' => $commande,
            'details' => $details,
        ]);  
    }











    // USER ACCESS
    /**
     * @Route("/profile/commande", name="panier_commande")
     */
    public function commande(CommandeRepository $cr, DetailCommandeRepository $dcr)
    {
        // dd($cr->findAllFromUser($this->getUser()));
        // on recupére le user connecter      
        $detail = [];
        $commandes = $cr->findAllFromUser($this->getUser());
        foreach ($commandes as $value) {
            $id = $value->getId();
            // dd($dcr->findDetailFromCom($id));
            // $id = new Commande->getId();
            array_push($detail, $dcr->findDetailFromCom($id));
        }
        return $this->render('panier/commande.html.twig', [
            'details' => $detail,
            'commandes' => $commandes,
        ]);   
    }
}
