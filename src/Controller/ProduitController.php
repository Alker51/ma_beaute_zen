<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/produit', name: 'app_produit')]
final class ProduitController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    public function add(): Response
    {
        return $this->render('produit/add.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    public function edit(): Response
    {
        return $this->render('produit/edit.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    public function delete(): Response
    {
        return $this->render('produit/delete.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }
}
