<?php
define("ELEMENT_NOT_FOUND", "No '%s' element was found while parsing the document");

class PageParser {
    //public function __construct()

    public function parseDocumentFile($filepath) {
        $domDocument = new DOMDocument();
        $domDocument->loadXMLFile($filepath);

        $headElement = null;
        $bodyElement = null;

        $root = $domDocument->documentElement;
        $rootChildren = $root->childNodes;
        foreach($rootChildren as $child) {
            if ($child->tagName == HEAD_HTML_TAG_NAME) {
                $headElement = $child;
            } else if ($child->tagName == BODY_HTML_TAG_NAME) {
                if ($headElement === null) {
                    throw new InvalidArgumentException(
                        createErrorMessage(HEAD_HTML_TAG_NOT_FOUND,
                        );
                }
                $bodyElement = $child;
            }
        }
    }

    private function createErrorMessage($message) {
        return sprintf(func_get_args());
    }
}
?>
