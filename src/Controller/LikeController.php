<?php

namespace App\Controller;

use App\Entity\Room;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LikeController extends AbstractController
{
    /**
     * @Route("/like/{id}", name="like_add")
     */
    public function likeadd(Request $request) : Response
    {
        if (!$request->get('id')) 
                throw new \Exception("Vous ne pouvez effectuer cette action.");
        $likes = $this->get('session')->get('likes');
        if ($likes == null)
            $likes = [];

        $id = $request->get('id');
        $room = $this->getDoctrine()->getManager()->getRepository(Room::class)->find($id);

        // si l'identifiant n'est pas présent dans le tableau des likes, l'ajouter
        if (!in_array($id, $likes) ) 
        {
            $likes[] = $id;
            $this->get('session')->getFlashBag()->add('message', 'Vous avez aimé le logement '. $room->getSummary(). '!');
        }
        else
        // sinon, le retirer du tableau
        {
            $likes = array_diff($likes, array($id));
            $this->get('session')->getFlashBag()->add('error', "Vous n'aimez plus le logement ". $room->getSummary(). "!");
        }

        $this->get('session')->set('likes', $likes);

       
        return $this->redirectToRoute('room_index');
    }

     /**
     * @Route("/like", name="like")
     */
    public function index() : Response
    {
        $likes = $this->get('session')->get('likes');
        $rooms = [];
        foreach ($likes as $like) {
            array_push($rooms, $this->getDoctrine()->getManager()->getRepository(Room::class)->find($like));
        }
        return $this->render('room/index.html.twig', [
            'rooms' => $rooms,
            'title' => "Mes Likes"
        ]);
        return $this->redirectToRoute('room_index');
    }
}
