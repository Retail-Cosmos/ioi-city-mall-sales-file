<?php

namespace RetailCosmos\IoiCityMallSalesFile\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalesFileGenerationNotification extends Notification
{
    private $status;

    private $messages;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $status, string $messages)
    {
        $this->status = $status;
        $this->messages = $messages;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject('IOI City Mall Sales File Generation Notification')
            ->markdown('ioi-city-mall-sales-file::mail.file-generation', ['status' => $this->status, 'messages' => $this->messages]);
    }

    public function getStatus()
    {
        return $this->status;
    }
}
