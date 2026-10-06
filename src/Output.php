<?php

namespace Ptk\Tui;

class Output
{
    protected readonly Tui $tui;

    public function __construct(Tui $tui)
    {
        $this->tui = $tui;
    }

    public function write(string $data): self
    {
        fwrite($this->tui->stdout, $data);
        return $this;
    }

    public function nl(int $repeat = 1): self
    {
        echo str_repeat(PHP_EOL, $repeat);
        return $this;
    }

    public function hline(string $char = '-'): self
    {
        $max = $this->tui->getScreenDimension()->cols;
        echo mb_str_pad('', $max, $char), PHP_EOL;
        return $this;
    }

    public function label(string $right, string|float|int $left, string $char = '.'): self
    {
        $max = $this->tui->getScreenDimension()->lines;
        $size = mb_strlen($right.$left);
        echo $right . mb_str_pad('', $max-$size, $char) . $left, PHP_EOL;
        return $this;
    }

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