<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Categorie;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', null, [ "label" => "titre"])
            ->add('description')
            ->add('prix')
            ->add('reference')
            ->add('volume')
            ->add('stock')
            ->add('certificat')
            ->add("categorie", EntityType::class, [ 
                
                "class"=> Categorie::class,
                "choice_label"=> "titre",
                "label"=> "Categorie"
                
                ])
            ->add('image', FileType::class, [


                "mapped" => false, 
                "required" => false,
                "constraints" => [
                    new File([ 
                        
                        "mimeTypes"=> [ "image/gif", "image/jpeg", "image/png", ],
                        "mimeTypesMessage"=> "les formats de fichier autorisés sont gif, jpeg, png",
                        "maxSize"=> "5048K",
                        "maxSizeMessage"=> "Le fichier ne doit pas faire plus de 3 Mo"
                    ])
                ]
            ])
            
            ->add('enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
