<?php
class GuiBuilder {
    private $strJavascriptIncludeTemplate = '<script type="text/JavaScript" src="./javascript/%URL%"></script>';
    private $strCssIncludeTemplate = '<link rel="stylesheet" href="./css/%URL%" />';
    private $strLang;
    private $arrTexts = [];
    private $arrValidationError = [];
    
    public function __construct($strLang) {
        $this->strLang = $strLang === 'de' ? $strLang : 'en';
    }
    
    public function validateToken($strToken) {
        if(!preg_match('/[A-Za-z0-9]*/', $strToken)) {
            return false;
        }
        
        return true;
    }
    
    public function setTexts($arrTexts) {
        if(is_array($arrTexts) && array_key_exists($this->strLang, $arrTexts)) {
            $this->arrTexts = $arrTexts[$this->strLang];
        }
    }
    
    public function buildGui($strPageType) {
        $strTemplate = file_get_contents('./html/skeleton.html');
        $strTemplate = str_replace('%INCLUDES%', $this->getIncludes(), $strTemplate);
        $strTemplate = str_replace('%CONTENT%', $this->getPage($strPageType), $strTemplate);
        
        $strTemplate = str_replace(array_keys($this->arrTexts), array_values($this->arrTexts), $strTemplate);
        
        //$this->loadGetParameters();
        //$strTemplate = str_replace(array_keys($this->arrTexts[$this->arrGetParameters['%GET_LANG%']]), array_values($this->arrTexts[$this->arrGetParameters['%GET_LANG%']]), $strTemplate);
        //$strTemplate = str_replace(array_keys($this->arrGetParameters), array_values($this->arrGetParameters), $strTemplate);
        $strTemplate = str_replace('%SELF%', $_SERVER['PHP_SELF'], $strTemplate);
        
        return $strTemplate;
    }
    
    private function getIncludes() {
        $arrDirs = array_merge(scandir('./css'), scandir('./javascript'));
        $strIncludes = '';
        
        foreach($arrDirs as $strFileName) {
            if(strpos($strFileName, '.js') !== false) {
                $strIncludes .= str_replace('%URL%', $strFileName, $this->strJavascriptIncludeTemplate).PHP_EOL;
            } else if(strpos($strFileName, '.css')) {
                $strIncludes .= str_replace('%URL%', $strFileName, $this->strCssIncludeTemplate).PHP_EOL;
            } else {
                //not an expected file, ignore it
            }
        }
        
        return $strIncludes;
    }
    
    private function getPage($strPageType) {
        if(!file_exists('./html/'.$strPageType.'.html')) {
            $strPageType = 'error';
        }
        
        $strPage = file_get_contents('./html/'.$strPageType.'.html');
        
        if($strPageType === 'form') {
            $strPage = $this->prefillForm($strPage);
            $strPage = $this->appendErrorMessages($strPage);
        }
        
        return $strPage;
    }
    
    private function prefillForm($strForm) {
        $strForm = str_replace('%%TOKEN%%', $_GET['token'], $strForm);
        $strForm = str_replace('%%LANG%%', $_GET['lang'], $strForm);
        
        $strForm = str_replace('%%NAME%%', isset($_GET['name']) ? $_GET['name'] : '', $strForm);
        $strForm = str_replace('%%EMAIL%%', isset($_GET['email']) ? $_GET['email'] : '', $strForm);
        
        $_GET['gdpr'] = (isset($_GET['gdpr']) && $_GET['gdpr'] == 0) ? 0 : 1;
        
        $strForm = str_replace('%%GDPR_CHECKED_YES%%', ($_GET['gdpr'] == 1 ? 'checked' : ''), $strForm);
        $strForm = str_replace('%%GDPR_CHECKED_NO%%', ($_GET['gdpr'] == 0 ? 'checked' : ''), $strForm);
        
        $strForm = str_replace('%%OPTION_MAILINGS_CHECKED%%', (isset($_GET['optionMailings']) ? 'checked' : ''), $strForm);
        $strForm = str_replace('%%OPTION_TRAINING_CHECKED%%', (isset($_GET['optionTraining']) ? 'checked' : ''), $strForm);
        $strForm = str_replace('%%OPTION_PATCHES_CHECKED%%', (isset($_GET['optionPatches']) ? 'checked' : ''), $strForm);
        $strForm = str_replace('%%OPTION_STORAGE_CHECKED%%', (isset($_GET['optionStorage']) ? 'checked' : ''), $strForm);
        
        return $strForm;
    }
    
    private function appendErrorMessages($strForm) {
        $strErrorClass = 'hide';
        $strErrorMessages = '';
        
        if(count($this->arrValidationError)) {
            $strErrorClass = 'red-text';
            $strErrorMessages = '<ul class="collection">'.PHP_EOL;
            
            foreach($this->arrValidationError as $strErrorType) {
                $strErrorMessages .= '<li class="collection-item"><div class="valign-wrapper"><i class="material-icons">error</i>&emsp;'.$this->arrTexts[$strErrorType].'</div></li>'.PHP_EOL;
            }
            
            $strErrorMessages .= '</ul>';
        }
        
        $strForm = str_replace('%FORM_ERROR_CLASS%', $strErrorClass, $strForm);
        $strForm = str_replace('%FORM_ERROR_MESSAGES%', $strErrorMessages, $strForm);
        
        return $strForm;
    }
    
    public function validateUserInput($arrGetParameters) {
        if($arrGetParameters['gdpr'] == 0) {
            return true;
        }
        
        foreach($arrGetParameters as $strName => $strValue) {
            switch($strName) {
                case 'token':
                case 'lang':
                    //gets taken care of by the index file
                    break;
                
                case 'name':
                    if(!preg_match('/[A-Za-z]*?[ ][A-Za-z]*?/', $strValue)) {
                        $this->arrValidationError[] = '%ERROR_MESSAGE_NAME%';
                    }
                    break;
                
                case 'email':
                    if(!filter_var($strValue, FILTER_VALIDATE_EMAIL)) {
                        $this->arrValidationError[] = '%ERROR_MESSAGE_EMAIL%';
                    }
                    break;
                
                default:
                    //other parameters aren't needed for their value
                    break;
            }
        }
        
        return !count($this->arrValidationError);
    }
}
?>