<?php

/**
 * redirect to the documentation
 */


$path = '.phpdoc/build';
$uri = filter_input(INPUT_SERVER, 'REQUEST_URI') ;
header('Location: ' . $uri . $path);

?>