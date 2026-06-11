<?php

declare(strict_types=1);

namespace ControleGastos\Models;

use ControleGastos\Interfaces\NotificavelInterface;

class Usuario
{
    private static int $contadorId = 0;

    private readonly int $id;
    private readonly \DateTime $criadoEm;

    private array $carteiras = [];
    private array $metodosPagamento = [];
    private array $canaisNotificacao = [];

    public function __construct(
        private string $nome,
        private string $email,
        private string $senha,
        private bool $ativo = true,
    ) {
        $this->validarEmail($email);
        $this->validarSenha($senha);

        self::$contadorId++;
        $this->id = self::$contadorId;
        $this->criadoEm = new \DateTime();

        $this->criarCarteira('Carteira Principal', 0.0);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function getCriadoEm(): \DateTime
    {
        return $this->criadoEm;
    }

    public function atualizarNome(string $novoNome): void
    {
        if (trim($novoNome) === '') {
            throw new \InvalidArgumentException('O nome não pode ser vazio.');
        }
        $this->nome = $novoNome;
    }

    public function atualizarEmail(string $novoEmail): void
    {
        $this->validarEmail($novoEmail);
        $this->email = $novoEmail;
    }

    public function alterarSenha(string $senhaAtual, string $novaSenha): void
    {
        if (!password_verify($senhaAtual, $this->senha)) {
            throw new \RuntimeException('Senha atual incorreta.');
        }

        $this->validarSenha($novaSenha);
        $this->senha = password_hash($novaSenha, PASSWORD_BCRYPT);
    }

    public function verificarSenha(string $senha): bool
    {
        return password_verify($senha, $this->senha);
    }

    public function desativar(): void
    {
        $this->ativo = false;
    }

    public function ativar(): void
    {
        $this->ativo = true;
    }

    public function criarCarteira(
        string $nome,
        float $saldoInicial = 0.0,
        string $moeda = 'BRL'
    ): Carteira {
        $carteira = new Carteira($nome, $saldoInicial, $moeda);
        $this->carteiras[$carteira->getId()] = $carteira;

        return $carteira;
    }

    public function removerCarteira(int $carteiraId): void
    {
        if (!isset($this->carteiras[$carteiraId])) {
            throw new \RuntimeException("Carteira #{$carteiraId} não encontrada.");
        }

        unset($this->carteiras[$carteiraId]);
    }

    public function getCarteiras(): array
    {
        return array_values($this->carteiras);
    }

    public function getCarteiraPorId(int $id): Carteira
    {
        if (!isset($this->carteiras[$id])) {
            throw new \RuntimeException("Carteira #{$id} não encontrada.");
        }

        return $this->carteiras[$id];
    }

    public function getCarteiraPrincipal(): Carteira
    {
        if (empty($this->carteiras)) {
            throw new \RuntimeException('Nenhuma carteira encontrada.');
        }

        return array_values($this->carteiras)[0];
    }

    public function associarMetodoPagamento(MetodoPagamento $metodo): void
    {
        $this->metodosPagamento[$metodo->getId()] = $metodo;
    }

    public function desassociarMetodoPagamento(int $metodoId): void
    {
        unset($this->metodosPagamento[$metodoId]);
    }

    public function getMetodosPagamento(): array
    {
        return array_values($this->metodosPagamento);
    }

    public function getMetodoPagamentoPorId(int $id): MetodoPagamento
    {
        if (!isset($this->metodosPagamento[$id])) {
            throw new \RuntimeException(
                "Método de pagamento #{$id} não associado a este usuario."
            );
        }

        return $this->metodosPagamento[$id];
    }

    public function registrarTransacao(
        int $carteiraId,
        string $descricao,
        float $valor,
        string $tipo,
        int $metodoPagamentoId,
        ?Categoria $categoria = null,
    ): Transacao {
        $this->garantirAtivo();

        $carteira = $this->getCarteiraPorId($carteiraId);
        $metodo = $this->getMetodoPagamentoPorId($metodoPagamentoId);

        $transacao = new Transacao(
            $descricao,
            $valor,
            $tipo,
            $metodo,
            $categoria
        );

        $carteira->registrarTransacao($transacao);

        $categoria?->adicionarTransacao($transacao);

        if ($transacao->isSaida() && $valor >= 100.0) {
            $this->notificar(
                sprintf(
                    "Nova saída registrada: %s — R$ %.2f",
                    $descricao,
                    $valor
                )
            );
        }

        return $transacao;
    }

    public function adicionarCanalNotificacao(
        NotificavelInterface $canal
    ): void {
        $this->canaisNotificacao[] = $canal;
    }

    public function notificar(string $mensagem): void
    {
        foreach ($this->canaisNotificacao as $canal) {
            $canal->enviarNotificacao($mensagem);
        }
    }

    public function getCanaisNotificacao(): array
    {
        return $this->canaisNotificacao;
    }

    public function getSaldoTotal(): float
    {
        return array_sum(
            array_map(
                fn(Carteira $c) => $c->getSaldoAtual(),
                $this->carteiras
            )
        );
    }

    public function getResumo(): string
    {
        $carteirasCount = count($this->carteiras);
        $metodosCount = count($this->metodosPagamento);
        $canaisCount = count($this->canaisNotificacao);
        $status = $this->ativo ? 'Ativo' : 'Inativo';

        return implode(PHP_EOL, [
            "========================================",
            "  USUARIO: {$this->nome}",
            "  E-mail : {$this->email}",
            "  Status : {$status}",
            "  Desde  : {$this->criadoEm->format('d/m/Y')}",
            "----------------------------------------",
            "  Carteiras       : {$carteirasCount}",
            "  Métodos pagto.  : {$metodosCount}",
            "  Canais notif.   : {$canaisCount}",
            sprintf("  Saldo total     : R$ %.2f", $this->getSaldoTotal()),
            "========================================",
        ]);
    }

    public function __toString(): string
    {
        return "[{$this->id}] {$this->nome} <{$this->email}>";
    }

    private function validarEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException(
                "E-mail inválido: '{$email}'."
            );
        }
    }

    private function validarSenha(string $senha): void
    {
        if (strlen($senha) < 8) {
            throw new \InvalidArgumentException(
                'A senha deve ter no mínimo 8 caracteres.'
            );
        }
    }

    private function garantirAtivo(): void
    {
        if (!$this->ativo) {
            throw new \RuntimeException(
                'Operação negada: conta de usuario inativa.'
            );
        }
    }
}