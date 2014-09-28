<?php

final class CMS {

    public static function renderBox($path_to_setting_file) {
        $properties = parse_ini_file('config/BOX.ini');
        $css = '';

        if (count($properties) > 0) {
            do {
                $css .= key($properties) . ':' . current($properties) . '; ';
            } while (next($properties));
        }

        echo '<div style="' . $css . '">';
        echo 'My content';
        echo '</div>';
    }

}
?>