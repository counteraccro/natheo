<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Class utilitaire pour aider pour le débug
 */
namespace App\Utils;

class Debug
{
    /**
     * Permet de reformater correctement le print_r
     * @param array $tab
     * @return void
     */
    public static function print_r(array $tab)
    {
        $tabStack = (debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]);
        echo '<div class="block-debug-pre">';
        echo '<p>Généré depuis la fonction <b>' . $tabStack['function'] . '</b> dans la class <b>' . $tabStack['class'] . '</b>';
        echo '<pre>';
        print_r($tab);
        echo '</pre></div>';
    }
}