<?php
// TODO more graceful way of handling this
if(!defined("PML_ROOT")) {
    throw new ErrorException("the constant PML_ROOT is not defined. " .
        "This constant must be defined when using PML.");
}

/*
 * load constant files
 */
require_once(PMLLoader::replaceConstantsWithValues("{PML_ROOT}/pml_settings.php"));
require_once(PMLLoader::replaceConstantsWithValues("{PML_PARSER_DIR}/tagnames.php"));

class PMLLoader {
    private static $libClasses;

    public static function initPML() {
        $elementsFolderPath = self::replaceConstantsWithValues(PML_ELEMENTS_DIR);
        $parserFolderPath = self::replaceConstantsWithValues(PML_PARSER_DIR);
        $utilFolderPath = self::replaceConstantsWithValues(PML_UTIL_DIR);

        self::$libClasses = array(
            "HtmlDocument" => "$elementsFolderPath/htmlDoc.php",
            "HtmlElement" => "$elementsFolderPath/HtmlElement.php",
            "ManagedDocument" => "$elementsFolderPath/managedDocument.php",
            "ManagedElement" => "$elementsFolderPath/managedElement.php",
            "Widget" => "$elementsFolderPath/widget.php",
            "ElementParser" => "$parserFolderPath/elementParser.php",
            "PageParser" => "$parserFolderPath/parser.php",
            "Util" => "$utilFolderPath/util.php");
    }

    public static function registerClass($className, $path) {
        if (array_key_exists($className, self::$libClasses)) {
            throw new ErrorException("PMLLoader: Class '$className' is already"
                . " registered.");
        } else if ($className === null) {
            throw new ErrorException("PMLLoader: Cannot register null classname");
        } else if ($path === null) {
            throw new ErrorException("PMLLoader: Cannot register null path "
                . "(for class '$className'");
        } else {
            self::$libClasses[$className] = $path;
        }
    }

    public static function getPath($className) {
        if (array_key_exists($className, self::$libClasses)) {
            return self::$libClasses[$className];
        } else {
            var_dump(self::$libClasses);
            throw new ErrorException("PMLLoader: Class $className not registered");
        }
    }

    /*
     * Replaces any instance of opening and closing braces with the value of the
     * constant named by the string within the curly braces.
     *
     * for example:
     *
     * define("EXAMPLE_CONSTANT", "blah blah blah");
     * $var = replaceConstantsWithValues("something {EXAMPLE_CONSTANT} whatever");
     *
     * the value of $var would be "something blah blah blah whatever"
     */
    public static function replaceConstantsWithValues($string) {
        $splitByOpenBrace = explode("{", $string);

        if (count($splitByOpenBrace) < 2) {
            return $string;
        }

        $pieces = array($splitByOpenBrace[0]);
        for($i = 1; $i < count($splitByOpenBrace); $i++) {
            $splitByCloseBrace = explode("}", $splitByOpenBrace[$i], 2);
            if (count($splitByCloseBrace) != 2) {
                $pieces[] = $splitByCloseBrace[0];
            } else {
                $pieces[] = PMLLoader::replaceConstantsWithValues(
                    constant($splitByCloseBrace[0]));
                $pieces[] = $splitByCloseBrace[1];
            }
        }

        return join($pieces);
    }
}
PMLLoader::initPML();

function pml_classLoader($className) {
    $path = PMLLoader::getPath($className);
    /*
    if (!file_exists($path)) {
        throw new ErrorException("Can't load class '$className' the associated"
            . " file '$path' doesn't exist.");
    }*/
    require_once($path);
}

spl_autoload_extensions(".php");
spl_autoload_register("pml_classLoader");
?>
