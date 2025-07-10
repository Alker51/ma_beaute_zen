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
            case self::PENDING_VALIDATION_STATE:
            case self::PENDING_STATE:
                $color = 'info';
                break;
            case self::VALIDATED_STATE:
            case self::EXECUTED_STATE:
            case self::FINISH_STATE:
                $color = 'success';
                break;
            case self::REFUSE_STATE:
            case self::CANCEL_STATE:
            case self::DISCONTINUED_STATE:
                $color = 'danger';
                break;
            default:
                $color = 'primary';
                break;
        }

        return $color;
    }

    public  function getColorByState(int $state): string
    {
        switch ($state) {
            case self::PENDING_VALIDATION_STATE:
            case self::PENDING_STATE:
                $color = "#FFCC00";
                break;
            case self::VALIDATED_STATE:
            case self::EXECUTED_STATE:
            case self::FINISH_STATE:
                $color = '#006600';
                break;
            case self::REFUSE_STATE:
            case self::CANCEL_STATE:
            case self::DISCONTINUED_STATE:
                $color = '#CC0000';
                break;
            default:
                $color = '#6699FF';
                break;
        }

        return $color;
    }

    public  function getTextColorByState(int $state): string
    {
        switch ($state) {
            case self::VALIDATED_STATE:
            case self::EXECUTED_STATE:
            case self::DISCONTINUED_STATE:
            case self::CANCEL_STATE:
            case self::REFUSE_STATE:
            case self::FINISH_STATE:
                $color = '#FFFFFF';
                break;
            default:
                $color = '#000000';
                break;
        }

        return $color;
    }
}
