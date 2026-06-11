<?php

declare(strict_types=1);

namespace ControleGastos\Models;

final class Carteira
{
    /**
     * @var Transacao[]
     */
    private array $transacoes = [];

    public function __construct(
        private float $saldo = 0.0
    ) {
    }

    public function depositar(float $valor): void
    {
        if ($valor <= 0) {
            throw new \InvalidArgumentException(
                'O valor do depósito deve ser maior que zero.'
            );
        }

        $this->saldo += $valor;
    }

    public function sacar(float $valor): void
    {
        if ($valor <= 0) {
            throw new \InvalidArgumentException(
                'O valor do saque deve ser maior que zero.'
            );
        }

        if ($valor > $this->saldo) {
            throw new \RuntimeException(
                'Saldo insuficiente.'
            );
        }

        $this->saldo -= $valor;
    }

    public function adicionarTransacao(
        Transacao $transacao
    ): void {
        if ($transacao->getValor() > $this->saldo) {
            throw new \RuntimeException(
                'Saldo insuficiente para registrar a transação.'
            );
        }

        $this->transacoes[] = $transacao;
        $this->saldo -= $transacao->getValor();
    }

    public function getSaldo(): float
    {
        return $this->saldo;
    }

    /**
     * @return Transacao[]
     */
    public function getTransacoes(): array
    {
        return $this->transacoes;
    }

    public function getTotalGasto(): float
    {
        return array_reduce(
            $this->transacoes,
            fn (float $total, Transacao $transacao): float =>
                $total + $transacao->getValor(),
            0.0
        );
    }

    public function exibirResumo(): void
    {
        echo PHP_EOL;
        echo "===== CARTEIRA =====" . PHP_EOL;
        echo "Saldo Atual: R$ " .
            number_format($this->saldo, 2, ',', '.') .
            PHP_EOL;

        echo "Total Gasto: R$ " .
            number_format($this->getTotalGasto(), 2, ',', '.') .
            PHP_EOL;

        echo "Quantidade de Transações: " .
            count($this->transacoes) .
            PHP_EOL;
    }
}
