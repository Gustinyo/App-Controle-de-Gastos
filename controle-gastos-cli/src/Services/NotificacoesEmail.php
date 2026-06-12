<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\NotificavelInterface;

class NotificacaoEmail implements NotificavelInterface
{
    public function __construct(
        private string $servidorSmtp = 'localhost',
        private int $porta = 587
    ) {}

    public function enviarNotificacao(string $destinatario, string $assunto, string $mensagem): bool
    {
        echo "[EMAIL] Para: {$destinatario} | Assunto: {$assunto}" . PHP_EOL;
        echo "        Mensagem: {$mensagem}" . PHP_EOL;
        return true;
    }

    public function getTipoNotificacao(): string
    {
        return 'Email';
    }

    public function getServidorSmtp(): string
    {
        return $this->servidorSmtp;
    }
}
