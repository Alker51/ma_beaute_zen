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

    public function getBootstrapColorByState(int $state): string
    {
        switch ($state) {
            case self::PENDING_STATE:
                $color = 'info';
            case self::FINISH_STATE:
                $color = 'success';
            case self::DISCONTINUED_STATE:
                $color = 'danger';
            case self::VALIDATED_STATE:
                $color = 'success';
            case self::PENDING_VALIDATION_STATE:
                $color = 'info';
            case self::REFUSE_STATE:
                $color = 'danger';
            case self::EXECUTED_STATE:
                $color = 'primary';
            case self::CANCEL_STATE:
                $color = 'danger';
        }

        return $color;
    }
}
