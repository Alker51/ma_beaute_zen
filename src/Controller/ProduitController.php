<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/produit', name: 'app_produit')]
final class ProduitController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findLatest50();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'controller_name' => 'ProduitController',
        ]);
    }

    #[Route('/show/{id}', name: '_show')]
    public function show(): Response
    {
        return $this->render('produit/show.html.twig', [
            'controller_name' => 'ProduitController',
        ]);}

    #[Route('/add', name: '_add')]
    public function add(): Response
    {
        return $this->render('produit/add.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    #[Route('/edit/{id}', name: '_edit')]
    public function edit(): Response
    {
        return $this->render('produit/edit.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }

    #[Route('/delete/{id}', name: '_delete')]
    public function delete(): Response
    {
        return $this->render('produit/delete.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }
}
