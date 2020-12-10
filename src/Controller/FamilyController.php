<?php

namespace App\Controller;

use App\Entity\Family;
use App\Form\FamilyType;
use App\Repository\FamilyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;

class FamilyController extends AbstractController
{

    /**
     * @Route("/famille/profil", name="family_profile")
     */
    public function profile(FamilyRepository $childRepository, FamilyRepository $familyRepository)
    {
        return $this->render('family/profile.html.twig', [
            'controller_name' => 'FamilyController',
            'families' => $this->getUser()->getFamilies()->getValues()
        ]);
    }
    
    /**
     * @Route("/famille/ajouter", name="family_create")
     */
    public function create(Request $request, EntityManagerInterface $em)
    {
    
        $family = new Family;

        $form = $this->createForm(FamilyType::class, $family);

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

            $directory = 'assets/files/family_picture/';

            if ($picture != null) {
                $finalDirectory = $directory.$fileName.'.jpg';
                $family->setPicture($finalDirectory);
                $picture->move($this->getParameter('family_picture_directory'), $fileName.'.jpg');
            }

            $family->setName($form->getData()->getName());
            $family->addPerson($this->getUser());

            $token = bin2hex(random_bytes(32));
            $family->setToken($token);

            $link = 'http://ec2-34-227-161-244.compute-1.amazonaws.com/inscription?token='.$token;
            
            $em->persist($family);
            $em->flush();
            
            $this->addFlash('success', 'Vous avez bien créé votre famille. Vous pouvez inviter un autre utilisateur en transmettant ce lien '.$link);
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('family/create.html.twig', [
            'controller_name' => 'FamilyController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/famille/profil/{id}", name="family_read", requirements= {"id": "\d+"})
     */
    public function read(Family $family)
    {

        $this->denyAccessUnlessGranted('view', $family);

        return $this->render('family/read.html.twig', [
            'controller_name' => 'FamilyController',
            'family' => $family
        ]);
    }

   /**
     * @Route("/famille/{id}", name="family_delete", requirements={"id": "\d+"}, methods={"DELETE"})
     */
    public function delete(Request $request, Family $family): Response
    {
        $this->denyAccessUnlessGranted('delete', $family);

        if ($this->isCsrfTokenValid('delete'.$family->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($family);
            $em->flush();

            $this->addFlash('danger', 'Famille supprimé');
        }
        
        return $this->redirectToRoute('family_profile');
    }

    /**
     *  @Route ("/famille/{id}", name="family_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Family $family, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $family);

        $form = $this->createForm(FamilyType::class, $family);

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
                
                $directory = 'assets/files/family_picture/';
                
                $finalDirectory = $directory.$fileName.'.jpg';
                $family->setPicture($finalDirectory);
            
                $newFile->move($this->getParameter('family_picture_directory'), $fileName.'.jpg');
            }

            $em = $this->getDoctrine()->getManager();

            $em->persist($family);

            $em->flush();

            return $this->redirectToRoute('family_profile');
        }

        return $this->render('family/edit.html.twig', [
            'form' => $form->createView(),
            'family' => $family, 
        ]);

    }
}
