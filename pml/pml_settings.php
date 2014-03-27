<?php
/*
 * PML library directory paths
 *
 * Anything inside of {curly braces} will get replaced with the value of that
 * constant b the PMLLoader.
 *
 * For example
 * define("PML_ROOT", "/lib/pml");
 * define("PML_PARSER", "{PML_ROOT}/parser");
 *
 * PML_PARSER will ultimately be "/lib/pml/parser"
 */
//define("PML_ROOT", "/lib/pml");
define("PML_ELEMENTS_DIR", "{PML_ROOT}/elements");
define("PML_PARSER_DIR", "{PML_ROOT}/parser");
define("PML_UTIL_DIR", "{PML_ROOT}/util");
?>
