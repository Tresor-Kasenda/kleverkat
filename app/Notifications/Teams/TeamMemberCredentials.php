<?php

declare(strict_types=1);

namespace App\Notifications\Teams;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Team $team,
        public string $temporaryPassword,
    ) {}

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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Votre accès à l’équipe :team', ['team' => $this->team->name]))
            ->greeting(__('Bonjour :name', ['name' => $notifiable->name]))
            ->line(__('Un compte a été créé pour vous afin de rejoindre l’équipe :team.', ['team' => $this->team->name]))
            ->line(__('Email : :email', ['email' => $notifiable->email]))
            ->line(__('Mot de passe temporaire : :password', ['password' => $this->temporaryPassword]))
            ->action(__('Se connecter'), route('login'))
            ->line(__('Pensez à changer votre mot de passe après votre première connexion.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
        ];
    }
}
