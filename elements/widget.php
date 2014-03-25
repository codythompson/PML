<?php
abstract class Widget extends HtmlElement {
    public function __construct() {
        parent::__construct(null);
    }

    public function writeOpenTag() {
        //No Op
    }

    public function writeCloseTag() {
        //No Op
    }

    public abstract function onMarkupLoad($managedElements, $parentDocument);
}
?>
