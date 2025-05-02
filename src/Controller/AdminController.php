<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Film;
use App\Form\UserType;
use App\Form\FilmType;
use App\Service\VideoUploader;
use App\Service\VideoUrlTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\VideoStorage;
use App\Service\VideoCompressor;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FormFactoryInterface $formFactory;
    private UserPasswordHasherInterface $passwordHasher;
    private VideoUrlTransformer $videoUrlTransformer;
    private VideoUploader $videoUploader;
    private VideoStorage $videoStorage;
    private VideoCompressor $videoCompressor;

    public function __construct(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        UserPasswordHasherInterface $passwordHasher,
        VideoUrlTransformer $videoUrlTransformer,
        VideoUploader $videoUploader,
        VideoStorage $videoStorage,
        VideoCompressor $videoCompressor
    ) {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->passwordHasher = $passwordHasher;
        $this->videoUrlTransformer = $videoUrlTransformer;
        $this->videoUploader = $videoUploader;
        $this->videoStorage = $videoStorage;
        $this->videoCompressor = $videoCompressor;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $films = $this->entityManager->getRepository(Film::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'films' => $films
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/users/new', name: 'admin_user_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newUser(Request $request): Response
    {
        $user = new User();
        $form = $this->formFactory->create(UserType::class, $user, ['is_new' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouvel utilisateur'
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editUser(User $user, Request $request): Response
    {
        $form = $this->formFactory->create(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier l\'utilisateur'
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user): Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/films', name: 'admin_films')]
    #[IsGranted('ROLE_ADMIN')]
    public function films(): Response
    {
        $films = $this->entityManager->getRepository(Film::class)->findAll();

        return $this->render('admin/films.html.twig', [
            'films' => $films
        ]);
    }

    #[Route('/admin/films/new', name: 'admin_film_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function newFilm(Request $request): Response
    {
        $film = new Film();
        $form = $this->formFactory->create(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $videoFile */
            $videoFile = $form->get('videoFile')->getData();

            if ($videoFile) {
                try {
                    // Compression de la vidéo
                    $compressedVideoPath = $this->videoCompressor->compress($videoFile);
                    
                    // Upload vers S3
                    $videoUrl = $this->videoStorage->uploadVideo(new UploadedFile(
                        $compressedVideoPath,
                        $videoFile->getClientOriginalName(),
                        $videoFile->getMimeType()
                    ));
                    
                    // Nettoyage du fichier temporaire
                    $this->videoCompressor->cleanup($compressedVideoPath);
                    
                    $film->setVideoUrl($videoUrl);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de la vidéo : ' . $e->getMessage());
                    return $this->redirectToRoute('admin_films');
                }
            }

            $this->entityManager->persist($film);
            $this->entityManager->flush();

            $this->addFlash('success', 'Film ajouté avec succès !');
            return $this->redirectToRoute('admin_films');
        }

        return $this->render('admin/film_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nouveau film'
        ]);
    }

    #[Route('/admin/films/{id}/edit', name: 'admin_film_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editFilm(Request $request, Film $film): Response
    {
        $form = $this->formFactory->create(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $videoFile */
            $videoFile = $form->get('videoFile')->getData();

            if ($videoFile) {
                try {
                    // Suppression de l'ancienne vidéo si elle existe
                    if ($film->getVideoUrl()) {
                        $this->videoStorage->deleteVideo($film->getVideoUrl());
                    }

                    // Compression de la vidéo
                    $compressedVideoPath = $this->videoCompressor->compress($videoFile);
                    
                    // Upload vers S3
                    $videoUrl = $this->videoStorage->uploadVideo(new UploadedFile(
                        $compressedVideoPath,
                        $videoFile->getClientOriginalName(),
                        $videoFile->getMimeType()
                    ));
                    
                    // Nettoyage du fichier temporaire
                    $this->videoCompressor->cleanup($compressedVideoPath);
                    
                    $film->setVideoUrl($videoUrl);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors du traitement de la vidéo : ' . $e->getMessage());
                    return $this->redirectToRoute('admin_films');
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Film modifié avec succès !');
            return $this->redirectToRoute('admin_films');
        }

        return $this->render('admin/film_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le film'
        ]);
    }

    #[Route('/admin/films/{id}/delete', name: 'admin_film_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteFilm(Film $film): Response
    {
        $this->entityManager->remove($film);
        $this->entityManager->flush();

        $this->addFlash('success', 'Film supprimé avec succès');
        return $this->redirectToRoute('admin_films');
    }
} 