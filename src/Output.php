<?php

namespace Ptk\Tui;

/**
 * Responsável pela escrita de dados no terminal.
 *
 * Todos os métodos de escrita retornam a própria instância, o que permite
 * encadear as chamadas.
 */
class Output
{
    /** Instância principal da biblioteca, usada para acessar os fluxos de saída. */
    protected readonly Tui $tui;

    /**
     * Vincula este objeto de saída à instância principal da biblioteca.
     *
     * @param Tui $tui Instância que possui os fluxos de saída do terminal.
     */
    public function __construct(Tui $tui)
    {
        $this->tui = $tui;
    }

    /**
     * Escreve os dados informados no fluxo de saída padrão.
     *
     * @param string $data Dados a serem gravados no terminal.
     *
     * @return self Instância atual, permitindo encadeamento de chamadas.
     */
    public function write(string $data): self
    {
        fwrite($this->tui->stdout, $data);
        return $this;
    }

    /**
     * Escreve uma ou mais quebras de linha.
     *
     * @param int $repeat Quantidade de quebras de linha a serem escritas. Padrão: 1.
     *
     * @return self Instância atual, permitindo encadeamento de chamadas.
     */
    public function nl(int $repeat = 1): self
    {
        echo str_repeat(PHP_EOL, $repeat);
        return $this;
    }

    /**
     * Desenha uma linha horizontal ocupando toda a largura do terminal.
     *
     * @param string $char Caractere utilizado para preencher a linha. Padrão: "-".
     *
     * @return self Instância atual, permitindo encadeamento de chamadas.
     */
    public function hline(string $char = '-'): self
    {
        $max = $this->tui->getScreenDimension()->cols;
        echo mb_str_pad('', $max, $char), PHP_EOL;
        return $this;
    }

    /**
     * Exibe um rótulo preenchendo o espaço restante da linha com um caractere.
     *
     * O primeiro texto é impresso no início da linha e o valor, no fim dela,
     * separados pelo caractere de preenchimento.
     *
     * @param string           $right Texto exibido no início da linha.
     * @param string|float|int $left  Valor exibido no fim da linha.
     * @param string           $char  Caractere de preenchimento. Padrão: ".".
     *
     * @return self Instância atual, permitindo encadeamento de chamadas.
     */
    public function label(string $right, string|float|int $left, string $char = '.'): self
    {
        $max = $this->tui->getScreenDimension()->lines;
        $size = mb_strlen($right.$left);
        echo $right . mb_str_pad('', $max-$size, $char) . $left, PHP_EOL;
        return $this;
    }

    /**
     * Renderiza uma tabela no terminal.
     *
     * A largura de cada coluna é calculada a partir do conteúdo mais largo que
     * ela apresenta. O cabeçalho é impresso seguido de uma linha separadora,
     * depois as linhas de dados e, ao final, outra linha separadora.
     *
     * @param array<array-key, array<string, string>> $data    Linhas da tabela, onde cada linha é um
     *                                                        array associativo coluna => valor. Os nomes
     *                                                        das colunas devem ser iguais em todas as linhas.
     * @param list<string>|null                       $headers Rótulos das colunas, na mesma ordem das
     *                                                        chaves de cada linha. Quando omitido, as
     *                                                        próprias chaves são usadas como rótulo.
     *
     * @return self Instância atual, permitindo encadeamento de chamadas.
     *
     * @throws \OverflowException Quando a largura total da tabela excede a largura disponível no terminal.
     */
    public function table(array $data, ?array $headers = null): self
    {
        $max = $this->tui->getScreenDimension()->lines;

        $hkeys = array_keys($data[array_key_first($data)]);
        if(is_null($headers)) $headers = array_combine($hkeys, $hkeys);
        $headers = array_combine($hkeys, $headers);
        // print_r($headers);exit;

        $maxColLens = [];
        foreach([$headers, ...$data] as $row){
            foreach($row as $key => $value){
                $maxColLens[$key] = (($maxColLens[$key] ?? 0) < mb_strlen($value))? mb_strlen($value) : $maxColLens[$key];
            }
        }
        // print_r($maxColLens);exit;

        $maxLen = array_sum($maxColLens)-1+(count($headers));

        if($maxLen > ($max-1-(count($headers)))) throw new \OverflowException('The column width is greater than the screen width.');

        $hbuffer = '';
        foreach($headers as $col => $label){
            $hbuffer .= mb_str_pad($label, $maxColLens[$col]+1);
        }
        $this->write($hbuffer)->nl()->write(str_repeat('-', $maxLen))->nl();

        foreach($data as $row){
            $dbuffer = '';
            foreach($row as $col => $cel){
                $dbuffer .= mb_str_pad($cel, $maxColLens[$col]+1);
            }
            $this->write($dbuffer)->nl();
        }
        $this->write(str_repeat('-', $maxLen))->nl();

        return $this;
    }
}