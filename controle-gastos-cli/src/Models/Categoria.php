<?php

declare(strict_types=1);

namespace App\Models;


class Categoria
{
    private static int $contador = 0;
    private int $id;

    /** @var Transacao[] */
    private array $transacoes = [];

    public function __construct(
        private string $nome,
        private string $cor = '#3498db'
    ) {
        self::$contador++;
        $this->id = self::$contador;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getCor(): string { return $this->cor; }

    public function adicionarTransacao(Transacao $transacao): void
    {
        $this->transacoes[] = $transacao;
    }

    /** @return Transacao[] */
    public function getTransacoes(): array
    {
        return $this->transacoes;
    }

    public function getTotalReceitas(): float
    {
        return array_sum(array_map(
            fn(Transacao $t) => $t->getTipo() === 'receita' ? $t->getValor() : 0,
            $this->transacoes
        ));
    }

    public function getTotalDespesas(): float
    {
        return array_sum(array_map(
            fn(Transacao $t) => $t->getTipo() === 'despesa' ? $t->getValor() : 0,
            $this->transacoes
        ));
    }

    public function getSaldo(): float
    {
        return $this->getTotalReceitas() - $this->getTotalDespesas();
    }

    public function __toString(): string
    {
        return "[{$this->id}] {$this->nome} | " . count($this->transacoes) . " transação(ões)";
    }
}
