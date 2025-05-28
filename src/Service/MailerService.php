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
        $html = '<h1>Default HTML Content</h1>',
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
            ->html($html);

        $this->mailer->send($email);
    }
    //use this command to run the mailer service worker
    // php bin/console messenger:consume async -vv
}

?>