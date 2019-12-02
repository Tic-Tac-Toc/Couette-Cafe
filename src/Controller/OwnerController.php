<?php

namespace App\Controller;

use App\Entity\Owner;
use App\Form\OwnerType;
use App\Repository\OwnerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/owner")
 */
class OwnerController extends AbstractController
{
    /**
     * @Route("/", name="owner_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(OwnerRepository $ownerRepository): Response
    {
        return $this->render('owner/index.html.twig', [
            'owners' => $ownerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="owner_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();

        // accès à notre entité Owner
        $owner = $user->getOwner();
        if ($owner && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new \Exception("Vous êtes déja propriétaire.");
        }

        $owner = new Owner();
        $form = $this->createForm(OwnerType::class, $owner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $user->setOwner($owner);
                $user->addRole("ROLE_OWNER");
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($owner);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le propriétaire a bien été ajouté!');

            return $this->redirectToRoute('home');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('owner/new.html.twig', [
            'owner' => $owner,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="owner_show", methods={"GET"})
     */
    public function show(Owner $owner): Response
    {
        return $this->render('owner/show.html.twig', [
            'owner' => $owner,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="owner_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Owner $owner): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            // accès à notre entité Owner
            $ownerConnected = $user->getOwner();
            if (!$ownerConnected) {
                throw new \Exception("Vous n'êtes pas propriétaire, vous ne pouvez modifier leurs profils.");
            }
            else if ($ownerConnected->getId() != $owner->getId()) {
                throw new \Exception("Ce n'est pas votre compte propriétaire !");
            }
        }

        $form = $this->createForm(OwnerType::class, $owner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le propriétaire a bien été modifié!');

            return $this->redirectToRoute('home');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('owner/edit.html.twig', [
            'owner' => $owner,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="owner_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Owner $owner): Response
    {
        if ($this->isCsrfTokenValid('delete'.$owner->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($owner);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le propriétaire a bien été supprimé!');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->redirectToRoute('owner_index');
    }
}
