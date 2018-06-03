<?php
//example link to the site http://directory/index.php?token=1357&lang=en&name=Firstname+Lastname&email=firstname.lastname%40provider.net

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

include './l10n/textStorage.php';
include './php/GuiBuilder.php';

$lang = (isset($_GET['lang']) && $_GET['lang'] === 'de') ? 'de' : 'en';
$GuiBuilder = new GuiBuilder($lang);
$GuiBuilder->setTexts($arrTexts);

if(!isset($_GET['token']) || !$GuiBuilder->validateToken($_GET['token'])) {
    echo $GuiBuilder->buildGui('error');
    
} else if(isset($_GET['submit']) && $GuiBuilder->validateUserInput($_GET)) {
    $boolProcessSuccessful = include './php/writeToCsv.php';
    echo $GuiBuilder->buildGui($boolProcessSuccessful ? 'confirmation' : 'error');
    
} else {
    echo $GuiBuilder->buildGui('form');
}
?>