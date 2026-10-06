<?php

namespace Ptk\Tui;

/**
 * Responsável pela leitura dos dados digitados pelo usuário no terminal.
 *
 * Cada método exibe um rótulo, aguarda a digitação de uma linha e converte a
 * entrada para o tipo esperado.
 */
class Input
{
    /** Instância principal da biblioteca, usada para acessar os fluxos de entrada e saída. */
    protected readonly Tui $tui;

    /**
     * Vincula este objeto de leitura à instância principal da biblioteca.
     *
     * @param Tui $tui Instância que possui os fluxos de entrada e saída do terminal.
     */
    public function __construct(Tui $tui)
    {
        $this->tui = $tui;
    }

    /**
     * Solicita ao usuário a digitação de um texto livre.
     *
     * @param string $label Rótulo (prompt) exibido antes da leitura.
     *
     * @return string Texto digitado, sem os espaços das extremidades.
     */
    public function text(string $label): string
    {
        $this->tui->output->write($label);
        return trim(fgets($this->tui->stdin));
    }
    
    /**
     * Solicita ao usuário a digitação de um valor numérico.
     *
     * A entrada passa pelas conversões dos separadores informados: o separador
     * de milhar é removido e o separador decimal é substituído por ".", antes do
     * arredondamento do resultado.
     *
     * @param string     $label             Rótulo (prompt) exibido antes da leitura.
     * @param int|null   $decimals          Quantidade de casas decimais mantidas no arredondamento.
     *                                       Padrão: `PHP_INT_MAX`.
     * @param string     $decimalSeparator  Separador decimal usado na entrada. Padrão: ",".
     * @param string     $thousandSeparator Separador de milhar removido da entrada. Padrão: ".".
     *
     * @return float Valor numérico informado pelo usuário.
     */
    public function number(string $label, ?int $decimals = PHP_INT_MAX, string $decimalSeparator = ',', string $thousandSeparator = '.'): float
    {
        $this->tui->output->write($label);
        $input = trim(fgets($this->tui->stdin));
        $input = str_replace($thousandSeparator, '', $input);
        (float) $input = str_replace($decimalSeparator, '.', $input);
        return round($input, $decimals);
    }
    
    /**
     * Solicita ao usuário a digitação de uma data e/ou hora.
     *
     * @param string      $label  Rótulo (prompt) exibido antes da leitura.
     * @param string|null $format Formato esperado da entrada, no padrão da função `date()`.
     *                            Quando informado, a conversão é feita por
     *                            `date_create_immutable_from_format()`; caso contrário,
     *                            a entrada é interpretada pelo analisador de datas do PHP.
     *
     * @return \DateTimeImmutable Data e hora informadas pelo usuário.
     *
     * @throws \TypeError Quando a entrada não puder ser interpretada como uma data válida.
     */
    public function datetime(string $label, ?string $format = null): \DateTimeImmutable
    {
        $this->tui->output->write($label);
        $input = trim(fgets($this->tui->stdin));
        if(is_null($format)){
            $datetime = date_create_immutable($input);
        }else{
            $datetime = date_create_immutable_from_format($format, $input);
        }
        return $datetime;
    }

    /**
     * Apresenta uma lista de opções e solicita a escolha de uma delas.
     *
     * A pergunta é repetida até que a entrada corresponda a uma das chaves
     * disponíveis.
     *
     * @param array<string, string> $options Opções disponíveis, no formato chave => texto exibido.
     *
     * @return string Chave da opção escolhida pelo usuário.
     */
    public function choice(array $options): string
    {
        foreach($options as $key => $item){
            $this->tui->output->write("[$key]\t$item")->nl();
        }
        $choice = '';
        while(!key_exists($choice, $options)){
            $this->tui->output->write('> ');
            $choice = trim(fgets($this->tui->stdin));
        }
        return $choice;
    }

    /**
     * Apresenta uma lista de opções que permite marcar várias escolhas.
     *
     * As opções marcadas são sinalizadas com "[X]". A cada entrada válida a
     * marcação é alternada, e a seleção é encerrada quando o usuário envia uma
     * linha vazia.
     *
     * @param array<string, string> $options  Opções disponíveis, no formato chave => texto exibido.
     * @param array<int, string>    $defaults Chaves já iniciadas como marcadas.
     *
     * @return array<int, string|null> Chaves marcadas ao final da seleção.
     */
    public function select(array $options, array $defaults = []): array
    {
        $selected = $defaults;
        
        while(true){
            $choice = null;
            foreach($options as $key => $item){
                $sel = ' ';
                if(in_array($key, $selected)){
                    $sel = 'X';
                }
                $this->tui->output->write("[$sel][$key]\t$item")->nl();
            }
            while($choice !== '' && !key_exists($choice, $options)){
                $this->tui->output->write('> ');
                $choice = trim(fgets($this->tui->stdin));
            }
            
            if(mb_strlen($choice) === 0) break;
            
            if(in_array($choice, $selected)){
                unset($selected[array_search($choice, $selected)]);
            }else{
                $selected[] = $choice;
            }
        }
        return $selected;
    }

    /**
     * Lê várias linhas de texto digitadas pelo usuário.
     *
     * Cada linha digitada é apresentada com o marcador "> " e a leitura é
     * encerrada quando o usuário envia uma linha vazia.
     *
     * @return list<string> Linhas digitadas, na ordem em que foram informadas.
     */
    public function memo(): array
    {
        $data = [];
        while(true){
            $line = null;
            $this->tui->output->write('> ');
            $line = trim(fgets($this->tui->stdin));
            if($line === '') break;
            $data[] = $line;
        }
        return $data;
    }
}