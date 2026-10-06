<?php

namespace Ptk\Tui;

use stdClass;

class Tui
{

    public readonly Input $input;
    public readonly Output $output;

    public $stdin = STDIN;
    public $stdout = STDOUT;
    public $stderr = STDERR;
    public function __construct()
    {
        $this->input = new Input($this);
        $this->output = new Output($this);
    }

    public function getScreenDimension(): stdClass
    {
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') return $this->getScreenDimensionForWindows();

        return $this->getScreenDimensionForLinux();
    }

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