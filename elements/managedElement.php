<?php
abstract class ManagedElement : HtmlElement{
    public managedElementId;

    /*
     * instead there will be another interface for this as a frequent use
     * case will be to let the parser just parse the children as normal
     */
    //public function parseChildren($childNodes);

    public function onDOMLoad($managedDocument);
}
?>
