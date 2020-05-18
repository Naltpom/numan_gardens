<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Etat;
use App\Form\EtatType;

class EtatController extends AbstractController
{
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/etat", name="etat")
     */
    public function index(EtatRepository $er)
    {
        $etats = $er->findAll();
        // on veut afficher tout les etats
        return $this->render('etat/index.html.twig', [
            'etats' => $etats,
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/etat/new", name="etat_ajouter")
     */
    public function new()
    {
        return $this->render('etat/form.html.twig', [
            'h1' => "Ajouter un Etat",
            'action' => "ajouter",
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/etat/modifier/{id}", name="etat_modifier",  requirements={"id"="\d+"})
     */
    public function modifier(EtatRepository $er, $id)
    {
        // On recupérer l'etat dans la bdd
        $etat = $er->find($id);
        return $this->render('etat/form.html.twig', [
            'h1' => "Modifier un Etat : ". $etat->getId(),
            'action' => "modifier",
            'etat' => $etat,
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/etat/supprimer/{id}", name="etat_supprimer",  requirements={"id"="\d+"})
     */
    public function supprimer(EtatRepository $er, $id)
    {
        // On recupérer l'etat dans la bdd
        $etat = $er->find($id);
        return $this->render('etat/form.html.twig', [
            'h1' => "Supprimer etat : ". $etat->getTitre(),
            'action' => "supprimer",
            'etat' => $etat,
        ]);
    }


    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/etat/bdd", name="etat_validation")
     */
    public function modbdd(Request $rq, EntityManager $em,EtatRepository $er)
    {
        // Cette fonction permet la verification lors de la modification, d'un ajout, ou d'une suppretion d'etat dans la bdd
        if($rq->request->get('action') == "ajouter")
        {        
            // on crée un nouvelle objet etat
            $etat = new Etat;

            $titre = $rq->request->get('titre');
            $description = $rq->request->get('description');
            #etape de verification
            // Si le titre et la description sont bien reseigner alors
            if ($rq->request->get('titre') !== "" && $rq->request->get('description') !== "" ) 
            {
                // on y redefinit le titre
                $etat->setTitre($titre);
                // on y redefinit la description
                $etat->setDescription($description);
                // on envoi a la bdd
                $em->persist($etat);
            } 
            else //sinon on redirige vers l'ajout d'etat
            {
                return $this->redirectToRoute("etat_ajouter");
            }
            

        }
        elseif($rq->request->get('action') == "modifier"){
            
            if($er->find($rq->request->get('id')))// par securité on verifie qu"on trouve bien dans la bdd etat a modifier
            {
                $etat = $er->find($rq->request->get('id'));
                $titre = $rq->request->get('titre');
                $description = $rq->request->get('description');
                #etape de verification
                // Si le titre et la description sont bien reseigner alors
                if ($rq->request->get('titre') !== "" && $rq->request->get('description') !== "" ) 
                {
                    // on y redefinit le titre
                    $etat->setTitre($titre);
                    // on y redefinit la description
                    $etat->setDescription($description);
                    // on envoi a la bdd
                    $em->persist($etat);
                } 
                else //sinon on redirige vers l'modif d'etat
                {
                    return $this->redirectToRoute("etat_modifier", ["id"=>$rq->request->get('id')]);
                }
            }
            else//si il n'existe pas on redirige vers tout les etats
            {
                return $this->redirectToRoute("etat");
            }
        }
        elseif($rq->request->get('action') == "supprimer")//si on recupére l'action de supprimer alors on supprime l'etat
        {
            $etat = $er->find($rq->request->get('id'));
            $em->remove($etat);
            $em->flush();
        }
        
        $em->flush();
        return $this->redirectToRoute("etat");
    }

}




