<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define os comandos Artisan do aplicativo.
     */
    protected $commands = [
        // Registra o comando de notificações WhatsApp
        \App\Console\Commands\EnviarNotificacoesWhatsApp::class,
    ];

    /**
     * Define o agendamento de comandos.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Enviar notificações WhatsApp todo dia às 08:00 da manhã
        $schedule->command('whatsapp:notificar')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/whatsapp.log'));

        // Backup automático (se ativado nas configurações)
        $schedule->command('backup:automatico')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // PARA TESTES: descomente a linha abaixo para testar a cada minuto
        // $schedule->command('whatsapp:notificar')->everyMinute()->appendOutputTo(storage_path('logs/whatsapp.log'));
    }

    /**
     * Registra os comandos para o aplicativo.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}