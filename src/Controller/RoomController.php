<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Reservation;

/**
 * @Route("/room")
 */
class RoomController extends AbstractController
{
    /**
     * @Route("/", name="room_index", methods={"GET"})
     */
    public function index(RoomRepository $roomRepository): Response
    {
        return $this->render('room/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="room_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        // accès à notre entité Owner
        $owner = $user->getOwner();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            if (! $owner) {
                throw new \Exception("Vous n'êtes pas propriétaire, vous ne pouvez déposer une annonce, merci de modifier votre profil.");
            }
        }

        $room = new Room();
        $room->setOwner($owner);
        $form = $this->createForm(RoomType::class, $room, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($room);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le logement a bien été ajouté!');

            return $this->redirectToRoute('room_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="room_show", methods={"GET"})
     */
    public function show(Room $room): Response
    {
        return $this->render('room/show.html.twig', [
            'room' => $room,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="room_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Room $room): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            // accès à notre entité Owner
            $owner = $user->getOwner();
            if (! $owner) {
                throw new \Exception("Vous n'êtes pas propriétaire, vous ne pouvez déposer une annonce, merci de modifier votre profil.");
            }
            else if ($owner->getId() != $room->getOwner()->getId()) {
                throw new \Exception("Vous n'êtes pas le propriétaire de cette chambre.");
            }
        }
        
        $form = $this->createForm(RoomType::class, $room, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le logement a bien été modifié!');

            return $this->redirectToRoute('room_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="room_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Room $room): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            // accès à notre entité Owner
            $owner = $user->getOwner();
            if (! $owner) {
                throw new \Exception("Vous n'êtes pas propriétaire, vous ne pouvez supprimer une annonce, merci de modifier votre profil.");
            }
            else if ($owner->getId() != $room->getOwner()->getId()) {
                throw new \Exception("Vous n'êtes pas le propriétaire de cette chambre.");
            }
        }

        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le logement a bien été supprimé!');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->redirectToRoute('room_index');
    }

    /**
     * @Route("/check/{id}", name="room_check", methods={"GET","POST"})
     */
    public function check(Request $request, Room $room): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            // accès à notre entité Owner
            $owner = $user->getOwner();
            if (! $owner) {
                throw new \Exception("Vous n'êtes pas propriétaire, vous ne pouvez supprimer une annonce, merci de modifier votre profil.");
            }
            else if ($owner->getId() != $room->getOwner()->getId()) {
                throw new \Exception("Vous n'êtes pas le propriétaire de cette chambre.");
            }
        }

        $reservationRepository = $this->getDoctrine()->getRepository(Reservation::class);
        $reservations = $reservationRepository->findBy(['room' => $room]);
        
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
            'titre' => $room->getSummary()
        ]);
    }
}
