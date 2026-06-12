<?php

declare(strict_types=1);

namespace App\Models;

class MetodoPagamento
{
    private static int $contador = 0;
    private int $id;

    public function __construct(
        private string $nome,
        private string $tipo,
        private bool $ativo = true
    ) {
        self::$contador++;
        $this->id = self::$contador;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function desativar(): void
    {
        $this->ativo = false;
    }

    public function __toString(): string
    {
        $status = $this->ativo ? 'Ativo' : 'Inativo';
        return "[{$this->id}] {$this->nome} ({$this->tipo}) - {$status}";
    }
}
