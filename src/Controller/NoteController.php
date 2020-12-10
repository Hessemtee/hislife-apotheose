<?php

namespace App\Controller;

use App\Entity\Note;
use App\Form\UploadEditType;
use App\Form\UploadType;
use App\Repository\NoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class NoteController extends AbstractController
{

    /** 
     * @Route("/mots-des-profs", name="note_browse")
     */
    public function browse ()
    {        
        $families = $this->getUser()->getFamilies();
        $notesArray = array();
        
        foreach ($families as $family) {
            $children = $family->getChildren()->getValues();

            if (empty($children)) {
                $this->addFlash('danger', 'Vous n\'avez pas ajouté d\'enfant à la famille '.$family.'. Veuillez le faire dans votre profil, section Profil Enfant, avant d\'ajouter un document');
            }
            
            foreach ($children as $child) {
                $notes = $child->getNotes()->getValues();

                foreach($notes as $notes) {
                    array_push($notesArray, $notes);
                }
            }
        }

        return $this->render('note/browse.html.twig', [
            'notes' => $notesArray,
        ]);
    }

    /** 
     * @Route("/mots-des-profs/details/{id}", name="note_read", requirements= {"id": "\d+"})
     */
    public function read (note $note)
    {
        $this->denyAccessUnlessGranted('read', $note);
        
        return $this->render('note/read.html.twig', [
            'note' => $note,
        ]);
    }

    /**
     * @Route("/mots-des-profs/{id}", name="note_edit", requirements= {"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Note $note, Request $request){

        $this->denyAccessUnlessGranted('edit', $note);

        $form =$this->createForm(UploadEditType::class, $note);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

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
                
                $directory = 'assets/files/notes/';
                
                $finalDirectory = $directory.$fileName.'.jpg';
                $note->setFile($finalDirectory);
                
                $newFile->move($this->getParameter('notes_directory'), $fileName.'.jpg');

                
            }

            $em = $this->getDoctrine()->getManager();

            $em->persist($note);

            $em->flush();

            return $this->redirectToRoute('note_browse');
        }

        return $this->render('note/edit.html.twig', [
            'form' => $form->createView(),
            'note' => $note,
        ]);
    }

    /**
     * @Route("/mots-des-profs/ajouter", name="note_add")
     */
    public function add(Request $request, EntityManagerInterface $em, NotifierInterface $notifier)
    {
        $note = new Note;

        $form = $this->createForm(UploadType::class, $note);

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

            $note->setName($name);

            $directory = 'assets/files/notes/';

            $finalDirectory = $directory.$fileName.'.jpg';
            $note->setFile($finalDirectory);

            $em->persist($note);
            
            
            $file->move($directory, $fileName.'.jpg');

            $em->flush();

            $notification = (new Notification('Nouveau mot de professeur ajouté sur le site His Life', ['email']))
            ->content('Un nouveau mot de professeur a été ajouté concernant '.$note->getChild()->getFirstname().' : ' .$note->getName());
            
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
            $this->addFlash('success', 'Message des professeurs ajouté');
            return $this->redirectToRoute('note_browse');
        }


        return $this->render('note/add.html.twig', [
            'controller_name' => 'noteController',
            'form' => $form->createView(),
        ]);
    }

    /** 
     * @Route("/mots-des-profs/{id}", name="note_delete", requirements= {"id": "\d+"}, methods={"DELETE"})
     */
    public function delete (Request $request, Note $note)
    {

        $this->denyAccessUnlessGranted('delete', $note);

        if ($this->isCsrfTokenValid('delete'.$note->getId(), $request->request->get('_token'))) {
            
            $em = $this->getDoctrine()->getManager();
            $em->remove($note);
            $em->flush();

            $this->addFlash('danger', 'Message du professeur supprimé');
        }
        return $this->redirectToRoute('note_browse');
    }

}
