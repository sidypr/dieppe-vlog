<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Comment;
use App\Form\CommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FilmController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/films', name: 'film_list')]
    public function index(): Response
    {
        $films = $this->entityManager->getRepository(Film::class)->findAll();

        return $this->render('film/index.html.twig', [
            'films' => $films
        ]);
    }

    #[Route('/film/{id}', name: 'film_show')]
    public function show(Film $film, Request $request): Response
    {
        // Créer un nouveau commentaire si l'utilisateur est connecté
        $comment = new Comment();
        $form = null;
        
        if ($this->getUser()) {
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $comment->setFilm($film);
                $comment->setUser($this->getUser());
                $comment->setCreatedAt(new \DateTime());
                
                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                $this->addFlash('success', 'Votre commentaire a été ajouté');
                return $this->redirectToRoute('film_show', ['id' => $film->getId()]);
            }
        }

        return $this->render('film/show.html.twig', [
            'film' => $film,
            'commentForm' => $form ? $form->createView() : null
        ]);
    }

    #[Route('/film/{id}/like', name: 'film_like')]
    #[IsGranted('ROLE_USER')]
    public function like(Film $film): Response
    {
        $user = $this->getUser();
        
        if ($film->getLikedBy()->contains($user)) {
            $film->removeLikedBy($user);
            $message = 'Like retiré';
        } else {
            $film->addLikedBy($user);
            $message = 'Film liké';
        }
        
        $this->entityManager->flush();
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('film_show', ['id' => $film->getId()]);
    }
} 