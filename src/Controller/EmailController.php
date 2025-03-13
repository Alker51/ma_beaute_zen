<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class EmailController extends AbstractController
{
    #[Route('/email', name: 'app_email')]
    public function sendMail(string $to, string $subject,string $body): bool
    {
        $transport = Transport::fromDsn('smtp://localhost:1025');
        $mailer = new Mailer($transport);

        $email = new Email()
            ->from('no-replay@ma-beaute-zen.fr')
            ->to($to)
            ->subject($subject)
            ->text($body);

        try{
            $mailer->send($email);
        }catch(\Exception $e){
            return false;
        }

        return true;
    }
}
