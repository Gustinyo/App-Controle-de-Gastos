<?php

declare(strict_types=1);

namespace App\Models;

use App\Interfaces\NotificavelInterface;


class Usuario
{
    private static int $contador = 0;
    private int $id;

    /** @var Carteira[] */
    private array $carteiras = [];

    /** @var MetodoPagamento[] */
    private array $metodosPagamento = [];

    private ?NotificavelInterface $notificador = null;

    public function __construct(
        private string $nome,
        private string $email,
        private string $senha
    ) {
        self::$contador++;
        $this->id = self::$contador;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }

    public function criarCarteira(string $nome, string $moeda = 'BRL', float $saldoInicial = 0.0): Carteira
    {
        $carteira = new Carteira($nome, $moeda, $saldoInicial);
        $this->carteiras[] = $carteira;
        return $carteira;
    }

    /** @return Carteira[] */
    public function getCarteiras(): array
    {
        return $this->carteiras;
    }

    public function getCarteiraPorId(int $id): ?Carteira
    {
        foreach ($this->carteiras as $carteira) {
            if ($carteira->getId() === $id) return $carteira;
        }
        return null;
    }

    // ASSOCIAÇÃO: MetodoPagamento existe independentemente do Usuário
    public function associarMetodoPagamento(MetodoPagamento $metodo): void
    {
        $this->metodosPagamento[] = $metodo;
    }

    /** @return MetodoPagamento[] */
    public function getMetodosPagamento(): array
    {
        return $this->metodosPagamento;
    }

    public function getMetodoPagamentoPorId(int $id): ?MetodoPagamento
    {
        foreach ($this->metodosPagamento as $metodo) {
            if ($metodo->getId() === $id) return $metodo;
        }
        return null;
    }

    public function setNotificador(NotificavelInterface $notificador): void
    {
        $this->notificador = $notificador;
    }

    public function notificar(string $assunto, string $mensagem): void
    {
        if ($this->notificador !== null) {
            $this->notificador->enviarNotificacao($this->email, $assunto, $mensagem);
        }
    }

    public function getSaldoTotal(): float
    {
        return array_sum(array_map(
            fn(Carteira $c) => $c->getSaldoAtual(),
            $this->carteiras
        ));
    }

    public function __toString(): string
    {
        return "[{$this->id}] {$this->nome} <{$this->email}>";
    }
}
