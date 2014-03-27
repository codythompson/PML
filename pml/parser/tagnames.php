<?php
//TODO RENAME THIS FILE TO CONSTANTS
/*
 * Misc Constants
 */
define("TRUE_BOOLEAN", "true");
define("FALSE_BOOLEAN", "true");

/*
 * XML / HTML
 */
//PML SPECIAL ELEMENTS
define("PML_ROOT_TAG_NAME", "pml:root");
define("PML_XMLNS_ATTRIBUTE", "xmlns:pml=\"http://flatverse.com\"");
define("PML_ASSOCIATED_MARKUP_ROOT_TAG_NAME", "pml:widget_root");
define("PML_META_TAG_NAME", "pml:meta");
define("PML_HTML_CONTENT_TAG_NAME", "pml:content");

define("PML_MANAGED_DOCUMENT_TAG_NAME", "pml:managed_document");
define("PML_REGISTER_CLASS_TAG_NAME", "pml:register_class");
define("PML_MANAGED_DOCUMENT_CLASS_NAME_ATTRIBUTE", "pml:class_name");
define("PML_MANAGED_ID_ATTRIBUTE_NAME", "pml:managed_element_id");
define("PML_ASSOCIATED_MARKUP_ATTRIBUTE_NAME", "pml:associated_markup");
define("PML_FILE_PATH_ATTRIBUTE", "pml:file_path");

//HTML REQUIRED ELEMENTS
define("HTML_TAG_NAME", "html");
define("HEAD_HTML_TAG_NAME", "head");
define("BODY_HTML_TAG_NAME", "body");

//HTML BLOCK ELEMENTS
define("DIV_HTML_TAG_NAME", "div");

//HTML INLINE ELEMENTS
define("SPAN_HTML_TAG_NAME", "span");

//COMMON ATTRIBUTES
define("XMLNS_ATTRIBUTE", "xmlns");
define("CSS_ID", "id");
define("CSS_CLASS", "class");

//XML SPECIAL CHARACTERS
define("XML_NAMESPACE_DELIMITER", ":");

/*
 * Constants used by the parser class
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
//TODO move this to a settings file
define("DEFAULT_ELEMENT_PARSER", "ElementParser");
?>
