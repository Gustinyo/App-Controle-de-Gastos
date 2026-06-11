<?php

declare(strict_types=1);

namespace ControleGastos\Models;

final class MetodoPagamento
{
    public function __construct(
        private string $nome
    ) {}

    public function getNome(): string
    {
        return $this->nome;
    }

    public static function pix(): self
    {
        return new self('PIX');
    }

    public static function dinheiro(): self
    {
        return new self('Dinheiro');
    }

    public static function cartaoCredito(): self
    {
        return new self('Cartão de Crédito');
    }

    public static function cartaoDebito(): self
    {
        return new self('Cartão de Débito');
    }
}
