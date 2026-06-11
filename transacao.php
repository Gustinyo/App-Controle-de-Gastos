<?php

declare(strict_types=1);

namespace ControleGastos\Models;

use DateTimeImmutable;

final class Transacao
{
    public function __construct(
        private float $valor,
        private Categoria $categoria,
        private MetodoPagamento $metodoPagamento,
        private string $descricao,
        private DateTimeImmutable $data = new DateTimeImmutable()
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        if ($this->valor <= 0) {
            throw new \InvalidArgumentException(
                'O valor da transação deve ser maior que zero.'
            );
        }

        if (trim($this->descricao) === '') {
            throw new \InvalidArgumentException(
                'A descrição é obrigatória.'
            );
        }
    }

    public function getValor(): float
    {
        return $this->valor;
    }

    public function getCategoria(): Categoria
    {
        return $this->categoria;
    }

    public function getMetodoPagamento(): MetodoPagamento
    {
        return $this->metodoPagamento;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function getData(): DateTimeImmutable
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s | %s | R$ %.2f',
            $this->categoria->getNome(),
            $this->descricao,
            $this->valor
        );
    }
}
