<?php

namespace ControleGastos\Services;

use ControleGastos\Interfaces\NotificavelInterface;

class NotificacaoEmail implements NotificavelInterface
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function enviar(string $mensagem): void
    {
        echo "📧 E-mail enviado para {$this->email}: {$mensagem}" . PHP_EOL;
    }
}
