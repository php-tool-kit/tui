<?php

namespace Ptk\Tui;

use stdClass;

/**
 * Ponto de entrada da biblioteca TUI (Text User Interface).
 *
 * Centraliza a criação dos serviços de entrada e saída e fornece
 * informações sobre a janela do terminal, como linhas e colunas.
 */
class Tui
{

    /** Instância responsável pela leitura dos dados informados pelo usuário. */
    public readonly Input $input;

    /** Instância responsável pela escrita dos dados no terminal. */
    public readonly Output $output;

    /** @var resource Fluxo de entrada padrão do processo (STDIN). */
    public $stdin = STDIN;

    /** @var resource Fluxo de saída padrão do processo (STDOUT). */
    public $stdout = STDOUT;

    /** @var resource Fluxo de saída de erros padrão do processo (STDERR). */
    public $stderr = STDERR;

    /**
     * Cria as instâncias de entrada e de saída vinculadas a esta instância.
     */
    public function __construct()
    {
        $this->input = new Input($this);
        $this->output = new Output($this);
    }

    /**
     * Obtém as dimensões da janela do terminal.
     *
     * O sistema operacional é detectado automaticamente para escolher a
     * estratégia adequada de leitura das dimensões.
     *
     * @return stdClass Objeto contendo as propriedades `lines` (número de linhas)
     *                  e `cols` (número de colunas) disponíveis para a saída.
     */
    public function getScreenDimension(): stdClass
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') return $this->getScreenDimensionForWindows();

        return $this->getScreenDimensionForLinux();
    }

    /**
     * Obtém as dimensões do terminal em sistemas Windows.
     *
     * O comando `mode CON` é executado para ler a configuração atual da janela
     * de console. Quando os valores não são encontrados na saída do comando,
     * são utilizados os padrões de 120 linhas e 80 colunas.
     *
     * @return stdClass Objeto contendo as propriedades `lines` e `cols`.
     */
    protected function getScreenDimensionForWindows(): stdClass
    {
        $dimension = new stdClass();
        $dimension->lines = 120;
        $dimension->cols = 80;
        $modecon = exec('mode CON', $output);
        foreach($output as $item){
            if(str_starts_with(trim(strtolower($item)), 'lin')){
                $dimension->lines = (int) explode(':', $item)[1];
                continue;
            }
            if(str_starts_with(trim(strtolower($item)), 'col')){
                $dimension->cols = (int) explode(':', $item)[1];
                continue;
            }
        }
        return $dimension;
    }

    /**
     * Obtém as dimensões do terminal em sistemas Linux/Unix.
     *
     * Os comandos `tput lines` e `tput cols` são executados para consultar a
     * geometria atual da janela do terminal.
     *
     * @return stdClass Objeto contendo as propriedades `lines` e `cols`.
     */
    protected function getScreenDimensionForLinux(): stdClass
    {
        $dimension = new stdClass();
        $tputl = (int) exec('tput lines 2>/dev/null', $lines);
        $tputc = (int) exec('tput cols 2>/dev/null', $cols);
        $dimension->lines = $lines;
        $dimension->cols = $cols;
        return $dimension;
    }
}