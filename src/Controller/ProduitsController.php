<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository as ProduitRepo;
use App\Repository\CommentaireRepository as CommRepo;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\Image;

class ProduitsController extends AbstractController
{
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/produit", name="produit")
     */
    public function index(ProduitRepo $pr)
    {
        return $this->render('produit/index.html.twig', [
            'liste_produits' => $pr->findAll()
        ]);
    }
    
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/produit/new", name="produit_new")
     */
    public function new(Request $rq, EntityManager $em)
    {
        $nvProduit = new Produit;
        $formProduit = $this->createForm(ProduitType::class, $nvProduit);
        $formProduit->handleRequest($rq);
        if($formProduit->isSubmitted() && $formProduit->isValid()){
           //dd($nvProduit);
           $imageTelechargee = $formProduit->get("image")->getData();
          
            if($imageTelechargee){// si une image a été téléchargée...
               
                $nouveauNom  = pathinfo($imageTelechargee->getClientOriginalName(), PATHINFO_FILENAME); // récupérer nom fichier telecharge
               
               // je remplace espace par "_"
                $nouveauNom = str_replace(" ", "_", $nouveauNom);// remplacer espace par _
               // je rajpute un string uniue pour eviter d'avoir des doublons et je rajoute l'extention du fichier
                $nouveauNom .= "_".uniqid() . ".". $imageTelechargee->guessExtension();// renome la photo aec nom image /id/extension
               
               //j'enregistre l'image telecharge sur mo, server ds le dosseir public/img
                $imageTelechargee->move($this->getparameter("dossier_images"), $nouveauNom);
             

                $newImage = new Image;
                $newImage->setImage01($nouveauNom);
                $newImage->setProduit($nvProduit);

            }

            $em->persist($nvProduit);
            $em->persist($newImage);
            $em->flush();
            //$this->addFlash("success", "le produit a bien été ajouté") ;
          
            return $this->redirectToRoute("produit");
        }
        return $this->render('produit/form.html.twig', [
            'form' => $formProduit->createView(),  "bouton" => "Enregistrer"
        ]);
    }

    /**
     * @Route("/fiche/produit/{id}", name="produit_fiche", requirements={"id"="\d+"})
     */
      /// a cause de requirements {"id"="\d+"} cela veut dire que l id doit etre composé de 1 ou plusieurs chiffres
      public function fiche(ProduitRepo $pr, $id, CommRepo $cr,EtatRepository $er){
        // recordrepository pour recupérer les infos de la base de deonnées
       $produit = $pr->find($id);
       $commentaires = $cr->findCommByProd($id);
       $etats = $er->findAll();

       if (!empty($produit)){
           return $this->render("produit/fiche.html.twig", [ 
               "produit" => $produit ,
                'etats' => $etats,
                "commentaires" => $commentaires,
            ]);
          

       }
       return $this->redirectToRoute("produit");
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/produit/modifier/{id}", name="produit_modifier",  requirements={"id"="\d+"})
     */

    public function modifier(Request $rq, EntityManager $em,ProduitRepo $pr, $id){
      
        $produitAmodifier= $pr->find($id);
        $formProduit = $this->createForm(ProduitType::class, $produitAmodifier);
        $formProduit->handleRequest($rq);
        if ($formProduit->isSubmitted() && $formProduit->isValid()){
             
            $imageTelechargee = $formProduit->get("image")->getData();
          
            if($imageTelechargee){// si une image a été téléchargée...
               
                $nouveauNom  = pathinfo($imageTelechargee->getClientOriginalName(), PATHINFO_FILENAME); // récupérer nom fichier telecharge
               
               // je remplace espace par "_"
                $nouveauNom = str_replace(" ", "_", $nouveauNom);// remplacer espace par _
               // je rajpute un string uniue pour eviter d'avoir des doublons et je rajoute l'extention du fichier
                $nouveauNom .= "_".uniqid() . ".". $imageTelechargee->guessExtension();// renome la photo aec nom image /id/extension
               
               //j'enregistre l'image telecharge sur mo, server ds le dosseir public/img
                $imageTelechargee->move($this->getparameter("dossier_images"), $nouveauNom);
             
                
            
                $newImage = new Image;
                $newImage->setImage01($nouveauNom);
                $newImage->setProduit($produitAmodifier);
                $em->persist($newImage);
                $em->flush();
 
            }
            $newImage= $pr->find($id);
            //  $em->persist($newImage);
            $em->flush();
              
                
            //$this->addFlash("success", "le produit n° " . $id ." a été modifié" ) ;


            return $this->redirectToRoute("produit");
        }
        return $this->render("produit/form.html.twig", [
            "form" =>$formProduit->createView(),
            "bouton" => "Modifier",
            "titre" => "Modification du produit n° $id ",
            "produit" => $produitAmodifier

        ]);
    
        
    }  

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/produit/supprimer/{id}", name="produit_supprimer",  requirements={"id"="\d+"})
     */
    public function supprimer(Request $rq, EntityManager $em, ProduitRepo $pr, $id){
      
        $produitAsupprimer= $pr->find($id);
        $formProduit = $this->createForm(ProduitType::class, $produitAsupprimer);
        $formProduit->handleRequest($rq);
        if ($formProduit->isSubmitted() && $formProduit->isValid()){
            $em->remove($produitAsupprimer);
            $em->flush();
            $this->addFlash("danger", "l'album a été supprimé") ;
            return $this->redirectToRoute("produit");
        }
     
        return $this->render("produit/form.html.twig", [
            "form"=>$formProduit->createView(), 
            "bouton" => "Confirmer",
            "titre" => "Suppression du produit n°$id"
        ]);
    }


}
