<?php
require_once("parser.php");
/*
 * This should maybe be changed to an interface and have the implementation
 * moved to a different class.
 */
class ElementParser {
    private $managedElements;

    public function __construct() {
        $this->managedElements = array();
    }

    /*
     * Recursively parse the XML into a tree of HtmlElement objects
     */
    public function parseElement($domElement, $parentDocument) {
        if (!($domElement instanceof DOMElement)) {
            PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                get_class(DOMElement), get_class($domElement));
        }

        $parsedElement = $this->parseElementType($domElement, $parentDocument);
        $this->parseAttributes($domElement->attributes, $parsedElement);

        $childNodes = $domElement->childNodes;
        $this->parseChildren($childNodes, $parsedElement, $parentDocument);

        return $parsedElement;
    }

    /*
     * TODO some kind of better filepath handling
     */
    private function parseElementFile($filePath, $widget, $parentDocument) {
        $domDocument = new DOMDocument();
        $domDocument->preserveWhiteSpace = false;
        $domDocument->load($filePath);

        $root = $domDocument->documentElement;
        if ($root === null
                || !($root->tagName !== PML_ASSOCIATED_MARKUP_ROOT_TAG_NAME)) {
            PageParser::throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                PML_ASSOCIATED_MARKUP_ROOT_TAG_NAME);
        }

        $rootChildren = $root->childNodes;

        $elementParser = PageParser::getNewElementParser(get_class($widget));
        foreach($rootChildren as $rootChild) {
            if (!($rootChild instanceof DOMElement)) {
                PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                    DOMElement, get_class($rootChild));
            }
            $widget->childElements[] = $elementParser->parseElement($rootChild
                , $parentDocument);
        }

        foreach($elementParser->managedElements as $managedElement) {
            if ($managedElement instanceof ManagedElement) {
                $managedElement->onDOMLoad($parentDocument);
            }
        }
        $widget->onMarkupLoad($elementParser->managedElements, $parentDocument);
    }

    private function parseElementType($domElement, $parentDocument) {
        $parsedElement = new HtmlElement($domElement->tagName);
        $className = $this->getClassNameFromTagName($domElement->tagName);
        if ($className !== null) {
            $parsedElement = new $className();
        }
        if ($domElement->hasAttribute(PML_ASSOCIATED_MARKUP_ATTRIBUTE_NAME)) {
            //TODO allow for custom associated markup parsing
            if (!($parsedElement instanceof Widget)) {
                PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                    "Widget", get_class($parsedElement));
            }
            $associatedMarkupFilename = $domElement->getAttribute(
                PML_ASSOCIATED_MARKUP_ATTRIBUTE_NAME);
            $this->parseElementFile($associatedMarkupFilename, $parsedElement,
                $parentDocument);
        }
        if ($domElement->hasAttribute(PML_MANAGED_ID_ATTRIBUTE_NAME)) {
            $managedElementId = $domElement->getAttribute(
                PML_MANAGED_ID_ATTRIBUTE_NAME);
            if (array_key_exists($managedElementId, $this->managedElements)) {
                PageParser::throwInvalidError(DUPLICATE_MANAGED_ID_PARSER_MESSAGE,
                    $managedElementId);
            }
            $this->managedElements[$managedElementId] = $parsedElement;
        }

        return $parsedElement;
    }

    /*
     * Returns the last substring of $tagName split by a colon ':'
     *
     * aka if element is <pml:someKindOfSomething></pml:someKindOfSomething>
     * this would return 'someKindOfSomething'
     *
     * if you had <pml:youHaveMoreThan1Colon:ForSomeWeirdReason/>
     * this would return 'ForSomeWeirdReason'
     */
    private function getClassNameFromTagName($tagName) {
        $split = explode(XML_NAMESPACE_DELIMITER, $tagName);
        if (count($split) < 2) {
            return null;
        }
        return $split[count($split) - 1];
    }

    private function parseAttributes($domNamedNodeMap, $parsedElement) {
        if ($parsedElement instanceof AttributesParser) {
            $domNamedNodeMap = $parsedElement->parseAttributes($domNamedNodeMap);
        }
        if ($domNamedNodeMap !== null) {
            foreach($domNamedNodeMap as $attribute) {
                if ($attribute->name === CSS_ID) {
                    $parsedElement->cssId = $attribute->value;
                } else if ($attribute->name === CSS_CLASS) {
                    $parsedElement->cssClass = $attribute->value;
                } else {
                    $parsedElement->setAttribute($attribute->name, $attribute->value);
                }
            }
        }
    }

    private function parseChildren($childNodes, $parsedElement, $parentDocument) {
        if ($parsedElement instanceof ChildrenParser) {
            $childNodes = $parsedElement->parseChildren($childNodes);
        }
        if ($childNodes !== null) {
            foreach($childNodes as $domElement) {
                if ($domElement instanceof DOMElement) {
                    $parsedElement->childElements[] = $this->parseElement($domElement, $parentDocument);
                } else if ($domElement instanceof DOMText) {
                    //TODO better solution than simply placing text into spans
                    // if there is more than one child text node.
                    if ($this->hasMoreThanOneDOMTextChild($childNodes)) {
                        $parsedElement->childElements[] = new HtmlElement(SPAN_HTML_TAG_NAME,
                            null, null, $domElement->wholeText);
                    } else {
                        $parsedElement->text = $domElement->wholeText;
                    }
                } else {
                    PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                        get_class(DOMElement), $domElement->__toString());
                }
            }
        }
    }

    private function hasMoreThanOneDOMTextChild($childNodes) {
        $foundOne = false;
        foreach($childNodes as $child) {
            if ($child instanceof DOMText) {
                if ($foundOne) {
                    return true;
                } else {
                    $foundOne = true;
                }
            }
        }
        return false;
    }

    public function getManagedElements() {
        return $this->managedElements;
    }
}
?>
