<?php namespace DBDiff;


class Logger {
    
    public static function error($msg, $die=false) {
        echo "#".$msg."\n";
        if ($die) die();
    }

    public static function success($msg) {
        echo "\n#".$msg."\n";
    }

    public static function info($msg) {
        echo "#".$msg."\n";
    }
}

