<?php

declare(strict_types=1);

namespace ControleGastos\Models;

final class MetodoPagamento
{
    public function __construct(
        private string $nome,
        private bool $ativo = true
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        if (trim($this->nome) === '') {
            throw new \InvalidArgumentException(
                'O nome do método de pagamento é obrigatório.'
            );
        }
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function estaAtivo(): bool
    {
        return $this->ativo;
    }

    public function ativar(): void
    {
        $this->ativo = true;
    }

    public function desativar(): void
    {
        $this->ativo = false;
    }

    public function __toString(): string
    {
        return $this->nome;
    }
}
