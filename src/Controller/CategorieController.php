<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository as CategorieRepo;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request; 
use Doctrine\ORM\EntityManagerInterface as EntityManager;



class CategorieController extends AbstractController
{
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/categorie", name="categorie")
     */
    public function index(CategorieRepo $cr)
    {
        // On veut afficher toutes les categories existante
        return $this->render('categorie/index.html.twig', [
            'liste_categories'=> $cr->findAll()
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/categorie/new", name="categorie_new")
     */

    public function new(Request $rq, EntityManager $em)
    {
        // On veut pouvoir ajouter une categorie a la bdd

        // on crée l'objet
        $nouvelleCategorie = new Categorie;
        // on crée le formulaire
        $formCategorie = $this->createForm(CategorieType::class, $nouvelleCategorie);
        $formCategorie->handleRequest($rq);

        if($formCategorie->isSubmitted() && $formCategorie->isValid()){
            // Si le formulaire est valdé et valide
            $em->persist($nouvelleCategorie);
            $em->flush();
            return $this->redirectToRoute("categorie");
            //$this->addFlash("success", "la catégorie a été ajouté" ) ;
        }

        // On affiche par default le formulaire d'ajout de categorie
        return $this->render('categorie/form.html.twig', [
            'form' => $formCategorie->createView(),  "bouton" => "Enregistrer" 
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/categorie/fiche/{id}", name="categorie_fiche", requirements={"id"="\d+"})
     */
    public function fiche(CategorieRepo $cr, $id)
    {
        // On veut ici affiché la categorie 

        // on selectionne la categorie
        $categorie = $cr->find($id);
        // on envoi la categorie sur le rendu uniquement si elle existe
        if (!empty($categorie)){
            return $this->render("categorie/fiche.html.twig", [ "categorie" => $categorie ]);
        }
        // sinon on redirige vers la liste des categories
        return $this->redirectToRoute("categorie");
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/categorie/modifier/{id}", name="categorie_modifier",  requirements={"id"="\d+"})
     */
    public function modifier(Request $rq,EntityManager $em, CategorieRepo $cr, $id)
    {
        // On veut pouvoir modifier une categorie
        // On cherche la categorie a modifier dans la bdd
        $categorieAmodifier= $cr->find($id);
        // on crée le formulaire
        $formCategorie = $this->createForm(CategorieType::class, $categorieAmodifier);
        $formCategorie->handleRequest($rq);
        if ($formCategorie->isSubmitted() && $formCategorie->isValid()){
            // si le formulaire est validé et est valide on modifie dans la bdd et on redirige vers la liste des categorie
            $em->persist($categorieAmodifier);
            $em->flush();
            return $this->redirectToRoute("categorie");
        }
        // par default on affiche le formulaire rempli des anciennes valeurs
        return $this->render("categorie/form.html.twig", [
            "form"=>$formCategorie->createView(),
            "bouton" => "Modifier",
            "titre" => "Modification de la catégorie n°$id"
        ]);   
    }  

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/categorie/supprimer/{id}", name="categorie_supprimer",  requirements={"id"="\d+"})
     */
    public function supprimer(Request $rq,EntityManager $em, CategorieRepo $cr, $id){
        // On veut pouvoir supprimer une categorie
        // par securité on va demander confirmation avant la suppression
        // on va donc redirigé vers un formulaire conprenant les information de la categorie selectionné
        $categorieAsupprimer= $cr->find($id);
        $formCategorie = $this->createForm(CategorieType::class, $categorieAsupprimer);
        $formCategorie->handleRequest($rq);
        if ($formCategorie->isSubmitted() && $formCategorie->isValid()){
            // Si on valide le formulaire alors on supprimer la categorie et on redirige vers la liste de categories
            $em->remove($categorieAsupprimer);
            $em->flush();
            
            return $this->redirectToRoute("categorie");
        }
        // par default on affiche les info de la categorie selectionné sous forme de formulaire
        return $this->render("categorie/form.html.twig", [
            "form"=>$formCategorie->createView(), 
            "bouton" => "Confirmer la suppression",
            "titre" => "Suppression de la categorie n°$id"
        ]);
    }


    ####################################################################################
    ####################################################################################
    ####################################################################################
    // dessous on crée une route pour chaque categorie que l'on veut afficher separement 
    // ALL ACCESS | tout le monde peut le voir


    /**
     * @Route("/categorie/massage", name="massage")
     */
    public function bio(CategorieRepo $cr, ProduitRepository $pr)
    {
        // On veut pouvoir afficher que les produit de la categorie massage
        $cat = $cr->findCat(1); //correspond a la categorie massage
        // On envoi la liste en arrays de tout les produit appartenant a la categorie
        return $this->render('categorie/produits.html.twig', [
            'produits' => $pr->findAllProdCat($cat),
            'h1' => "Gamme massage"
        ]);
    }  
    
    /**
     * @Route("/categorie/beaute", name="beaute")
     */
    public function beaute(CategorieRepo $cr, ProduitRepository $pr)
    {
        // On veut pouvoir afficher que les produit de la categorie Beauté
        $cat = $cr->findCat(2); //correspond a la categorie Beauté
        // On envoi la liste en arrays de tout les produit appartenant a la categorie
        return $this->render('categorie/produits.html.twig', [
            'produits' => $pr->findAllProdCat($cat),
            'h1' => "Gamme beauté"
        ]);
    }
      
    /**
     * @Route("/categorie/hammam", name="hammam")
     */
    public function hammam(CategorieRepo $cr, ProduitRepository $pr)
    {
        // On veut pouvoir afficher que les produit de la categorie hammam
        $cat = $cr->findCat(3); //correspond a la categorie hammam
        // On envoi la liste en arrays de tout les produit appartenant a la categorie
        return $this->render('categorie/produits.html.twig', [
            'produits' => $pr->findAllProdCat($cat),
            'h1' => "Gamme Hammam"
        ]);
    }
      
    /**
     * @Route("/categorie/argan_Bio", name="arganBio")
     */
    public function arganBio(CategorieRepo $cr, ProduitRepository $pr)
    {
        // On veut pouvoir afficher que les produit de la categorie argan_Bio
        $cat = $cr->findCat(4); //correspond a la categorie argan_Bio
        // On envoi la liste en arrays de tout les produit appartenant a la categorie
        return $this->render('categorie/produits.html.twig', [
            'produits' => $pr->findAllProdCat($cat),
            'h1' => "Gamme d'argan bio"
        ]);
    }
      
    /**
     * @Route("/categorie/huils_Vegetal", name="huilsVegetal")
     */
    public function huilsVegetal(CategorieRepo $cr, ProduitRepository $pr)
    {
        // On veut pouvoir afficher que les produit de la categorie huils_Vegetal
        $cat = $cr->findCat(5); //correspond a la categorie huils_Vegetal
        // On envoi la liste en arrays de tout les produit appartenant a la categorie
        return $this->render('categorie/produits.html.twig', [
            'produits' => $pr->findAllProdCat($cat),
            'h1' => "Gammes huils végétales"
        ]);
    }
  
    /**
     * @Route("/categorie/selection", name="categorie_selection")
     */
    public function selection(CategorieRepo $cr, ProduitRepository $pr, Request $request)
    {
        // On crée un objet pour contenir les produit
        $categories = [];

        // pour chaque reponse du formulaire de selection on va ajouter dans l'array precedent le nom de la categorie comme key et un array de tout les produit trouver appartenant a la categorie
        foreach ($request->request as $key => $value) {
            if ($request->request->get($key) == "massage") {
                array_push($categories,array($value => $pr->findAllProdCat($cr->findCat($key))));
            }elseif ($request->request->get($key) == "beaute") {
                array_push($categories,array($value => $pr->findAllProdCat($cr->findCat($key))));
            }elseif ($request->request->get($key) == "hammam") {
                array_push($categories,array($value => $pr->findAllProdCat($cr->findCat($key))));
            }elseif ($request->request->get($key) == "arganBio") {
                array_push($categories,array($value => $pr->findAllProdCat($cr->findCat($key))));
            }elseif ($request->request->get($key) == "huilsVegetal") {
                array_push($categories,array($value => $pr->findAllProdCat($cr->findCat($key))));
            }
        }
      
        // on renvoi la selection sur la page
        return $this->render('home/selection.html.twig', [
            'categories' => $categories,
        ]);
    }
}
