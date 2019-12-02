<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/", name="client_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="client_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();

        // accès à notre entité Client
        $client = $user->GetClient();
        if ($client && !in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new \Exception("Vous êtes déja client.");
        }

        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                $user->setClient($client);
                $user->addRole("ROLE_CLIENT");
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le client a bien été ajouté!');

            return $this->redirectToRoute('home');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="client_show", methods={"GET"})
     */
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="client_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Client $client): Response
    {
        $user = $this->getUser();
        // accès à notre entité client
        $clientConnected = $user->getClient();
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
        {
            if (!$clientConnected) {
                throw new \Exception("Vous n'êtes pas client, vous ne pouvez modifier leurs profils.");
            }
            else if ($clientConnected->getId() != $client->getId()) {
                throw new \Exception("Ce n'est pas votre compte client !");
            }
        }
        
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le client a bien été modifié!');

            return $this->redirectToRoute('home');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="client_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Client $client): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($client);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('message', 'Le client a bien été supprimé!');
        } else {
            $this->get('session')->getFlashBag()->add('error', "Une erreur s'est produite.");
        } 

        return $this->redirectToRoute('client_index');
    }
}
