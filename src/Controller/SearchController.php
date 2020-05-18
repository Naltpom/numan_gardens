<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     */
    public function index(Request $rq, ProduitRepository $pr,CategorieRepository $catr,CommentaireRepository $comr)
    {
        $motRecherche = $rq->query->get("mot");
        $motRecherche = trim($motRecherche);

        if ($motRecherche) {

            $produits = $pr->findByName($motRecherche);
            $categories = $catr->findByName($motRecherche);
            $commentaires = $comr->findByName($motRecherche);

            return  $this->render('search/index.html.twig', compact("produits", "categories", "commentaires"));
        }else{
            return $this->redirectToRoute("home");
        }

    }
}
