<?php

namespace Ptk\Tui;

class Input
{
    protected readonly Tui $tui;

    public function __construct(Tui $tui)
    {
        $this->tui = $tui;
    }

    public function text(string $label): string
    {
        $this->tui->output->write($label);
        return trim(fgets($this->tui->stdin));
    }
    
    public function number(string $label, ?int $decimals = PHP_INT_MAX, string $decimalSeparator = ',', string $thousandSeparator = '.'): float
    {
        $this->tui->output->write($label);
        $input = trim(fgets($this->tui->stdin));
        $input = str_replace($thousandSeparator, '', $input);
        (float) $input = str_replace($decimalSeparator, '.', $input);
        return round($input, $decimals);
    }
    
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