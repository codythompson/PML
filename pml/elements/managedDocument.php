<?php
abstract class ManagedDocument extends HtmlDocument {
    public abstract function onDOMLoad($managedElements);
}
?>
