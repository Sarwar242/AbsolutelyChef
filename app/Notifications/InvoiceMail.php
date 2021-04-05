<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\User;
use App\Order;
use App\Payment;
use Illuminate\Support\Facades\Lang;

class InvoiceMail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $payment;
    public function __construct($payment)
    {
        $this->payment = $payment;
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
                        ->subject('Absolutely Chef Order and Invoice Payment Confirmation')
                        ->greeting('Dear '.ucwords($notifiable->name).',')
                        ->line('This is a notice that we have received your order and an invoice has been generated and paid on '.$this->payment->created_at->format('d/m/Y').'.')
                        ->line('Your payment method is: Credit/Debit Card')
                        ->line('Invoice #ABC-010-'.$this->payment->id.'')
                        ->line('Amount Due: £'.$this->payment->amount)
                        ->line('Due Date: '.$this->payment->created_at->format('d/m/Y'))
                        ->line('Invoice Items:')
                        ->line(ucwords($this->payment->package->label))
                        ->line(ucwords($this->payment->package_name))
                        ->line('-----------------------------------------------------------------------------')
                        ->line('Sub Total: '.$this->payment->amount)
                        ->line('Credit: £0.00')
                        ->line('Total: '.$this->payment->amount)
                        ->line('-----------------------------------------------------------------------------')
                        ->line('')
                        ->action('Invoice', url('https://absolutelychef.com/invoice/paid/'.$this->payment->id))
                        ->line('If you have any questions or concerns, please do not hesitate to contact us via our Contact Form on our website.')
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
                        ->line(new HtmlString('<br>'))
                        ->line(new HtmlString('<br>'))
                        ->line(new HtmlString('<br>'))
                        ->line('This email was sent to you by Absolutelychef.com. ')
                        ->line('To ensure delivery to your inbox (not bulk or junk folders), please add noreply@Absolutelychef.com, support@Absolutelychef.com to your safe senders list or address book.')
                        ->line('STAY SAFE, STAY SECURE: We never ask for your personal account details by email.')
                        ->line('')
                        ->line('The information in this message is confidential and is intended solely for the addressee.')
                        ->line('Access to this e-mail by anyone else is unauthorised. If you are not the intended recipient, any disclosure, copying, distribution or any action taken or omitted in reliance on this, is prohibited and may be unlawful.')
                        ->salutation('Whilst all sensible steps are taken to ensure the accuracy and integrity of information and data transmitted electronically and to preserve the confidentiality thereof, no liability or responsibility whatsoever is accepted if information or data is, for whatever reason, corrupted or does not reach its intended destination.');
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
