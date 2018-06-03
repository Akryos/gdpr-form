<?php
$boolCouldWrite = false;
$intWantedLineNr = false;
$strPathCsv = './csv/gdpr.csv';
$strCsvContent = file_get_contents($strPathCsv);
$arrLines = explode(PHP_EOL, $strCsvContent);

foreach($arrLines as $intLineNr => $strLineContent) {
    if(strpos($strLineContent, $_GET['token']) === 0) {
        $intWantedLineNr = $intLineNr;
        break;
    }
}

if(!$intWantedLineNr) {
    //token does not exist in file
    
} else {
    $arrLines[$intWantedLineNr] = implode(',', array(
        $_GET['token'],
        (isset($_GET['gdpr']) && $_GET['gdpr'] === "1") ? 1 : 0,
        $_GET['name'],
        $_GET['email'],
        isset($_GET['optionMailings']) ? 1 : 0,
        isset($_GET['optionTraining']) ? 1 : 0,
        isset($_GET['optionPatches']) ? 1 : 0,
        isset($_GET['optionStorage']) ? 1 : 0,
    ));
    
    $strCsvContent = implode(PHP_EOL, $arrLines);
    $boolCouldWrite = file_put_contents($strPathCsv, $strCsvContent);
}

return !!$boolCouldWrite;
?>