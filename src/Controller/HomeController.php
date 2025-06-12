<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/', name: 'app_')]
final class HomeController extends AbstractController
{
    #[Route('', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'project_name' => 'Ma beauté zen',
        ]);
    }

    #[Route('/error', name: 'error')]
    public function error(Request $request): Response
    {
        $errorTitle = $request->query->get('title');
        $errorMessage = $request->query->get('message');


        return $this->render('home/error.html.twig', [
            'title' => $errorTitle,
            'message' => $errorMessage,
        ]);
    }
}
