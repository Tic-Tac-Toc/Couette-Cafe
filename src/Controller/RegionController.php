<?php

namespace App\Controller;

use App\Entity\Region;
use App\Entity\Room;
use App\Form\RegionType;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/region")
 */
class RegionController extends AbstractController
{
    /**
     * @Route("/", name="region_index", methods={"GET"})
     */
    public function index(RegionRepository $regionRepository): Response
    {
        return $this->render('region/index.html.twig', [
            'regions' => $regionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="region_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $region = new Region();
        $form = $this->createForm(RegionType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($region);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'La région a bien été ajoutée!');

            return $this->redirectToRoute('region_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('region/new.html.twig', [
            'region' => $region,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="region_show", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Region $region): Response
    {
        return $this->render('region/show.html.twig', [
            'region' => $region,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="region_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Region $region): Response
    {
        $form = $this->createForm(RegionType::class, $region);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('message', 'La région a bien été modifiée!');

            return $this->redirectToRoute('region_index');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('region/edit.html.twig', [
            'region' => $region,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="region_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Region $region): Response
    {
        if ($this->isCsrfTokenValid('delete'.$region->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($region);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'La région a bien été supprimée!');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->redirectToRoute('region_index');
    }

    /**
     * @Route("/{id}/rooms", name="region_rooms", methods={"GET","POST"})
     */
    public function getRegionRooms(Request $request, Region $region): Response
    {
        $roomRepository = $this->getDoctrine()->getRepository(Room::class);
        $rooms = $roomRepository->findAll();
        $roomsinregion = [];

        foreach  ($rooms as $room) {
            $regions = $room->getRegion();
            foreach ($regions as $reg) {
                if ($reg->getName() == $region->getName())
                    array_push($roomsinregion, $room);
            }
        }
        
        return $this->render('room/index.html.twig', [
            'rooms' => $roomsinregion,
            'title' => $region->getName()
        ]);
    }
}
