<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Film;
use App\Repository\FilmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PublicController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FilmRepository $filmRepository
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('public/index.html.twig', [
            'films' => $this->filmRepository->findAllActive(),
        ]);
    }

    #[Route('/film/{id}', name: 'film_show')]
    public function show(Film $film): Response
    {
        // Vérifier si le film est actif
        if (!$film->isActive()) {
            throw $this->createNotFoundException('Le film demandé n\'existe pas ou n\'est pas actif.');
        }

        // Récupérer les films similaires (même date de création, par exemple)
        $similarFilms = $this->filmRepository->findSimilarFilms($film, 5);

        return $this->render('public/film_show.html.twig', [
            'film' => $film,
            'similar_films' => $similarFilms
        ]);
    }

    #[Route('/film/{id}/comment', name: 'film_comment', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function comment(Film $film, Request $request): Response
    {
        $content = trim($request->request->get('content'));
        
        if (empty($content)) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('film_show', ['id' => $film->getId()]);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setFilm($film);
        $comment->setUser($this->getUser());
        $comment->setCreatedAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté.');
        return $this->redirectToRoute('film_show', ['id' => $film->getId()]);
    }

    #[Route('/film/{id}/like', name: 'film_like')]
    #[IsGranted('ROLE_USER')]
    public function like(Film $film): Response
    {
        $user = $this->getUser();
        
        if ($film->getLikedBy()->contains($user)) {
            $film->removeLikedBy($user);
            $message = 'Vous n\'aimez plus ce film.';
        } else {
            $film->addLikedBy($user);
            $message = 'Vous aimez ce film !';
        }
        
        $this->entityManager->flush();
        $this->addFlash('success', $message);
        
        return $this->redirectToRoute('film_show', ['id' => $film->getId()]);
    }
} 