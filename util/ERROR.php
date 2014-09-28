<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Static utility class for Error handling
 *
 * @author Allen
 */
final class ERROR {
    
    /**
     * Prompt an error via interface
     * @param String $errormsg Error message to be displayed
     * @param String $str_source (Optional) Name of the source file
     * @param int $linenumber (Optional) Line number during code break
     * @param Boolean $is_break (Optional) Boolean value
     */
    public static function PromptError($errormsg, $str_source=null, $linenumber=null, $is_break=true) {
        echo '<p style="background-color:black; margin:3%; color:#fff; border:5px solid orange;">' . PHP_EOL;
        echo '<b>ERROR!</b>' . PHP_EOL;
        echo '<br>' . $errormsg . '<br>';
        if ($str_source!==null) {
            echo 'From file <b>'.$str_source.'</b> ';
        }
        if ($linenumber!==null && is_int($linenumber)) {
            echo 'at line <b>' . $linenumber . '</b>';
        }
        echo '</p>';
    }
}
