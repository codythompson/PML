<?php
interface AttributesParser {
    public function parseAttributes($domNamedNodeMap);
}

interface ChildrenParser {
    public function parseChildren($childNodes);
}
?>
