<?php
namespace Src\ModeLs;

class Carteira 
{ 
    private string $nome;
    private float $saldo;
    private array $transacoes;

    public function __construct(string $nome, float $saldoInicial = 0)
    {
        $this->nome = $nome
        $this->saldo + $saldoInicial;
        $this->transacoes +[];

    }

public function getNome(): string
{
    return $this->nome;
}
public function getSaldo(): float
{
    return $this->saldo;
}

public function listarTransacoes(): array
{
    return $this->transacoes;
}
public function exibirResumo(): void
{
    echo PHP_EOL;
    echo "Carteira: {$this->nome}" . PHP_EOL;
    echo "Saldo: R$ " . number_format($this->saldo, 2, ',', '.') . PHP_EOL;
    echo "Total de transações: " . count($this->transacoes) .PHP_EOL;
    }
}