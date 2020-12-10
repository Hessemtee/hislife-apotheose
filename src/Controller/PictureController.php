<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Form\PictureEditType;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PictureController extends AbstractController
{
    /**
     * @Route("/galerie", name="picture")
     */
    public function browse(PictureRepository $pictureRepository)
    {
        $families = $this->getUser()->getFamilies();
      
        $picturesArray = array();

        foreach ($families as $family) {
            $pictures = $family->getPictures()->getValues();
            foreach ($pictures as $picture){
                array_push ($picturesArray, $picture);
            }
        }

        return $this->render('picture/browse.html.twig', [
            'controller_name' => 'PictureController',
            'pictures' => $picturesArray
        ]);
    }

    /**
     * @Route("/galerie/details/{id}", name="picture_read", requirements= {"id": "\d+"})
     */
    public function read(Picture $picture)
    {
        $this->denyAccessUnlessGranted('view', $picture);

        return $this->render('picture/read.html.twig', [
            'controller_name' => 'PictureController',
            'picture' => $picture
        ]);
    }

    /**
     * @Route("/galerie/{id}", name="picture_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Picture $picture, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('edit', $picture);

        $form = $this->createForm(PictureEditType::class, $picture);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $newFile = $form['file']->getData();
            
            if ($newFile != null) {
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
                
                $directory = 'assets/files/pictures/';
                
                $finalDirectory = $directory.$fileName.'.jpg';
                $picture->setFile($finalDirectory);
                
                $newFile->move($this->getParameter('pictures_directory'), $fileName.'.jpg');

            }
            $em->persist($picture);
            $em->flush();
            return $this->redirectToRoute('picture');
        }

        return $this->render('picture/edit.html.twig', [
            'form' => $form->createView(),
            'picture' => $picture
        ]);
    }

    /**
     * @Route("/galerie/ajouter", name="picture_add")
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $picture = new Picture;

        $form = $this->createForm(PictureType::class, $picture);

        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $title = $form->getData()->getTitle();
            $description = $form->getData()->getDescription();
            
            $file = $form['file']->getData();

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

            $picture->setTitle($title);

            if ($description != null) {
                $picture->setDescription($description);
            }

            if ($description === null) {
                $picture->setDescription('Pas de description');
            }

            $picture->setPeople($this->getUser());


            $directory = 'assets/files/pictures/';

            $finalDirectory = $directory.$fileName.'.jpg';
            $picture->setFile($finalDirectory);

            $em->persist($picture);
            
            $file->move($this->getParameter('pictures_directory'), $fileName.'.jpg');

            $em->flush();

            $this->addFlash('success', 'Photo ajouté'); 
            return $this->redirectToRoute('picture');
        }

        return $this->render('picture/add.html.twig', [
            'controller_name' => 'PictureController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/galerie/{id}", name="picture_delete", requirements= {"id": "\d+"}, methods={"DELETE"})
     */

    public function delete(Request $request, Picture $picture): Response 
    {
        $this->denyAccessUnlessGranted('delete', $picture);

        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($picture);
            $em->flush();
            
            $this->addFlash('danger', 'Photo supprimée');
        }
        
        return $this->redirectToRoute('picture');
    }
}
