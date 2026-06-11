<?php

declare(strict_types=1);

namespace ControleGastos\Models;

final class Categoria
{
    public function __construct(
        private string $nome,
        private ?string $descricao = null
    ) {
        $this->validar();
    }

    private function validar(): void
    {
        if (trim($this->nome) === '') {
            throw new \InvalidArgumentException(
                'O nome da categoria é obrigatório.'
            );
        }
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function alterarNome(string $nome): void
    {
        if (trim($nome) === '') {
            throw new \InvalidArgumentException(
                'O nome da categoria é obrigatório.'
            );
        }

        $this->nome = $nome;
    }

    public function alterarDescricao(?string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function __toString(): string
    {
        return $this->nome;
    }
}
