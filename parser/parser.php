<?php
//TODO dynamically load the class php files
require_once("classfileloader.php");

require_once("/lib/util/util.php");
require_once("tagnames.php");
/*
 * Constants used exclusively by this class
 */
define("ELEMENT_NOT_FOUND_PARSER_MESSAGE",
    "No '%s' element was found while parsing the document");
define("EXPECTING_ONLY_HEAD_AND_BODY_PARSER_MESSAGE",
    "Expecting only head and body elements in html tag, found '%d' elements.");
define("EXPECTING_ONLY_META_AND_HTML_PARSER_MESSAGE",
    "Expecting only pml:meta and html tags at top level, found '%d' elements.");
define("ELEMENT_MISSING_ATTRIBUTE_PARSER_MESSAGE",
    "'%s' element is missing '%s' attribute.");
define("UNEXPECTED_TYPE_PARSER_MESSAGE",
    "Expected '%s' but received '%s'");
define("DUPLICATE_MANAGED_ID_PARSER_MESSAGE",
    "The managed_element_id '%s' has already been used.");

class PageParser {
    private $managedDocument;
    private $managedElements;

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

        //DOM LOADING which I refer to as parsing not quite accurately
        $this->managedElements = array();
        
        $this->parseMetaSection($metaElement);

        $parsedHead = $this->parseElement($htmlHead);
        $parsedBody = $this->parseElement($htmlBody);

        $this->managedDocument->headElements = $parsedHead->childElements;
        $this->managedDocument->bodyElements = $parsedBody->childElements;

        //NEED to account for difference of being able to change text and 
        //attributes of normal html elements and the notion of user-control
        // -like behavior
        // lets change the name of ManagedElement class to Widget
        /*
        foreach($this->managedELements as $managedElement) {
            $managedElement->onDOMLoad($this->managedDocument);
        }
         */
        $this->managedDocument->onDOMLoad($this->managedElements);

        //write the document
        $this->managedDocument->writeDocument();
    }

    private function validateMetaAndHtml($rootChildren) {
        if ($rootChildren->length != 2) {
            $this->throwInvalidError(EXPECTING_ONLY_META_AND_HTML_PARSER_MESSAGE,
                $rootChildren->length);
        }
        if (!equalsIgnoreCase($rootChildren->item(0)->tagName,
                PML_META_TAG_NAME)) {
            $this->throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                PML_META_TAG_NAME);
        }
        if (!equalsIgnoreCase($rootChildren->item(1)->tagName, HTML_TAG_NAME)) {
            $this->throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                HTML_TAG_NAME);
        }
    }

    private function validateHeadAndBody($htmlChildren) {
        if ($htmlChildren->length != 2) {
            $this->throwInvalidError(EXPECTING_ONLY_HEAD_AND_BODY_PARSER_MESSAGE,
                $htmlChildren->length);
        }
        if (!equalsIgnoreCase($htmlChildren->item(0)->tagName,
                HEAD_HTML_TAG_NAME)) {
            $this->throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                HEAD_HTML_TAG_NAME);
        }
        if (!equalsIgnoreCase($htmlChildren->item(1)->tagName,
                BODY_HTML_TAG_NAME)) {
            $this->throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                BODY_HTML_TAG_NAME);
        }
    }

    private function parseMetaSection($metaElement) {
        $metaChildren = $metaElement->childNodes;

        $managedDocumentElement = null;
        foreach($metaChildren as $element) {
            if ($element instanceof DOMElement
                    && equalsIgnoreCase($element->tagName,
                    PML_MANAGED_DOCUMENT_TAG_NAME)) {
                $managedDocumentElement = $element;
            }
        }

        if ($managedDocumentElement == null) {
            $this->throwInvalidError(ELEMENT_NOT_FOUND_PARSER_MESSAGE,
                PML_MANAGED_DOCUMENT_TAG_NAME);
        } else {
            $this->createDocumentFromElement($managedDocumentElement);
        }
    }

    private function createDocumentFromElement($managedDocumentElement) {
        $docClassName = $this->getAttributeValue($managedDocumentElement,
            PML_MANAGED_DOCUMENT_CLASS_NAME_ATTRIBUTE);
//TODO dynamically load the class php files
//        $docFilePath = $this->getAttributeValue($managedDocumentElement,
//            PML_MANAGED_DOCUMENT_FILE_PATH_ATTRIBUTE);
        $this->managedDocument = new $docClassName();
    }

    /*
     * Recursively parse the XML into a tree of HtmlElement objects
     */
    private function parseElement($domElement) {
        if (!($domElement instanceof DOMElement)) {
            $this->throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
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
                $this->throwInvalidError(DUPLICATE_MANAGED_ID_PARSER_MESSAGE,
                    $managedElementId);
            }

        /*
         * TODO re-implement the following as user controll-ee things
         */
            /*
            $className = $this->getClassNameFromTagName($domElement->tagName);
            $parsedElement = new $className;
            if (!($parsedElement instanceof ManagedElement)) {
                $this->throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
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
                $this->throwInvalidError(UNEXPECTED_TYPE_PARSER_MESSAGE,
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

    private function getAttributeValue($element, $attributeName) {
        if ($element->hasAttribute($attributeName)) {
            return $element->getAttribute($attributeName);
        } else {
            $this->throwInvalidError(ELEMENT_MISSING_ATTRIBUTE_PARSER_MESSAGE,
                $element, $attributeName);
        }
    }

    private function throwInvalidError($message) {
        $formattedMessage = call_user_func_array("sprintf", func_get_args());
        throw new InvalidArgumentException($formattedMessage);
    }
}
?>
