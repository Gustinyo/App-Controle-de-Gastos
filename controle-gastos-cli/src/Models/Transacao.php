<?php

declare(strict_types=1);

namespace App\Models;

class Transacao
{
    private static int $contador = 0;
    private int $id;
    private \DateTime $criadoEm;

    public function __construct(
        private string $descricao,
        private float $valor,
        private string $tipo,
        private MetodoPagamento $metodoPagamento
    ) {
        if (!in_array($tipo, ['receita', 'despesa'])) {
            throw new \InvalidArgumentException("Tipo deve ser 'receita' ou 'despesa'.");
        }
        self::$contador++;
        $this->id = self::$contador;
        $this->criadoEm = new \DateTime();
    }

    public function getId(): int { return $this->id; }
    public function getDescricao(): string { return $this->descricao; }
    public function getValor(): float { return $this->valor; }
    public function getTipo(): string { return $this->tipo; }
    public function getMetodoPagamento(): MetodoPagamento { return $this->metodoPagamento; }
    public function getCriadoEm(): \DateTime { return $this->criadoEm; }

    public function __toString(): string
    {
        $sinal = $this->tipo === 'receita' ? '+' : '-';
        return sprintf(
            "[%d] %s | %sR$ %.2f | %s | %s",
            $this->id,
            $this->descricao,
            $sinal,
            $this->valor,
            $this->metodoPagamento->getNome(),
            $this->criadoEm->format('d/m/Y H:i')
        );
    }
}
