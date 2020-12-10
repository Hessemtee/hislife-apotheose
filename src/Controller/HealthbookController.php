<?php

namespace App\Controller;

use App\Entity\Healthbook;
use App\Form\UploadType;
use App\Form\UploadEditType;
use App\Repository\HealthbookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class HealthbookController extends AbstractController
{
    /** 
     * @Route("/sante", name="healthbook_browse")
     */
    public function browse()
    {
        $families = $this->getUser()->getFamilies();
        $healthbooksArray = array();

        foreach ($families as $family) {
            $children = $family->getChildren()->getValues();

            if (empty($children)) {
                $this->addFlash('danger', 'Vous n\'avez pas ajouté d\'enfant à la famille '.$family.'. Veuillez le faire dans votre profil, section Profil Enfant, avant d\'ajouter un document');
            }

            foreach ($children as $child) {
                $healthbooks = $child->getHealthbooks()->getValues();

                foreach($healthbooks as $healthbook) {
                    array_push($healthbooksArray, $healthbook);
                }
            }
        }
        return $this->render('healthbook/browse.html.twig', [
            'healthbooks' => $healthbooksArray,
        ]);
    }

    /** 
     * @Route("/sante/details/{id}", name="healthbook_read", requirements= {"id": "\d+"})
     */
    public function read(Healthbook $healthbook)
    {
        $this->denyAccessUnlessGranted('read', $healthbook);
        return $this->render('healthbook/read.html.twig', [
            'healthbook' => $healthbook,
        ]);
    }

    /**
     * @Route("/sante/ajouter", name="healthbook_add")
     */
    public function add(Request $request, EntityManagerInterface $em, NotifierInterface $notifier)
    {
        $healthbook = new Healthbook;

        $form = $this->createForm(UploadType::class, $healthbook);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $name = $form->getData()->getName();
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

            $healthbook->setName($name);

            $directory = 'assets/files/healthbooks/';

            $finalDirectory = $directory.$fileName.'.jpg';
            $healthbook->setFile($finalDirectory);

            $em->persist($healthbook);
            
            
            $file->move($directory, $fileName.'.jpg');

            $em->flush();

            $notification = (new Notification('Nouveau bulletin de note ajouté ajouté sur le site His Life', ['email']))
            ->content('Un nouveau bulletin de note a été ajouté concernant '.$healthbook->getChild()->getFirstname().' : ' .$healthbook->getName());
            
            $family = $form->getData()->getChild()->getFamilies()->first();

            $people = $family->getPeople()->getValues();

            foreach ($people as $person) {
                if ($person != $this->getUser()) {
                    $user = $person->getEmail();
                    
                    $recipient = new Recipient(
                        $user,
                    );
                    $notifier->send($notification, $recipient);
                }    
            }
            $this->addFlash('success', 'Carnet de Santé ajouté');
            return $this->redirectToRoute('healthbook_browse');
        }

        return $this->render('healthbook/add.html.twig', [
            'controller_name' => 'HealthbookController',
            'form' => $form->createView(),
        
        ]);
    }

    /** 
     * @Route("/sante/{id}", name="healthbook_delete", requirements= {"id": "\d+"}, methods={"DELETE"})
     */
    public function delete (Request $request, Healthbook $healthbook, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('delete', $healthbook);
        if ($this->isCsrfTokenValid('delete'.$healthbook->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($healthbook);
            $em->flush();
        }
        $this->addFlash('danger', 'Carnet de Santé supprimé');

        return $this->redirectToRoute('healthbook_browse');
    }

    /**
     * @Route("/sante/{id}", name="healthbook_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Healthbook $healthbook, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('edit', $healthbook);
        
        $form = $this->createForm(UploadEditType::class, $healthbook);

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
                
                $directory = 'assets/files/healthbooks/';
                
                $finalDirectory = $directory.$fileName.'.jpg';
                $healthbook->setFile($finalDirectory);
                
                $newFile->move($this->getParameter('healthbooks_directory'), $fileName.'.jpg');

            }
            
            $em->persist($healthbook);
            $em->flush();
            return $this->redirectToRoute('healthbook_browse');
        }
         
        
            
        return $this->render('healthbook/edit.html.twig', [
            'form' => $form->createView(),
            'healthbook' => $healthbook,
        ]);


    }
}
