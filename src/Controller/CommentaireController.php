<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Repository\CommentaireRepository;
use App\Repository\EtatRepository;
use App\Repository\UserRepository;
use App\Entity\Commentaire;
use App\Form\NotationType;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Symfony\Component\HttpFoundation\Request; 
use App\Entity\Etat;

class CommentaireController extends AbstractController
{
    // On veut on veut pourvoir pour un administrateur de modifier l'etat du commentaire uniquement, le passer de 'en verif' à 'valider' ou a 'rejeter' 
    // si le commentaire est rejeter alors il ne sera pas affiché sur le site public
    // cette fonctionnalité est directement possible pour les admin sur les commentaire directement

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commentaire", name="commentaire")
     */
    public function index(CommentaireRepository $cr)
    {
        $commentaires = $cr->findAll();
        // on veut afficher tout les commentaires
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commentaire/modifier/{id}", name="commentaire_modifier",  requirements={"id"="\d+"})
     */
    public function modifier($id, CommentaireRepository $cr,EtatRepository $er)
    {
        $commentaire = $cr->find($id);
        $etats = $er->findAll();

        // on envoi sur le formulaire pour modifier l'etat du commentaire selectionné
        return $this->render('commentaire/modifier.html.twig', [
            'commentaire' => $commentaire,
            'etats' => $etats,
        ]);
    }

    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commentaire/etatModifier/{id}", name="commentaire_etatModifier",  requirements={"id"="\d+"})
     */
    public function etatModifier($id, CommentaireRepository $cr, EntityManager $em,EtatRepository $er, Request $request)
    {
        // on recupére le commentaire
        $commentaire = $cr->find($id);
        // on récupére l'etat du commentaire lors de la validation du formulaire et on le redefini dans l'objet
        $commentaire->setEtat($er->find($request->request->get('etatId')));
        // on l'enregistre dans la bdd
        $em->persist($commentaire);
        $em->flush();

        if(null !== $request->request->get('idProduit'))
            // on veut soit retourné a la page du produit d'ou on a modifier l'etat d'un comentaire
            return $this->redirectToRoute("produit_fiche", ["id" => $request->request->get('idProduit')]);
        else
            // soit retourné a la liste de tout les commentaire
            return $this->redirectToRoute("commentaire");
    }
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/commentaire/verif/{id}", name="commentaire_ajouter" , requirements={"id"="\d+"})
     */
    public function ajouter($id,EntityManager $em, Request $request, ProduitRepository $pr, EtatRepository $er)
    {
        // on a besoin de :
        // objet utilisateur, objet produit, note, commentaire, date, etat

        // on recupére le user connecter
        $user = $this->getUser(); 
        // on recupére le produit
        $produit = $pr->find($id);
        // on recupére la note du formulaire sur la fiche du produit
        $note = $request->request->get('note');
        // on recupérer le commentaire du formulaire sur la fiche du produit
        $commentaire = $request->request->get('commentaire');
        // on recupére la date
        $date = new \DateTime('now');
        // on definit l'etat par defaut en cour de validation
        $etat = $er->find(1);

        // Etape de verification
        if (!empty($commentaire) && $note > 0 && $note <= 5) {
            // on cree le commentaire
            $newCommentaire = new Commentaire;
            $newCommentaire->setNote($note);
            $newCommentaire->setCommentaire($commentaire);
            $newCommentaire->setDateEnregistrement($date);
            $newCommentaire->setUser($user);
            $newCommentaire->setProduit($produit);
            $newCommentaire->setEtat($etat);

            // on envoi a la bdd
            $em->persist($newCommentaire);
            $em->flush();
            return $this->redirectToRoute("produit_fiche", ["id" => $id]);
        }

    }
}
