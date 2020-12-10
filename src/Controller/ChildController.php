<?php

namespace App\Controller;

use App\Entity\Child;
use App\Form\ChildType;
use App\Form\UploadEditType;
use App\Repository\ChildRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChildController extends AbstractController
{

    /**
     * @Route("/enfant/profil", name="child_profile")
     */
    public function profile(ChildRepository $childRepository, FamilyRepository $familyRepository)
    {
        // #############################################
        // Pour l'instant je l'écris en français car ça m'arrange fortement (et puis l'anglais c'est de la merde !!!)
        // Ce bloc de code fait :

        // Ici nous récupérons toutes les Entity Family de l'utilisateur connecté
        $familiesOfUser = $this->getUser()->getFamilies();

        // Ici création de 2 tableaux vides qui nous seront utiles après pour stocker des objets
        $childrenArray = array();

        // Pour toutes les Entity Family on demande les children associés
        // Vu qu'il en ressort une PersistentCollection, 
        // il faut boucler sur cette collection pour obtenir chaque objet Child
        // Ensuite on push dans notre tableau créé auparavant
        foreach($familiesOfUser as $family) {
            $childrenCollection = $family->getChildren();
            // Si l'ont fait un dump de $childrenCollection, nous obtiendrons la PeristentCollection
            // Mais nous ne voyons aucun éléments Child à l'intérieur, ceci est normal
            // Il suffirait de faire $childrenCollection = $family->getChildren()->getValues;
            // getValues() est une méthode des PersistentCollection qui permet d'obtenir tous les éléments
            // Vu qu'ici nous avons plusieurs Family, nous devons refaire une boucle pour obtenir les Childs de chacune

            // Doc utile pour les PersistentCollection :
            // http://apigen.juzna.cz/doc/davidmoravek/nette-doctrine-sandbox/class-Doctrine.ORM.PersistentCollection.html

                foreach ($childrenCollection as $children) {   
                    array_push($childrenArray, $children);
                }
            }
        
        // Ensuite il n'y a plus qu'à passer notre tableau
        // dans le render du controleur pour l'exploiter dans le twig
        // #############################################
        

        return $this->render('child/profile.html.twig', [
            'controller_name' => 'ChildController',
            'children' => $childrenArray
        ]);
    }

    /**
     * @Route("/enfant/ajout", name="child_create")
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
        $child = new Child;

        $form = $this->createForm(ChildType::class, $child);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $picture = $form['picture']->getData();

            function generateRandomString($length = 10)
            {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $maxLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++)
                {
                $randomString .= $characters[rand(0, $maxLength - 1)];
                }
                return $randomString;
            }

            $fileName = generateRandomString();

            $directory = 'assets/files/child_picture/';

            if ($picture != null) {
                $finalDirectory = $directory.$fileName.'.jpg';
                $child->setPicture($finalDirectory);
                $picture->move($this->getParameter('child_picture_directory'), $fileName.'.jpg');
            }

            $family = $form->getData()->getFamilies()[0];
            $family->addChild($child);
           
            $em->persist($child);
            $em->flush();

            $this->addFlash('success', 'Profil enfant créé');
            return $this->redirectToRoute('child_profile');
        }

        return $this->render('child/create.html.twig', [
            'controller_name' => 'ChildController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/enfant/profil/{id}", name="child_read", requirements= {"id": "\d+"})
     */
    public function read(Child $child)
    {
        $this->denyAccessUnlessGranted('read', $child);

        return $this->render('child/read.html.twig', [
            'controller_name' => 'ChildController',
            'child' => $child,
        ]);
    }

    
    /**
     * @Route("/enfant/{id}", name="child_delete", requirements={"id": "\d+"}, methods={"DELETE"})
     */
    public function delete(Request $request, Child $child): Response
    {
        $this->denyAccessUnlessGranted('delete', $child);

        if ($this->isCsrfTokenValid('delete'.$child->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($child);
            $em->flush();

            $this->addFlash('danger', 'Profil enfant supprimé');
        }
        
        return $this->redirectToRoute('child_profile');
    }

    /**
     *  @Route ("/enfant/{id}", name="child_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Child $child, Request $request)
    {

        $this->denyAccessUnlessGranted('edit', $child);

        $form = $this->createForm(ChildType::class, $child);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $newFile = $form['picture']->getData();

            if ($newFile != null){
                function generateRandomString($length = 10)
                {
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $maxLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[rand(0, $maxLength - 1)];
                    }
                    return $randomString;
                }
                
                $fileName = generateRandomString();
                
                $directory = 'assets/files/child_picture/';
                
                $finalDirectory = $directory.$fileName.'.jpg';
                $child->setPicture($finalDirectory);
            
                $newFile->move($this->getParameter('child_picture_directory'), $fileName.'.jpg');
            }

            $em = $this->getDoctrine()->getManager();

            $em->persist($child);

            $em->flush();

            return $this->redirectToRoute('child_profile');
        }

        return $this->render('child/edit.html.twig', [
            'form' => $form->createView(),
            'child' => $child, 
        ]);

    }
}
