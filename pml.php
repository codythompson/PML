<?php
define("PML_ROOT", "/lib/pml");
require_once("/lib/pml/pml_loader.php");

$path = parse_url($_SERVER["REQUEST_URI"]);
$path = $path["path"];

if (file_exists($_SERVER["DOCUMENT_ROOT"]."$path")) {
    $parser = new PageParser();
    $parser->parseDocumentFile($_SERVER["DOCUMENT_ROOT"].$path);
} else {
    header("HTTP/1.0 404 Not Found");
    //TODO not hard coded crappy 404
?>
<html>
<head>
<title>4 0 4</title>
<style>
body {
    font-family:Tahoma, Geneva, sans-serif
}
</style>
</head>
<body>
<h1>4<br/>0<br/>4<br/></h1>
</body>
</html>
<?php
}
?>
