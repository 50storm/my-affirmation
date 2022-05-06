<?php
namespace MyAffirmationUtility;
class Debug {
    public static function debug_vars($vars) {
        echo '<div style="background-color:yellow;">';
        echo '<h1>Debug vars</h1>';
        var_dump($vars);
        echo '</div>';
    }    
}
