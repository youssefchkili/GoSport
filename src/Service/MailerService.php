<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(private MailerInterface $mailer){}
    public function sendEmail(
        $to,
        $subject = 'Default Subject',
        $text = 'Default text content',
    ): void
    {
        $email = (new Email())
            ->from('GoSport.INSAT@gmail.com')
            ->to( $to )
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->html('<html><body><h2>' . htmlspecialchars($subject) . '</h2><p>' . nl2br(htmlspecialchars($text)) . '</p></body></html>');

        $this->mailer->send($email);
    }
}

?>