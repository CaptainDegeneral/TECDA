<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        JsonResource::withoutWrapping();

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->greeting('Здравствуйте!')
                ->subject('Подтвердите адрес электронной почты')
                ->line('Нажмите кнопку ниже, чтобы подтвердить свой адрес электронной почты.')
                ->action('Подтвердите адрес электронной почты', $url)
                ->salutation('С уважением, команда сайта');
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Сброс пароля')
                ->greeting('Здравствуйте!')
                ->line('Вы получили это письмо, так как мы получили запрос на сброс пароля для вашей учетной записи.')
                ->action('Сбросить пароль', $url)
                ->line('Ссылка для сброса пароля истечёт через '
                    . config('auth.passwords.'.config('auth.defaults.passwords').'.expire')
                    . ' минут.')
                ->line('Если вы не запрашивали сброс пароля, никаких действий предпринимать не нужно.')
                ->salutation('С уважением, команда сайта');
        });
    }
}
