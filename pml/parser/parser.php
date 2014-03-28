<?php
class PageParser {
    private $managedDocument;
    private $elementParser;

    public function __construct() {
        $elementParserClassName = DEFAULT_ELEMENT_PARSER;
        $this->elementParser = new $elementParserClassName();
    }

    public function parseDocumentFile($filepath) {
        $domDocument = new DOMDocument();
        $domDocument->preserveWhiteSpace = false;
        $domDocument->load($filepath);

        $root = $domDocument->documentElement;
        $rootChildren = $root->childNodes;

        $this->validateMetaAndHtml($rootChildren);

        $metaElement = $rootChildren->item(0);
        $htmlChildren = $rootChildren->item(1)->childNodes;

        $this->validateHeadAndBody($htmlChildren);

        $htmlHead = $htmlChildren->item(0);
        $htmlBody = $htmlChildren->item(1);

        $this->parseMetaSection($metaElement);

        $parsedHead = $this->elementParser->parseElement($htmlHead,
            $this->managedDocument);
        $parsedBody = $this->elementParser->parseElement($htmlBody,
            $this->managedDocument);

        $this->managedDocument->headElements = $parsedHead->childElements;
        $this->managedDocument->bodyElements = $parsedBody->childElements;

        $managedElements = $this->elementParser->getManagedElements();
        foreach($managedElements as $managedElement) {
            if ($managedElement instanceof ManagedElement) {
                $managedElement->onDOMLoad($this->managedDocument);
            }
        }
        $this->managedDocument->onDOMLoad($managedElements);

        //write the document
        $this->managedDocument->writeDocument();
    }

    private function validateMetaAndHtml($rootChildren) {
        if ($rootChildren->length != 2) {
            $PageParser::throwInvalidError(EXPECTING_ONLY_META_AND_HTML_PARSER_MESSAGE,
                $rootChildren->length);
        }
        if (!Util::equalsIgnoreCase($rootChildren->item(0)->tagName,
                PML_META_TAG_NAME)) {
            $PageParser::throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                PML_META_TAG_NAME);
        }
        if (!Util::equalsIgnoreCase($rootChildren->item(1)->tagName, HTML_TAG_NAME)) {
            $PageParser::throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                HTML_TAG_NAME);
        }
    }

    private function validateHeadAndBody($htmlChildren) {
        if ($htmlChildren->length != 2) {
            PageParser::throwInvalidError(EXPECTING_ONLY_HEAD_AND_BODY_PARSER_MESSAGE,
                $htmlChildren->length);
        }
        if (!Util::equalsIgnoreCase($htmlChildren->item(0)->tagName,
                HEAD_HTML_TAG_NAME)) {
            PageParser::throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                HEAD_HTML_TAG_NAME);
        }
        if (!Util::equalsIgnoreCase($htmlChildren->item(1)->tagName,
                BODY_HTML_TAG_NAME)) {
            PageParser::throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                BODY_HTML_TAG_NAME);
        }
    }

    private function parseMetaSection($metaElement) {
        $metaChildren = $metaElement->childNodes;

        $managedDocumentElement = null;
        foreach($metaChildren as $element) {
            if (!($element instanceof DOMElement)) {
                self::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                    get_class(DOMElement), get_class($element));
            }

            $tagName = $element->tagName;
            if (Util::equalsIgnoreCase($tagName, PML_MANAGED_DOCUMENT_TAG_NAME)) {
                $managedDocumentElement = $element;
            } else if (Util::equalsIgnoreCase($tagName,
                    PML_REGISTER_CLASS_TAG_NAME)) {
                $className = $element->getAttribute(
                    PML_MANAGED_DOCUMENT_CLASS_NAME_ATTRIBUTE);
                $path = $element->getAttribute(PML_FILE_PATH_ATTRIBUTE);
                PMLLoader::registerClass($className, $path);
            }
        }

        if ($managedDocumentElement == null) {
            $PageParser::throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                PML_MANAGED_DOCUMENT_TAG_NAME);
        } else {
            $this->createDocumentFromElement($managedDocumentElement);
        }
    }

    /*
     * TODO throw error if class_name and file_path not present
     */
    private function createDocumentFromElement($managedDocumentElement) {
        $docClassName = $managedDocumentElement->getAttribute(
            PML_MANAGED_DOCUMENT_CLASS_NAME_ATTRIBUTE);
        
        if ($managedDocumentElement->hasAttribute(PML_FILE_PATH_ATTRIBUTE)) {
            PMLLoader::registerClass($docClassName,
                $managedDocumentElement->getAttribute(PML_FILE_PATH_ATTRIBUTE));
        }
        $this->managedDocument = new $docClassName();
    }

    private function getAttributeValue($element, $attributeName) {
        if ($element->hasAttribute($attributeName)) {
            return $element->getAttribute($attributeName);
        } else {
            PageParser::throwInvalidError(ELEMENT_MISSING_ATTRIBUTE_PARSER_MESSAGE,
                $element, $attributeName);
        }
    }

    /*
     * TODO make this actually return different values based on class name
     */
    public static function getNewElementParser($elementClassName) {
        $elementParserClassName = DEFAULT_ELEMENT_PARSER;
        return new $elementParserClassName();
    }

    public static function throwInvalidError($message) {
        $formattedMessage = call_user_func_array("sprintf", func_get_args());
        throw new InvalidArgumentException($formattedMessage);
    }
}
?>
