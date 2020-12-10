<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

class PostController extends AbstractController
{
    /**
     * @Route("/messages", name="post_browse")
     */
    public function browse(Request $request, PostRepository $postRepository)
    {
        $families = $this->getUser()->getFamilies();
        $postsArray = array();

        foreach ($families as $family) {
            $posts = $family->getPosts()->getValues();
            foreach ($posts as $post) {
                array_push ($postsArray, $post);
            }
        }
        
        return $this->render('post/browse.html.twig', [
            'posts' => $postsArray,
        ]);
    }

    /**
     * @Route ("/messages/voir/{id}", name="post_read", requirements={"id": "\d+"})
     */
    public function read(Post $post, Request $request)
    {

        $this->denyAccessUnlessGranted('read', $post);

        return $this->render('post/read.html.twig', [
            'controller_name' => 'PostController',
            'post' => $post
        ]);
    }

    /**
     * @Route("/message/ajouter", name="post_add")
     */
    public function add(Request $request, NotifierInterface $notifier)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $post->setPeople($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $notification = (new Notification ('Vous avez un nouveau message sur le site HisLife ', ['email']))
                    ->content('Vous avez reçu un nouveau message ! ');

            $family = $this->getUser()->getFamilies()->first();

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
            $this->addFlash('success', 'Votre message a bien été posté');
            return $this->redirectToRoute('post_browse');
        }

        return $this->render('post/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route ("/message/{id}", name="post_edit", requirements={"id": "\d+"}, methods={"GET", "POST"})
     */
    public function edit(Post $post, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $post);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('post_browse');
        }

        



        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post, 
        ]);
    }

    /**
     * @Route ("/message/{id}", name="post_delete", requirements={"id": "\d+"}, methods={"DELETE"})
     */
    public function delete(Post $post, Request $request): Response
    {

        $this->denyAccessUnlessGranted('delete', $post);

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();

            $this->addFlash('danger', 'Message supprimé');
        }
        
        return $this->redirectToRoute('post_browse');
    }

}
