<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StateController extends AbstractController
{
    public CONST int PENDING_STATE = 1;
    public CONST int FINISH_STATE = 2;
    public CONST int DISCONTINUED_STATE = 3;
    public CONST int VALIDATED_STATE = 4;
    public CONST int PENDING_VALIDATION_STATE = 5;
    public CONST int REFUSE_STATE = 6;
    public CONST int EXECUTED_STATE = 7;
    public CONST int CANCEL_STATE = 8;

    #[Route('/state', name: 'app_state')]
    public function index(): Response
    {
        return $this->render('state/index.html.twig', [
            'controller_name' => 'StateController',
        ]);
    }
}
