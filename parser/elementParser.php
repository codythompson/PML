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
    public function parseElement($domElement) {
        if (!($domElement instanceof DOMElement)) {
            PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                get_class(DOMElement), get_class($domElement));
        }

        $parsedElement = $this->parseElementType($domElement);
        $this->parseAttributes($domElement->attributes, $parsedElement);

        $childNodes = $domElement->childNodes;
        $this->parseChildren($childNodes, $parsedElement);

        return $parsedElement;
    }

    private function parseElementType($domElement) {
        $parsedElement = new HtmlElement($domElement->tagName);
        if ($domElement->hasAttribute(PML_MANAGED_ID_ATTRIBUTE_NAME)) {
            $managedElementId = $domElement->getAttribute(
                PML_MANAGED_ID_ATTRIBUTE_NAME);
            if (array_key_exists($managedElementId, $this->managedElements)) {
                PageParser::throwInvalidError(DUPLICATE_MANAGED_ID_PARSER_MESSAGE,
                    $managedElementId);
            }

        /*
         * TODO re-implement the following as user controll-ee things
         */
            /*
            $className = $this->getClassNameFromTagName($domElement->tagName);
            $parsedElement = new $className;
            if (!($parsedElement instanceof ManagedElement)) {
                PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                    get_class(ManagedElement), get_class($parsedElement));
            }
            $parsedElement->managedElementId = $managedElementId;
            $this->managedElements[$managedElementId] = $parsedElement;
            $domElement->removeAttribute(PML_IS_MANAGED_ATTRIBUTE_NAME);
             */
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
        $split = explode($tagName, XML_NAMESPACE_DELIMITER);
        return $split[count($split) - 1];
    }

    private function parseAttributes($domNamedNodeMap, $parsedElement) {
        if ($parsedElement instanceof ParseableAttributesElement) {
            $parsedElement->parseAttributes($domNamedNodeMap);
        } else {
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

    private function parseChildren($childNodes, $parsedElement) {
        $parsedChildren = array();
        foreach($childNodes as $domElement) {
            if ($domElement instanceof DOMElement) {
                $parsedChildren[] = $this->parseElement($domElement);
            } else if ($domElement instanceof DOMText) {
                //TODO better solution than simply placing text into spans
                // if there is more than one child text node.
                if ($this->hasMoreThanOneDOMTextChild($childNodes)) {
                    $parsedChildren[] = new HtmlElement(SPAN_HTML_TAG_NAME, null,
                        null, $domElement->wholeText);
                } else {
                    $parsedElement->text = $domElement->wholeText;
                }
            } else {
                PageParser::throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
                    get_class(DOMElement), $domElement->__toString());
            }
        }
        $parsedElement->childElements = $parsedChildren;
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
    }

    public function getManagedElements() {
        return $this->managedElements;
    }
}
?>
