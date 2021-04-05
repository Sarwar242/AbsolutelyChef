<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\User;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Lang;

class MembershipMail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    public function __construct($user)
    {
        $this->user =$user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Welcome to Absolutely Chef')
                    ->greeting('Dear '.$this->user->name.',')
                    ->line('Thank you for signing up with us. Your new account has been setup and you can now login using the details below: ')
                    ->line('Email Address: '.$this->user->email.'')
                    ->line('Password: ************')
                    ->action('Login', url('https://absolutelychef.com/login'))
                    ->line('To ensure delivery to your inbox (not bulk or junk folders), please add noreply@Absolutelychef.com, support@Absolutelychef.com to your safe senders list or address book.')
                    ->line('We strongly suggest that you familiarise yourself with our Terms of Service before getting started. These can be found at the bottom of any page on our website.')
                    ->line('Thank you for choosing us!')
                    ->line('If you have any questions or concerns, please do not hesitate to contact us via our Live Chat or Contact Form on our website.')
                    ->line(new HtmlString('<strong>Regards,</strong>'))
                    ->line(new HtmlString('<strong>The Absolutely Chef Team</strong>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line(new HtmlString('<br>'))
                    ->line('To ensure delivery to your inbox (not bulk or junk folders), please add noreply@Absolutelychef.com, support@Absolutelychef.com to your safe senders list or address book.')
                    ->line('STAY SAFE, STAY SECURE: We never ask for your personal account details by email.')
                    ->line('')
                    ->line('The information in this message is confidential and is intended solely for the addressee.')
                    ->line('Access to this e-mail by anyone else is unauthorised. If you are not the intended recipient, any disclosure, copying, distribution or any action taken or omitted in reliance on this, is prohibited and may be unlawful.')
                    ->line('Whilst all sensible steps are taken to ensure the accuracy and integrity of information and data transmitted electronically and to preserve the confidentiality thereof, no liability or responsibility whatsoever is accepted if information or data is, for whatever reason, corrupted or does not reach its intended destination.')
                    ->salutation('This email was sent to you by Absolutelychef.com.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
