<?php
abstract class ManagedElement extends HtmlElement{
    public abstract function onDOMLoad($managedDocument);
}
?>
