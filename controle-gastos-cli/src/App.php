<?php

declare(strict_types=1);

namespace App;

use App\Models\{Usuario, Carteira, Categoria, Transacao, MetodoPagamento};
use App\Services\{NotificacaoEmail, NotificacaoSms};

class App
{
    private ?Usuario $usuarioAtivo = null;

    /** @var Usuario[] */
    private array $usuarios = [];

    /** @var MetodoPagamento[] */
    private array $metodosPagamento = [];

    /** @var Categoria[] */
    private array $categorias = [];

    public function __construct() {}

    public function executar(): void
    {
        $this->exibirBanner();
        $this->seedDados();

        while (true) {
            if ($this->usuarioAtivo === null) {
                $this->menuLogin();
            } else {
                $this->menuPrincipal();
            }
        }
    }

    private function exibirBanner(): void
    {
        echo PHP_EOL;
        echo "╔══════════════════════════════════════════╗" . PHP_EOL;
        echo "║       💰 CONTROLE DE GASTOS CLI          ║" . PHP_EOL;
        echo "║          Gerencie suas finanças          ║" . PHP_EOL;
        echo "╚══════════════════════════════════════════╝" . PHP_EOL;
        echo PHP_EOL;
    }

    private function seedDados(): void
    {
        // Métodos de pagamento globais (ASSOCIAÇÃO)
        $this->metodosPagamento[] = new MetodoPagamento('Dinheiro', 'dinheiro');
        $this->metodosPagamento[] = new MetodoPagamento('Cartão de Crédito', 'credito');
        $this->metodosPagamento[] = new MetodoPagamento('Cartão de Débito', 'debito');
        $this->metodosPagamento[] = new MetodoPagamento('Pix', 'pix');

        // Categorias globais (AGREGAÇÃO)
        $this->categorias[] = new Categoria('Alimentação', '#e74c3c');
        $this->categorias[] = new Categoria('Transporte', '#3498db');
        $this->categorias[] = new Categoria('Saúde', '#2ecc71');
        $this->categorias[] = new Categoria('Lazer', '#9b59b6');
        $this->categorias[] = new Categoria('Salário', '#f39c12');
    }

    private function menuLogin(): void
    {
        echo "┌─────────────────────────────┐" . PHP_EOL;
        echo "│           ACESSO            │" . PHP_EOL;
        echo "├─────────────────────────────┤" . PHP_EOL;
        echo "│ [1] Fazer Login             │" . PHP_EOL;
        echo "│ [2] Criar Conta             │" . PHP_EOL;
        echo "│ [0] Sair                    │" . PHP_EOL;
        echo "└─────────────────────────────┘" . PHP_EOL;
        echo "Escolha: ";

        $opcao = trim((string) fgets(STDIN));

        match ($opcao) {
            '1' => $this->login(),
            '2' => $this->criarConta(),
            '0' => $this->sair(),
            default => $this->erro("Opção inválida.")
        };
    }

    private function menuPrincipal(): void
    {
        $saldo = $this->usuarioAtivo->getSaldoTotal();
        $corSaldo = $saldo >= 0 ? '' : '';
        echo PHP_EOL;
        echo "┌─────────────────────────────────────────┐" . PHP_EOL;
        echo "│  Olá, " . str_pad($this->usuarioAtivo->getNome(), 35) . "│" . PHP_EOL;
        echo "│  Saldo Total: R$ " . str_pad(number_format($saldo, 2, ',', '.'), 24) . "│" . PHP_EOL;
        echo "├─────────────────────────────────────────┤" . PHP_EOL;
        echo "│ [1] Gerenciar Carteiras                 │" . PHP_EOL;
        echo "│ [2] Nova Transação                      │" . PHP_EOL;
        echo "│ [3] Ver Transações                      │" . PHP_EOL;
        echo "│ [4] Gerenciar Categorias                │" . PHP_EOL;
        echo "│ [5] Métodos de Pagamento                │" . PHP_EOL;
        echo "│ [6] Configurar Notificações             │" . PHP_EOL;
        echo "│ [7] Relatório Financeiro                │" . PHP_EOL;
        echo "│ [0] Logout                              │" . PHP_EOL;
        echo "└─────────────────────────────────────────┘" . PHP_EOL;
        echo "Escolha: ";

        $opcao = trim((string) fgets(STDIN));

        match ($opcao) {
            '1' => $this->menuCarteiras(),
            '2' => $this->novaTransacao(),
            '3' => $this->verTransacoes(),
            '4' => $this->menuCategorias(),
            '5' => $this->verMetodosPagamento(),
            '6' => $this->configurarNotificacoes(),
            '7' => $this->relatorioFinanceiro(),
            '0' => $this->logout(),
            default => $this->erro("Opção inválida.")
        };
    }

    // ── AUTH ───────────────────────────────────────────────────────────────

    private function login(): void
    {
        echo "Email: ";
        $email = trim((string) fgets(STDIN));
        echo "Senha: ";
        $senha = trim((string) fgets(STDIN));

        foreach ($this->usuarios as $usuario) {
            if ($usuario->getEmail() === $email) {
                $this->usuarioAtivo = $usuario;
                $this->sucesso("Bem-vindo, {$usuario->getNome()}!");
                $usuario->notificar("Login realizado", "Novo acesso à sua conta detectado.");
                return;
            }
        }
        $this->erro("Usuário não encontrado. Verifique o email.");
    }

    private function criarConta(): void
    {
        echo "Nome: ";
        $nome = trim((string) fgets(STDIN));
        echo "Email: ";
        $email = trim((string) fgets(STDIN));
        echo "Senha: ";
        $senha = trim((string) fgets(STDIN));

        if (empty($nome) || empty($email) || empty($senha)) {
            $this->erro("Todos os campos são obrigatórios.");
            return;
        }

        $usuario = new Usuario($nome, $email, $senha);

        // Associar todos os métodos de pagamento ao novo usuário (ASSOCIAÇÃO)
        foreach ($this->metodosPagamento as $metodo) {
            $usuario->associarMetodoPagamento($metodo);
        }

        // Criar carteira padrão (COMPOSIÇÃO)
        $usuario->criarCarteira('Carteira Principal', 'BRL', 0.0);

        $this->usuarios[] = $usuario;
        $this->usuarioAtivo = $usuario;
        $this->sucesso("Conta criada com sucesso! Bem-vindo, {$nome}!");
    }

    private function logout(): void
    {
        $this->sucesso("Até logo, {$this->usuarioAtivo->getNome()}!");
        $this->usuarioAtivo = null;
    }

    // ── CARTEIRAS ─────────────────────────────────────────────────────────

    private function menuCarteiras(): void
    {
        echo PHP_EOL . "=== CARTEIRAS ===" . PHP_EOL;
        $carteiras = $this->usuarioAtivo->getCarteiras();

        if (empty($carteiras)) {
            echo "Nenhuma carteira cadastrada." . PHP_EOL;
        } else {
            foreach ($carteiras as $carteira) {
                echo "  " . $carteira . PHP_EOL;
            }
        }

        echo PHP_EOL . "[1] Nova Carteira  [0] Voltar" . PHP_EOL . "Escolha: ";
        $opcao = trim((string) fgets(STDIN));

        if ($opcao === '1') {
            echo "Nome da carteira: ";
            $nome = trim((string) fgets(STDIN));
            echo "Saldo inicial (ex: 1500.00): ";
            $saldo = (float) trim((string) fgets(STDIN));
            $this->usuarioAtivo->criarCarteira($nome, 'BRL', $saldo);
            $this->sucesso("Carteira '{$nome}' criada com saldo inicial de R$ " . number_format($saldo, 2, ',', '.'));
        }
    }

    // ── TRANSAÇÕES ────────────────────────────────────────────────────────

    private function novaTransacao(): void
    {
        echo PHP_EOL . "=== NOVA TRANSAÇÃO ===" . PHP_EOL;

        $carteiras = $this->usuarioAtivo->getCarteiras();
        if (empty($carteiras)) {
            $this->erro("Você precisa ter ao menos uma carteira.");
            return;
        }

        echo "Carteiras disponíveis:" . PHP_EOL;
        foreach ($carteiras as $c) {
            echo "  " . $c . PHP_EOL;
        }
        echo "ID da carteira: ";
        $idCarteira = (int) trim((string) fgets(STDIN));
        $carteira = $this->usuarioAtivo->getCarteiraPorId($idCarteira);

        if ($carteira === null) {
            $this->erro("Carteira não encontrada.");
            return;
        }

        echo "Tipo [1-Receita / 2-Despesa]: ";
        $tipoOpcao = trim((string) fgets(STDIN));
        $tipo = $tipoOpcao === '1' ? 'receita' : 'despesa';

        echo "Descrição: ";
        $descricao = trim((string) fgets(STDIN));

        echo "Valor (ex: 250.00): ";
        $valor = (float) trim((string) fgets(STDIN));

        echo "Métodos de pagamento:" . PHP_EOL;
        foreach ($this->usuarioAtivo->getMetodosPagamento() as $m) {
            echo "  " . $m . PHP_EOL;
        }
        echo "ID do método de pagamento: ";
        $idMetodo = (int) trim((string) fgets(STDIN));
        $metodo = $this->usuarioAtivo->getMetodoPagamentoPorId($idMetodo);

        if ($metodo === null) {
            $this->erro("Método de pagamento não encontrado.");
            return;
        }

        // Vincular à categoria (AGREGAÇÃO)
        echo "Categorias disponíveis:" . PHP_EOL;
        foreach ($this->categorias as $cat) {
            echo "  " . $cat . PHP_EOL;
        }
        echo "ID da categoria (0 para ignorar): ";
        $idCategoria = (int) trim((string) fgets(STDIN));

        try {
            $transacao = $carteira->adicionarTransacao($descricao, $valor, $tipo, $metodo);

            // AGREGAÇÃO: transação é associada à categoria
            foreach ($this->categorias as $cat) {
                if ($cat->getId() === $idCategoria) {
                    $cat->adicionarTransacao($transacao);
                    break;
                }
            }

            $this->sucesso("Transação registrada: " . $transacao);
            $sinal = $tipo === 'receita' ? '+' : '-';
            $this->usuarioAtivo->notificar(
                "Nova transação",
                "Transação registrada: {$descricao} ({$sinal}R$ " . number_format($valor, 2, ',', '.') . ")"
            );
        } catch (\InvalidArgumentException $e) {
            $this->erro($e->getMessage());
        }
    }

    private function verTransacoes(): void
    {
        echo PHP_EOL . "=== TRANSAÇÕES ===" . PHP_EOL;
        $carteiras = $this->usuarioAtivo->getCarteiras();
        $total = 0;

        foreach ($carteiras as $carteira) {
            echo PHP_EOL . "📁 Carteira: {$carteira->getNome()}" . PHP_EOL;
            $transacoes = $carteira->getTransacoes();
            if (empty($transacoes)) {
                echo "   Sem transações." . PHP_EOL;
            } else {
                foreach ($transacoes as $t) {
                    $sinal = $t->getTipo() === 'receita' ? '✅' : '❌';
                    echo "   {$sinal} " . $t . PHP_EOL;
                    $total++;
                }
            }
        }

        echo PHP_EOL . "Total de transações: {$total}" . PHP_EOL;
        echo "Pressione Enter para continuar...";
        fgets(STDIN);
    }

    // ── CATEGORIAS ────────────────────────────────────────────────────────

    private function menuCategorias(): void
    {
        echo PHP_EOL . "=== CATEGORIAS (AGREGAÇÃO com Transações) ===" . PHP_EOL;
        foreach ($this->categorias as $cat) {
            echo "  {$cat}" . PHP_EOL;
            echo "    Receitas:  R$ " . number_format($cat->getTotalReceitas(), 2, ',', '.') . PHP_EOL;
            echo "    Despesas:  R$ " . number_format($cat->getTotalDespesas(), 2, ',', '.') . PHP_EOL;
            echo "    Saldo:     R$ " . number_format($cat->getSaldo(), 2, ',', '.') . PHP_EOL;
        }
        echo PHP_EOL . "[1] Nova Categoria  [0] Voltar" . PHP_EOL . "Escolha: ";
        $opcao = trim((string) fgets(STDIN));

        if ($opcao === '1') {
            echo "Nome da categoria: ";
            $nome = trim((string) fgets(STDIN));
            echo "Cor (ex: #e74c3c): ";
            $cor = trim((string) fgets(STDIN)) ?: '#3498db';
            $this->categorias[] = new Categoria($nome, $cor);
            $this->sucesso("Categoria '{$nome}' criada.");
        }
    }

    // ── MÉTODOS DE PAGAMENTO ──────────────────────────────────────────────

    private function verMetodosPagamento(): void
    {
        echo PHP_EOL . "=== MÉTODOS DE PAGAMENTO (ASSOCIAÇÃO com Usuário) ===" . PHP_EOL;
        foreach ($this->usuarioAtivo->getMetodosPagamento() as $m) {
            echo "  " . $m . PHP_EOL;
        }
        echo PHP_EOL . "Pressione Enter para continuar...";
        fgets(STDIN);
    }

    // ── NOTIFICAÇÕES (POLIMORFISMO) ────────────────────────────────────────

    private function configurarNotificacoes(): void
    {
        echo PHP_EOL . "=== CONFIGURAR NOTIFICAÇÕES ===" . PHP_EOL;
        echo "[1] Email  [2] SMS  [0] Voltar" . PHP_EOL . "Escolha: ";
        $opcao = trim((string) fgets(STDIN));

        // POLIMORFISMO: Email e SMS implementam a mesma interface
        $notificador = match ($opcao) {
            '1' => new NotificacaoEmail(),
            '2' => new NotificacaoSms(),
            default => null
        };

        if ($notificador !== null) {
            $this->usuarioAtivo->setNotificador($notificador);
            $this->sucesso("Notificações via {$notificador->getTipoNotificacao()} ativadas.");
            $this->usuarioAtivo->notificar(
                "Notificações ativadas",
                "Você ativou notificações via {$notificador->getTipoNotificacao()}."
            );
        }
    }

    // ── RELATÓRIO ────────────────────────────────────────────────────────

    private function relatorioFinanceiro(): void
    {
        echo PHP_EOL;
        echo "╔══════════════════════════════════════════╗" . PHP_EOL;
        echo "║          RELATÓRIO FINANCEIRO            ║" . PHP_EOL;
        echo "╠══════════════════════════════════════════╣" . PHP_EOL;

        $totalReceitas = 0.0;
        $totalDespesas = 0.0;

        foreach ($this->usuarioAtivo->getCarteiras() as $carteira) {
            foreach ($carteira->getTransacoes() as $t) {
                if ($t->getTipo() === 'receita') $totalReceitas += $t->getValor();
                else $totalDespesas += $t->getValor();
            }
        }

        $saldo = $totalReceitas - $totalDespesas;

        echo "║  Total de Receitas: R$ " . str_pad(number_format($totalReceitas, 2, ',', '.'), 18) . "║" . PHP_EOL;
        echo "║  Total de Despesas: R$ " . str_pad(number_format($totalDespesas, 2, ',', '.'), 18) . "║" . PHP_EOL;
        echo "╠══════════════════════════════════════════╣" . PHP_EOL;
        echo "║  Saldo Líquido:     R$ " . str_pad(number_format($saldo, 2, ',', '.'), 18) . "║" . PHP_EOL;
        echo "╠══════════════════════════════════════════╣" . PHP_EOL;
        echo "║  CARTEIRAS:                              ║" . PHP_EOL;
        foreach ($this->usuarioAtivo->getCarteiras() as $c) {
            $line = "  {$c->getNome()}: R$ " . number_format($c->getSaldoAtual(), 2, ',', '.');
            echo "║" . str_pad($line, 42) . "║" . PHP_EOL;
        }
        echo "╚══════════════════════════════════════════╝" . PHP_EOL;
        echo PHP_EOL . "Pressione Enter para continuar...";
        fgets(STDIN);
    }

    // ── HELPERS ───────────────────────────────────────────────────────────

    private function sucesso(string $msg): void
    {
        echo PHP_EOL . "✅ {$msg}" . PHP_EOL . PHP_EOL;
    }

    private function erro(string $msg): void
    {
        echo PHP_EOL . "❌ Erro: {$msg}" . PHP_EOL . PHP_EOL;
    }

    private function sair(): void
    {
        echo PHP_EOL . "Até logo! 👋" . PHP_EOL;
        exit(0);
    }
}
