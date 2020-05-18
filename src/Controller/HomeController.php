<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use stdClass;
use App\Repository\ProduitRepository;

class HomeController extends AbstractController
{
    /**
     * @Route("/qui_somme_nous", name="nous")
     */
    public function nous(ProduitRepository $pr){    
        return $this->render("home/nous.html.twig");   
    }
    /**
     * @Route("/cgv", name="cgv")
     */
    public function cgv(ProduitRepository $pr){    
        return $this->render("home/cgv.html.twig");   
    }
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(ProduitRepository $pr){    
        return $this->render("home/contact.html.twig");   
    }
    /**
     * @Route("/", name="home")
     */
    public function produit(ProduitRepository $pr){
        // On veut pouvori afficher tout les produit sur la page principale
        $produits = $pr->findAll();       
        return $this->render("home/index.html.twig",[ "produit" => $produits ]);   
    }
    
    /**
     * @Route("/catalogue", name="home_catalogue")
     */
    public function catalogue(){

        //afficher le catalogue 
        return $this->render("home/catalogue.html.twig");
    }
    /**
     * @Route("/promotion", name="home_promotion")
     */
    public function promotion(){

        //afficher le catalogue 
        return $this->render("home/promotion.html.twig");
    }
    
}
