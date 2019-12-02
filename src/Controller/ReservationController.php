<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;

use App\Entity\Room;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


/**
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    /**
     * @Route("/", name="reservation_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="reservation_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        // accès à notre entité Client
        $client = $user->getClient();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {            
            // seul l'admin peut choisir la chambre pour sa réservation
            if (!$request->get('id')) 
                throw new \Exception("Vous ne pouvez effectuer cette action.");
            $room = $this->getDoctrine()->getManager()->getRepository(Room::class)->find($request->get('id'));

            // Si réservation normal, vérifier que l'utilisateur est client
            if (!$request->get('unvailable')) {
                if (!$client)
                    throw new \Exception("Vous n'êtes pas client, vous ne pouvez réserver un appartement, merci de modifier votre profil.");
            }
            else { 
                $owner = $user->getOwner();
                if (!$owner)
                    throw new \Exception("Vous n'êtes pas propriétaire, vous ne pouvez pas ajouter de périodes d'indisponibilités.");
                if (!$room->isOwner($owner))
                    throw new \Exception("Vous n'êtes pas propriétaire de ce bien, vous ne pouvez pas ajouter de périodes d'indisponibilités.");
            }
        }

        $reservation = new Reservation();
        $reservation->setClient($client);
        $reservation->setValidate(false);
        if (isset($room))
            $reservation->setRoom($room);

        $form = $this->createForm(ReservationType::class, $reservation, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->CheckReservation($reservation)) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($reservation);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('message', 'La réservation a bien été ajoutée!');
            }
            else {
                throw new \Exception("Des réservations existent sur ces dates.");
            }

            return $this->redirectToRoute('room_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
            'room' => isset($room) ? $room : null,
            'unvailable' => $request->get('unvailable') ? $request->get('unvailable') : false,
        ]);
    }

    /**
     * @Route("/{id}", name="reservation_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reservation_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Reservation $reservation): Response
    {
        $user = $this->getUser();
        // accès à notre entité client
        $clientConnected = $user->getClient();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            if (!$clientConnected) {
                throw new \Exception("Vous n'êtes pas client, vous ne pouvez effectuer des réservations.");
            }
            else if ($clientConnected->getId() != $reservation->getClient()->getId()) {
                throw new \Exception("Ce n'est pas votre réservation !");
            }
        }

        $form = $this->createForm(ReservationType::class, $reservation, [
            'is_admin' => in_array('ROLE_ADMIN', $user->getRoles()),]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('message', 'La réservation a bien été modifiée!');

            return $this->redirectToRoute('room_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reservation_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'La réservation a bien été supprimée!');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        }

        return $this->redirectToRoute('room_index');
    }    

    public function CheckReservation($reservation) : bool
    {
        foreach ($this->getDoctrine()->getManager()->getRepository(Reservation::class)->findAll() as $rsv)
            if (($rsv->getStartDate() <= $reservation->getEndDate()) && ($reservation->getStartDate() <= $rsv->getEndDate()))
                return false;
        return true;
    }
}
