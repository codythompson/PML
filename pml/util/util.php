<?php
class Util {
    /*
     * This class should only contain static functions and should never be
     * instantiated
     */
    private function __construct() {
    }

    public static function equalsIgnoreCase($stringA, $stringB) {
        return strtolower($stringA) === strtolower($stringB);
    }
}
?>
