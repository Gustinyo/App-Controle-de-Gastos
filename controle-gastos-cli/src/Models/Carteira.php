<?php

declare(strict_types=1);

namespace App\Models;


class Carteira
{
    private static int $contador = 0;
    private int $id;

    /** @var Transacao[] */
    private array $transacoes = [];

    public function __construct(
        private string $nome,
        private string $moeda = 'BRL',
        private float $saldoInicial = 0.0
    ) {
        self::$contador++;
        $this->id = self::$contador;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getMoeda(): string { return $this->moeda; }
    public function getSaldoInicial(): float { return $this->saldoInicial; }

    public function adicionarTransacao(
        string $descricao,
        float $valor,
        string $tipo,
        MetodoPagamento $metodoPagamento
    ): Transacao {
        $transacao = new Transacao($descricao, $valor, $tipo, $metodoPagamento);
        $this->transacoes[] = $transacao;
        return $transacao;
    }

    /** @return Transacao[] */
    public function getTransacoes(): array
    {
        return $this->transacoes;
    }

    public function getSaldoAtual(): float
    {
        $totalReceitas = array_sum(array_map(
            fn(Transacao $t) => $t->getTipo() === 'receita' ? $t->getValor() : 0,
            $this->transacoes
        ));
        $totalDespesas = array_sum(array_map(
            fn(Transacao $t) => $t->getTipo() === 'despesa' ? $t->getValor() : 0,
            $this->transacoes
        ));
        return $this->saldoInicial + $totalReceitas - $totalDespesas;
    }

    public function __toString(): string
    {
        return sprintf(
            "[%d] %s (%s) | Saldo: R$ %.2f",
            $this->id,
            $this->nome,
            $this->moeda,
            $this->getSaldoAtual()
        );
    }
}
