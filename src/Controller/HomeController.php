<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use stdClass;
use App\Repository\ProduitRepository;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(ProduitRepository $pr){    
        return $this->render("home/home.html.twig");   
    }

    /**
     * @Route("/produits", name="home_produits")
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
}
