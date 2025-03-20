<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaxController extends AbstractController
{
    #[Route('/tax', name: 'app_tax')]
    public function index(): Response
    {
        return $this->render('tax/index.html.twig', [
            'controller_name' => 'TaxController',
        ]);
    }
}
