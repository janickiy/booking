<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.02.2018
 * Time: 13:31
 */

namespace App\Helpers;


class ConsoleHelpers
{

    public static function consoleShowBar($done, $total, $size=70) {

        static $start_time;

        if($done > $total) return;

        if(empty($start_time)) $start_time=time();
        $now = time();

        $perc=(double)($done/$total);

        $bar=floor($perc*$size);

        $status_bar="\r[[cyan]";
        $status_bar.=str_repeat("|", $bar);
        if($bar<$size){
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size-$bar);
        } else {
            $status_bar.="|";
        }

        $disp=number_format($perc*100, 0);

        $status_bar.="[/]] $disp%  $done/$total";

        $rate = ($now-$start_time)/$done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

        echo self::consoleColorize("$status_bar  ");

        flush();

        if($done == $total) {
            echo "\n";
        }

    }



     public static function consoleColorize($text){

        $colorsPatternsList = [
            '[red]' => "\e[0;31m",
            '[black]' => "\e[0;30m",
            '[green]' => "\e[0;32m",
            '[orange]' => "\e[0;33m",
            '[blue]' => "\e[0;34m",
            '[purple]' => "\e[0;35m",
            '[cyan]' => "\e[0;36m",
            '[lightGray]' => "\e[0;37m",
            '[darkGray]' => "\e[1;30m",
            '[lightRed]' => "\e[1;31m",
            '[lightGreen]' => "\e[1;32m",
            '[yellow]' => "\e[1;33m",
            '[lightBlue]' => "\e[1;34m",
            '[lightPurple]' => "\e[1;35m",
            '[lightCyan]' => "\e[1;36m",
            '[white]' => "\e[1;37m",
            '[/]' =>  "\e[0m"
        ];

        return strtr($text,$colorsPatternsList);
    }


}