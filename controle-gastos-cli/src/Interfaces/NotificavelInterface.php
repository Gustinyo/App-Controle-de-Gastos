<?php

declare(strict_types=1);

namespace App\Interfaces;

interface NotificavelInterface
{
    public function enviarNotificacao(string $destinatario, string $assunto, string $mensagem): bool;
    public function getTipoNotificacao(): string;
}
