<?php
abstract class ManagedElement : HtmlElement{
    public function onDOMLoad($managedDocument, $managedElements);
}
?>
