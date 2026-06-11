<?php

namespace ControleGastos\Services;

use ControleGastos\Interfaces\NotificavelInterface;

class NotificacaoSms implements NotificavelInterface
{
    private string $telefone;

    public function __construct(string $telefone)
    {
        $this->telefone = $telefone;
    }

    public function enviar(string $mensagem): void
    {
        echo "📱 SMS enviado para {$this->telefone}: {$mensagem}" . PHP_EOL;
    }
}