<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\UserType;


class UserController extends AbstractController
{
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/user", name="user")
     */
    public function index(UserRepository $ur)
    {
        $users=$ur->findAll();
        return $this->render('user/index.html.twig',compact("users"));
    }


    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/user/modifier/{id}", name="user_modifier", requirements={"id"="\d+"})
     */
    public function modifier(UserRepository $ur, EntityManagerInterface $em, Request $rq,UserPasswordEncoderInterface $up, int $id)
    {
        $user = $ur->find($id);
        $formUser= $this->createForm(UserType::class, $user);
        // recupérer info du formulaire html 
        $formUser->handleRequest($rq);   
        if($formUser->isSubmitted() && $formUser->isValid()){
            $mdp = $formUser->get("Password")->getData();
            if($mdp){
            $mdp  = $up->encodePassword($user, $formUser->get('Password')->getData());
            $user->setPassword($mdp);
        }
                $em->persist($user); //cree requete update
                $em->flush();
                $this->addFlash("success", "l'utilisateur n° ". $user->getId() . " a été modifé");
                return $this->redirectToRoute("mon_compte");
        }
        return $this->render('user/form.html.twig', [
            'formUser' => $formUser->createView(),
            "bouton" => "Enregistrer",
            "titre" => "Modification du compte ",
        ]);
    }

    






    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/user/roles/{id}", name="user_role", requirements={"id"="\d+"})
     */
    public function roles($id, UserRepository $ur)
    {
        $user = $ur->find($id);
        return $this->render('user/roleform.html.twig', [
            'user' => $user,
        ]);
    }
    // ADMIN UNIQUEMENT
    /**
     * @Route("/admin/user/roles/verification/{id}", name="user_roles_verif", requirements={"id"="\d+"})
     */
    public function rolesVerif($id, UserRepository $ur, Request $rq, EntityManagerInterface $em)
    {
        $user = $ur->find($id);
        
        $roles = [];
        foreach ($rq->request as $name => $value) {
            array_push($roles, $value);
        }
        $user->setRoles($roles);
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute("user");
    }







    // /**
    //  * @Route("/user/supprimer/{id}", name="user_supprimer", requirements={"id"="\d+"})
    //  */
    // public function supprimer(UserRepository $ur, EntityManagerInterface $em, Request $rq,UserPasswordEncoderInterface $up, int $id)
    // {
    //     $userAsupprimer = $ur->find($id);
    //     $formUser= $this->createForm(UserType::class, $userAsupprimer);
    //     // recupérer info du formulaire html 
    //     $formUser->handleRequest($rq);   
    //     if($formUser->isSubmitted() && $formUser->isValid()){
    //         $mdp = $formUser->get("Password")->getData();
    //         if($mdp){
    //         $mdp  = $up->encodePassword($userAsupprimer, $formUser->get('Password')->getData());
    //         $userAsupprimer->setPassword($mdp);
    //     }
    //         $em->remove($userAsupprimer); 
    //         $em->flush();
    //         $this->addFlash("success", "l'utilisateur n° ". $userAsupprimer->getId() . " a été modifé");
    //         return $this->redirectToRoute("home");
    //     }
    //     return $this->render('user/form.html.twig', [
    //         'formUser' => $formUser->createView(),
    //         "bouton" => "Supprimer",
    //         "titre" => "Suppression du compte ",
    //     ]);
    // }


    






    // USER ONLY
    /**
     * @Route("/profile/moncompte", name="mon_compte")
     */
    public function monCompte(UserRepository $ur)
    {
        return $this->render('user/moncompte.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/profile/moncompte/modifier", name="moncompte_modifier")
     */
    public function modifierMonCompte(UserRepository $ur, EntityManagerInterface $em, Request $rq,UserPasswordEncoderInterface $up)
    {
        $user = $this->getUser();
        $formUser= $this->createForm(UserType::class, $user);
        // recupérer info du formulaire html 
        $formUser->handleRequest($rq);   
        if($formUser->isSubmitted() && $formUser->isValid()){
            $mdp = $formUser->get("Password")->getData();
            if($mdp){
            $mdp  = $up->encodePassword($user, $formUser->get('Password')->getData());
            $user->setPassword($mdp);
        }
                $em->persist($user); //cree requete update
                $em->flush();
                $this->addFlash("success", "l'utilisateur n° ". $user->getId() . " a été modifé");
                return $this->redirectToRoute("mon_compte");
        }
        return $this->render('user/form.html.twig', [
            'formUser' => $formUser->createView(),
            "bouton" => "Enregistrer",
            "titre" => "Modification du compte ",
        ]);
    }
}
