# tui

Interface de texto (TUI — *Text User Interface*) para terminal/console.

Biblioteca leve do **PHP Tool Kit** para interagir com o usuário no terminal:
leitura de entradas (textos, números, datas, opções e múltipla escolha) e
escrita de saídas formatadas (linhas horizontais, rótulos e tabelas).

## Requisitos

- PHP **8.5** ou superior
- Extensão `mbstring`
- Comando `tput` disponível no `PATH` (Linux/Unix) ou utilitário `mode`
  (Windows), usado para detectar as dimensões da janela do terminal

## Instalação

```bash
composer require php-tool-kit/tui
```

## Início rápido

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ptk\Tui\Tui;

$tui = new Tui();

$nome = $tui->input->text('Digite seu nome: ');
$idade = $tui->input->number('Digite sua idade: ');

$tui->output
    ->hline('=')
    ->label('Olá, ', $nome)
    ->label('Idade: ', $idade)
    ->hline('=');
```

## API

### `Ptk\Tui\Tui`

Ponto de entrada da biblioteca. Cria e expõe os serviços de entrada e saída.

| Membro | Descrição |
| --- | --- |
| `Input $input` | Instância responsável pela leitura dos dados digitados pelo usuário. |
| `Output $output` | Instância responsável pela escrita dos dados no terminal. |
| `$stdin` / `$stdout` / `$stderr` | Fluxos padrão do processo (`resource`). |
| `getScreenDimension(): stdClass` | Retorna um objeto com as propriedades `lines` (linhas) e `cols` (colunas) da janela do terminal. A detecção é feita por `mode CON` no Windows e por `tput lines`/`tput cols` nos demais sistemas. |

### `Ptk\Tui\Output`

Todos os métodos retornam a própria instância (`self`), permitindo encadear
as chamadas.

| Método | Descrição |
| --- | --- |
| `write(string $data): self` | Escreve os dados no fluxo de saída padrão. |
| `nl(int $repeat = 1): self` | Escreve uma ou mais quebras de linha. |
| `hline(string $char = '-'): self` | Desenha uma linha horizontal ocupando toda a largura do terminal. |
| `label(string $right, string\|float\|int $left, string $char = '.'): self` | Exibe um texto no início da linha e um valor no fim dela, preenchendo o espaço intermediário com o caractere indicado. |
| `table(array $data, ?array $headers = null): self` | Renderiza uma tabela. As colunas são dimensionadas pelo conteúdo mais largo de cada uma. `$data` deve ser uma lista de arrays associativos (`coluna => valor`) com as mesmas chaves; `$headers` é a lista de rótulos, na mesma ordem das chaves (por padrão, as próprias chaves são usadas). Lança `OverflowException` quando a tabela é mais larga que o terminal. |

### `Ptk\Tui\Input`

Cada método exibe o rótulo informado, aguarda a digitação de uma linha e
converte a entrada para o tipo retornado.

| Método | Descrição |
| --- | --- |
| `text(string $label): string` | Lê um texto livre, removendo os espaços das extremidades. |
| `number(string $label, ?int $decimals = PHP_INT_MAX, string $decimalSeparator = ',', string $thousandSeparator = '.'): float` | Lê um valor numérico. Remove o separador de milhar, converte o separador decimal para `.` e aplica o arredondamento informado. |
| `datetime(string $label, ?string $format = null): \DateTimeImmutable` | Lê uma data/hora. Com `$format`, usa `date_create_immutable_from_format()`; sem `$format`, o analisador de datas nativo do PHP é utilizado. |
| `choice(array $options): string` | Lista as opções e repete a pergunta até que a entrada corresponda a uma das chaves. Retorna a chave escolhida. |
| `select(array $options, array $defaults = []): array` | Lista as opções com marcação `[X]`, alternando a seleção a cada entrada válida. A linha vazia encerra a operação, retornando as chaves marcadas. |
| `memo(): array` | Lê várias linhas de texto. A linha vazia encerra a leitura, retornando as linhas digitadas. |

## Exemplos

### Tabela

```php
$tui->output->table(
    [
        ['nome' => 'Ana',   'cidade' => 'Porto Alegre'],
        ['nome' => 'Bruno', 'cidade' => 'Curitiba'],
    ],
    ['Nome', 'Cidade']
);
```

### Opção única

```php
$opcao = $tui->input->choice([
    '1' => 'Novo documento',
    '2' => 'Abrir documento',
    '3' => 'Sair',
]);
```

### Múltipla seleção

```php
$selecionados = $tui->input->select(
    ['leite' => 'Leite', 'acucar' => 'Açúcar', 'cafe' => 'Café'],
    ['leite']
);
```

### Linhas de texto

```php
$linhas = $tui->input->memo();
```

### Data com formato específico

```php
$nascimento = $tui->input->datetime('Data de nascimento (dd/mm/aaaa): ', 'd/m/Y');
```

## Qualidade do código

```bash
composer static   # PHPStan (nível 10)
composer test     # PHPUnit, com relatório de cobertura
composer code     # PHP_CodeSniffer (PSR-1/PSR-2/PSR-12) e correções automáticas
```

A API pública é documentada com blocos PHPDoc em português do Brasil,
compatíveis com o nível 10 do PHPStan.

## Licença

MIT — veja o arquivo [LICENSE](LICENSE).
