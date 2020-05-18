<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProduitRepository;
use App\Repository\EtatRepository;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use Doctrine\ORM\EntityManagerInterface as EntityManager;


class PanierController extends AbstractController
{
    


    // USER ONLY
    /**
     * @Route("/profile/panier", name="panier")
     */
    public function index(SessionInterface $session, ProduitRepository $pr)
    {
        // on veut afficher le panier de l'utilisateur 
        //récupérer ce qu'il ya dans la session et créer un panier pour ajouter les produits selectionnés

        // on verifie si il ya une donnée  panier dans la session, sinon on recupère par default un tableau vide
        $panier = $session->get('panier', []);
        $panierData = [];// je cree un tableau qui contiendra les donnéss d'un produit
        foreach($panier as $id => $quantite){// je pioche dans mon panier pour extraire l id et la quantite du ce produit qui vont remplir mon nouveau panierData
            $panierData[] = [
                'produit' => $pr->find($id),// requête sur le produit avec son identifiant
                'quantite' => $quantite
            ];
        }

        // calculer le total prix * quantite
        $total = 0;// on commence avec un total 0
        // Pour calculer le prix total de chaque produit, on fait un foreach pour piocher les infos (prix et quantite) d'un produit dans le panierData 
        foreach($panierData as $item){
            
            $totalItem = $item['produit']->getPrix() * $item['quantite'];// items c'est la liste des elements correspondant au panierdata
            $total += $totalItem;
        }

        return $this->render('panier/index.html.twig', [
            'items' => $panierData,
            'total' => $total
        ]);            
    }

    // USER ONLY
    /**
     * @Route("/profile/panier/ajouter/{id}", name="panier_ajouter")
     */
    public function ajouter($id,SessionInterface $session, Request $request)
    {
        $q = $request->request->get('quantite');
        //recupérer les donnees du panier dans la session, sous un tabeleau avec données ou vide
        $panier = $session->get('panier', []);

        // si on on a deja un produit avec cet id 
        if(!empty($panier[$id])){
            $panier[$id] = $panier[$id]+$q;		
        } 
        else{ //si je n'ai pas de produit avec id
            $panier[$id]= $q;
        }
        $session->set('panier', $panier); // remplacer ce qu'il y avait dans la panier par la nouvelle valeur
        return $this->redirectToRoute("panier");
    }
    
    // USER ONLY
    /**
     * @Route("/profile/panier/supprimer/{id}", name="panier_supprimer")
     */
    public function supprimer($id,SessionInterface $session)
    {
        // extraire mon panier ds la session
        $panier = $session->get('panier', []);
        
        //si on a un article avec cette identifaint on le supprime 
        if(!empty($panier[$id])){
            // supprimer panier[id]
            unset($panier[$id]);
        } // nouveau panier apres suppresion
        $session->set('panier', $panier); // nouveau panier
        
        //retour à la laiste panier
        return $this->redirectToRoute("panier");   
    }

    // USER ONLY
    /**
     * @Route("/profile/panier/validation/", name="panier_validation")
     */
    public function validation(EntityManager $em, SessionInterface $session, ProduitRepository $pr, EtatRepository $er)
    {
        // on recupére le user connecter
        $user = $this->getUser();
        // On recupére le panier
        $panier = $session->get('panier', []);
        $error = "";

        // condition de quantiter du panier
        foreach ($panier as $key => $value) {
            $produit = $pr->find($key);
            if ($produit->getStock() < $value) {
                
                $error .= "Stock du produit ". $produit->getTitre(). " inssufisant, ";
                $panier[$key] =  $produit->getStock();	
                $session->set('panier', $panier);
            }
        }
        if(isset($error) && !empty($error)){
            $error .= "stock ajusté";
            return $this->redirectToRoute("panier", [
                "erreur" => $error
            ]);
        }else{
            // on crée la commande 
            $commande = new Commande;
            $commande->setUser($user);
            $total = 0;
            foreach ($panier as $key => $value) {
                $total = $total + $produit->getPrix()*$value;
            }
            $commande->setTotal($total);
            $commande->setEtat($er->find(1));
            $commande->setDateEnregistrement(new \DateTime('now'));
            $em->persist($commande);

            // on crée le detail de la commande
            foreach ($panier as $key => $value) {
                // on ajoute un detail pour chaque produit du panier
                $detailCommande = new DetailCommande;
                $produit = $pr->find($key);
                // dd($produit);
                $detailCommande->setProduit($produit);
                $detailCommande->setQuantite($value);
                $detailCommande->setPrixUnique($produit->getPrix());
                $detailCommande->setPrixTotal($produit->getPrix()*$value);
                $detailCommande->setCommande($commande);
                $em->persist($detailCommande);
                
                // on retire le stock au produit
                $produit->setStock($produit->getStock()-$value);
                $em->persist($produit);

            }
            $em->flush();
            $session->clear();
            return $this->redirectToRoute("panier");
        }   
    }


}
