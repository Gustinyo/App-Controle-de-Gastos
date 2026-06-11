<?php

declare(strict_types=1);

namespace App\Models;

class Categoria
{
    private string $nome = 'Geral';
    private string $corIdentificadora = '#FFFFFF';
    private array $transacoes = [];

    public function __construct() 
    {
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getCorIdentificadora(): string
    {
        return $this->corIdentificadora;
    }

    public function setCorIdentificadora(string $corIdentificadora): void
    {
        $this->corIdentificadora = $corIdentificadora;
    }

    public function adicionarTransacao(Transacao $transacao): void
    {
        $this->transacoes[] = $transacao;
    }

    public function getTransacoes(): array
    {
        return $this->transacoes;
    }

    public function calcularTotalGasto(): float
    {
        $total = 0.0;
        foreach ($this->transacoes as $transacao) {
            $total += $transacao->getValor();
        }
        return $total;
    }
}
