<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\NotificavelInterface;

class NotificacaoSms implements NotificavelInterface
{
    public function __construct(
        private string $provedor = 'Twilio',
        private string $numeroPadrao = '+5500000000000'
    ) {}

    public function enviarNotificacao(string $destinatario, string $assunto, string $mensagem): bool
    {
        echo "[SMS] Para: {$destinatario} | Assunto: {$assunto}" . PHP_EOL;
        echo "      Mensagem: {$mensagem}" . PHP_EOL;
        return true;
    }

    public function getTipoNotificacao(): string
    {
        return 'SMS';
    }

    public function getProvedor(): string
    {
        return $this->provedor;
    }
}
