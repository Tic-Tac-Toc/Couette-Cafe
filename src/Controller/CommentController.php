<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Room;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/", name="comment_index", methods={"GET"})
     */
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="comment_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        // accès à notre entité client
        $clientConnected = $user->getClient();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            // seul l'admin peut choisir la chambre pour sa réservation
            if (!$request->get('id')) 
                throw new \Exception("Vous ne pouvez effectuer cette action.");

            $room = $this->getDoctrine()->getManager()->getRepository(Room::class)->find($request->get('id'));
            if (!$clientConnected) {
                throw new \Exception("Vous n'êtes pas client, vous ne pouvez mettre des commentaires.");
            }
            else if (!$clientConnected->hasBooked($room)) {
                throw new \Exception("Vous n'avez jamais séjourné ici.");
            }            
        }

        $comment = new Comment();
        $comment->setClient($clientConnected);
        if (isset($room))
            $comment->setRoom($room);
        $form = $this->createForm(CommentType::class, $comment, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le commentaire a bien été ajouté!');

            return $this->redirectToRoute('comment_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="comment_show", methods={"GET"})
     */
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="comment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment): Response
    {
        $user = $this->getUser();
        // accès à notre entité client
        $clientConnected = $user->getClient();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            if (!$clientConnected) {
                throw new \Exception("Vous n'êtes pas client, vous ne pouvez mettre des commentaires.");
            }
            else if ($clientConnected != $comment->getClient()) {
                throw new \Exception("Ce n'est pas votre commentaire.");
            }            
        }

        $form = $this->createForm(CommentType::class, $comment, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le commentaire a bien été modifié!');

            return $this->redirectToRoute('comment_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="comment_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le commentaire a bien été supprimé!');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->redirectToRoute('comment_index');
    }

    /**
     * @Route("/comment/room/{id}", name="room_comment", methods={"GET"})
     */
    public function romm_comment(Request $request): Response
    {
        // seul l'admin peut choisir la chambre pour sa réservation
        if (!$request->get('id')) 
            throw new \Exception("Action erronée.");
        
        $room = $this->getDoctrine()->getManager()->getRepository(Room::class)->find($request->get('id'));

        return $this->render('comment/index.html.twig', [
            'comments' => $room->getComments(),
        ]);
    }
}
