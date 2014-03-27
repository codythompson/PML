<?php
class HtmlDocument
{
    public $docTypeDeclaration;
    public $pageTitle;
    public $headElements;
    public $bodyElements;

    public function __construct($pageTitle, $headElements = array(),
        $bodyElements = array())
    {
        $this->pageTitle = $pageTitle;
        $this->headElements = $headElements;
        $this->bodyElements = $bodyElements;

        $this->docTypeDeclaration = "<!DOCTYPE html>";
    }

    /*
     *  Creates the head section of the doc
     *  as well as the doctype and opening html tags
     */
    public function writeOpenHead()
    {
        echo $this->docTypeDeclaration;
        echo "\n<html>\n";
        echo "<head>\n";
    }

    public function writeHeadElements()
    {
        echo "<title>$this->pageTitle</title>";
        foreach($this->headElements as $element)
        {
            $element->writeElement();
            echo "\n";
        }
    }

    public function writeCloseHead()
    {
        echo "</head>\n";
    }

    public function writeHead()
    {
        $this->writeOpenHead();
        $this->writeHeadElements();
        $this->writeCloseHead();
    }

    /*
     * creates the Body Section of the doc
     * as well as the closing html tag
     */
    public function writeOpenBody()
    {
        echo "<body>\n";
    }

    public function writeBodyElements()
    {
        foreach($this->bodyElements as $element)
        {
            $element->writeElement();
            echo "\n";
        }
    }

    public function writeCloseBody()
    {
        echo "</body>\n";
        echo "</html>";
    }

    public function writeBody()
    {
        $this->writeOpenBody();
        $this->writeBodyElements();
        $this->writeCloseBody();
    }

    /*
     * Write the whole thing
     */
    public function writeDocument()
    {
        $this->writeHead();
        $this->writeBody();
    }
}
?>
