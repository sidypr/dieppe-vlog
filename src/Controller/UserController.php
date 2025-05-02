<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/create-admin', name: 'create_admin')]
    public function createAdmin(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $admin = new User();
        $admin->setEmail('admin@dieppevlog.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('DieppeVlog');
        $admin->setRoles(['ROLE_ADMIN']);
        
        // Hash du mot de passe
        $hashedPassword = $passwordHasher->hashPassword(
            $admin,
            'admin123' // Mot de passe temporaire
        );
        $admin->setPassword($hashedPassword);
        
        $entityManager->persist($admin);
        $entityManager->flush();
        
        return new Response('Administrateur créé avec succès !');
    }
} 