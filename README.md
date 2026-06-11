# App-Controle-de-Gastos
Sistema de controle financeiro pessoal via linha de comando, desenvolvido em PHP com orientação a objetos, utilizando os princípios de SOLID, interfaces, polimorfismo e os três tipos de relacionamento entre classes.

---

##  Análise das Funcionalidades

### 1. Gerenciamento de Usuários
O sistema permite criar contas e realizar login. Cada `Usuario` possui nome, email e senha, além de gerenciar suas próprias carteiras e métodos de pagamento.

### 2. Carteiras
O usuário pode criar múltiplas carteiras (ex: Carteira Principal, Poupança, Conta Corrente). Cada carteira possui saldo inicial e acumula transações, calculando o saldo atual automaticamente.

### 3. Transações
Cada transação pode ser do tipo **receita** (entrada) ou **despesa** (saída). Toda transação está associada a um método de pagamento e pode ser vinculada a uma categoria.

### 4. Categorias
Organizam as transações em grupos (Alimentação, Transporte, Saúde, etc.). O relatório por categoria permite entender os padrões de gastos.

### 5. Métodos de Pagamento
Recursos compartilhados do sistema (Dinheiro, Cartão de Crédito, Débito, Pix) que são associados aos usuários.

### 6. Notificações
O usuário pode escolher receber alertas via **Email** ou **SMS** ao registrar transações e ao fazer login.

### 7. Relatório Financeiro
Visão consolidada de receitas totais, despesas totais e saldo líquido, com detalhamento por carteira.

---

##  Diagrama de Relacionamentos

```
Usuario
  ├── COMPOSIÇÃO  ──▶ Carteira[]
  │                      └── COMPOSIÇÃO ──▶ Transacao[]
  │                                              └── ASSOCIAÇÃO ──▶ MetodoPagamento
  │
  ├── ASSOCIAÇÃO  ──▶ MetodoPagamento[]
  └── usa          ──▶ NotificavelInterface
                              ├── NotificacaoEmail (implementa)
                              └── NotificacaoSms   (implementa)

Categoria
  └── AGREGAÇÃO ──▶ Transacao[]
```

---

##  Tipos de Relacionamento

###  Composição (`Usuario` → `Carteira` → `Transacao`)
> *"O todo não existe sem as partes, e as partes não existem sem o todo."*

- **`Usuario` compõe `Carteira`**: as carteiras são criadas diretamente pelo `Usuario` (`criarCarteira()`). Se o usuário for removido, suas carteiras deixam de existir — elas não têm sentido fora do contexto do usuário.
- **`Carteira` compõe `Transacao`**: as transações são instanciadas dentro de `Carteira::adicionarTransacao()`. Elas pertencem exclusivamente à carteira e não existem de forma independente no sistema.

```php
// Em Usuario.php — COMPOSIÇÃO
public function criarCarteira(string $nome, ...): Carteira
{
    $carteira = new Carteira($nome, $moeda, $saldoInicial); // criada aqui
    $this->carteiras[] = $carteira;
    return $carteira;
}
```

---

###  Agregação (`Categoria` → `Transacao`)
> *"O todo referencia as partes, mas as partes existem independentemente."*

- **`Categoria` agrega `Transacao`**: uma transação é criada pela carteira e depois pode ser associada a uma categoria. A transação existe antes de pertencer à categoria e continuaria existindo mesmo se a categoria fosse removida.

```php
// Em Categoria.php — AGREGAÇÃO
public function adicionarTransacao(Transacao $transacao): void
{
    $this->transacoes[] = $transacao; // recebe referência externa
}
```

---

###  Associação (`Usuario` → `MetodoPagamento`)
> *"Um objeto conhece o outro, mas ambos existem de forma independente."*

- **`Usuario` associa `MetodoPagamento`**: os métodos de pagamento (Pix, Cartão, etc.) são criados globalmente pelo sistema e depois associados ao usuário. Um `MetodoPagamento` existe independentemente de qualquer usuário.

```php
// Em Usuario.php — ASSOCIAÇÃO
public function associarMetodoPagamento(MetodoPagamento $metodo): void
{
    $this->metodosPagamento[] = $metodo; // referência externa, não instanciado aqui
}
```

---

##  Interface e Polimorfismo

### `NotificavelInterface`
Define o contrato que qualquer serviço de notificação deve cumprir:

```php
interface NotificavelInterface
{
    public function enviarNotificacao(string $destinatario, string $assunto, string $mensagem): bool;
    public function getTipoNotificacao(): string;
}
```

### Polimorfismo com `NotificacaoEmail` e `NotificacaoSms`
Ambas as classes implementam `NotificavelInterface`, mas com comportamentos diferentes. O `Usuario` não sabe (nem precisa saber) qual implementação está sendo usada:

```php
// Em App.php — POLIMORFISMO
$notificador = match ($opcao) {
    '1' => new NotificacaoEmail(), // implementa NotificavelInterface
    '2' => new NotificacaoSms(),   // implementa NotificavelInterface
};

$this->usuarioAtivo->setNotificador($notificador);
// Chama enviarNotificacao() sem saber se é Email ou SMS
$this->usuarioAtivo->notificar("Assunto", "Mensagem");
```

---

##  Requisitos Técnicos Utilizados

| Requisito | Onde aplicado |
|-----------|---------------|
| `declare(strict_types=1)` | Em todos os arquivos `.php` |
| Atributos privados | Todos os modelos usam `private` |
| Promotor de propriedades | Todos os construtores usam property promotion |
| Tipagem adequada | Tipos em todos os parâmetros e retornos |
| Relacionamento de Associação | `Usuario` ↔ `MetodoPagamento` |
| Relacionamento de Agregação | `Categoria` ↔ `Transacao` |
| Relacionamento de Composição | `Usuario` → `Carteira` → `Transacao` |
| Interface | `NotificavelInterface` |
| Polimorfismo | `NotificacaoEmail` e `NotificacaoSms` |

---

##  Como Executar

### Pré-requisitos
- PHP 8.1 ou superior
- Composer

### Instalação

```bash
git clone https://github.com/seu-usuario/controle-gastos-cli.git
cd controle-gastos-cli
composer install
```

### Execução

```bash
php app.php
# ou
composer start
```

---

##  Estrutura do Projeto

```
controle-gastos-cli/
├── src/
│   ├── Interfaces/
│   │   └── NotificavelInterface.php
│   ├── Services/
│   │   ├── NotificacaoEmail.php
│   │   └── NotificacaoSms.php
│   ├── Models/
│   │   ├── Usuario.php         (Composição com Carteira / Associação com MetodoPagamento)
│   │   ├── Carteira.php        (Composição com Transacao)
│   │   ├── Categoria.php       (Agregação com Transacao)
│   │   ├── Transacao.php
│   │   └── MetodoPagamento.php
│   └── App.php                 (Gerenciador da CLI)
├── app.php                     (Script principal de execução)
├── README.md                   (Documentação)
└── composer.json               (Autoload do PHP)
```

---

## Fluxo de Uso

1. **Inicie** o sistema com `php app.php`
2. **Crie uma conta** ou faça login
3. **Crie uma carteira** (ex: Carteira Principal com R$ 1.000,00 de saldo)
4. **Configure notificações** (Email ou SMS)
5. **Registre transações** (receitas e despesas com método de pagamento e categoria)
6. **Visualize o relatório** financeiro consolidado

---

## Autor

Desenvolvido como projeto acadêmico para demonstração de conceitos de Orientação a Objetos em PHP.

