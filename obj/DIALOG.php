<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Object for creating dialog page for user interactions and system dialogs.
 *
 * @author Allen
 */
class DIALOG {
    
    /**
     * @var Array(assoc) Contains buttons with the following properties:<br>
     * <ul>
     * CONST_Button_type(B_YES, B_NO, B_CANCEL) => "Caption text"
     * </ul>
     */ public $DialogButtons;
    public $DialogMessage;
    public $DialogPagecallback;
    public $DialogResult;
    public $DialogTitle;
    /**
     * This is the pagename of the dialog page defined in 'config/pages.ini'
     * @var String 
     */
    const DIALOG_PAGENAME = 'dialogbox';
    
    // Dialog box constant result values
    const   R_NOTHING = -1,
            R_CANCEL = 1,
            R_AFFIRMATIVE = 2,
            R_NEGATIVE = 3;
    // Dialog box constant button types
    const   B_YES = 1,
            B_NO = 2,
            B_CANCEL = 3;
    
    /**
     * Initialize new instance of this object
     * @param string $title The title of this Dialog page
     */
    public function __construct($title) {
        $this->DialogButtons = array();
        $this->DialogMessage = null;
        $this->DialogPagecallback = null;
        $this->DialogResult = self::R_NOTHING;
        $this->DialogTitle = $title;
    }
    
    /**
     * Adds new button object, if button type already exists, it will be replaced
     * @param ButtonType $buttontype Type of this button
     * @param string $caption Button caption
     * @return \DIALOG
     */
    public function AddButton($buttontype, $caption = null) {
        if (is_null($caption)) {
            $caption = ($buttontype===self::B_YES ? 'Yes'
                    : ($buttontype===self::B_NO ? 'No'
                        : 'Cancel'));
        }
        if (array_key_exists($buttontype, $this->DialogButtons)) {
            $this->DialogButtons[$buttontype] = $caption;
        } else {
            $this->DialogButtons[$buttontype] = $caption;
        }
        return $this;
    }
    
    /**
     * Clears all button controls in this dialog
     */
    public function ClearButtons() {
        $this->DialogButtons = array();
    }
    
    /**
     * Gets the current message of this instance
     * @return String
     */
    public function GetMessage() {
        return $this->DialogMessage;
    }
    
    /**
     * Gets the call-back page of this instance.
     * @return String
     */
    public function GetPagecallback() {
        return $this->DialogPagecallback;
    }
    
    /**
     * Gets the result of this Dialog instance
     * @return int The DialogResult type of result instance
     */
    public function GetResult() {
        return $this->DialogResult;
    }
    
    /**
     * Get if this instance has valid result (other than NOTHING)
     * @return type
     */
    public function HasResult() {
        if (is_null($this->DialogResult)) {
            $this->DialogResult = self::R_NOTHING;
        }
        return $this->DialogResult!=self::R_NOTHING;
    }
    
    /**
     * Sets the page to be called after a control has been pressed
     * @param string $pagename The name of the callback page
     * @return \DIALOG
     */
    public function SetPageCallback($pagename) {
        $this->DialogPagecallback = $pagename;
        return $this;
    }
    
    /**
     * Sets the dialog result and redirect to callback page
     *      with an intent <b>DIALOG_RESULT</b> containing the result.
     * @param DialogResult $result The dialog page result
     * @param Boolean $is_readylaunch (Optional=false) Boolean value if this should redirect to callback page
     *      after declaring 'DialogResult'
     */
    public function SetResult($result, $is_readylaunch=false) {
        $this->DialogResult = $result;
        if ($is_readylaunch) {
            DATA::SetIntent('DIALOG_RESULT', $this->DialogResult);
            DATA::DeleteIntent('LOCALRESULT'); // verify that no LOCALRESULT intent exists
            UI::RedirectTo($this->DialogPagecallback);
        }
    }
    
    /**
     * Sets the title of this dialog
     * @param string $title The title of this dialog
     * @return \DIALOG
     */
    public function SetTitle($title) {
        $this->DialogTitle = $title;
        return $this;
    }
    
    /**
     * Sets the dialog message
     * @param string $message The message (can contain HTML) to be shown
     * @return \DIALOG
     */
    public function SetMessage($message) {
        $this->DialogMessage = $message;
        return $this;
    }
    
    public function ShowDialog() {
        $specs_check = DATA::__BulkCheck(array(
            count($this->DialogButtons) > 0,
            $this->DialogResult == self::R_NOTHING,
            !is_null($this->DialogMessage),
            !is_null($this->DialogPagecallback)
        ));
        if ($specs_check==-1) {
            DATA::openPassage(self::DIALOG_PAGENAME, true, false);
            DATA::SetIntent('DIALOG_OBJECT', $this);
            UI::RedirectTo(self::DIALOG_PAGENAME);
        } else {
            ERROR::PromptError('Specs checking failed at index ' . $specs_check, null, null, true);
        }
        return;
    }
    
    /**
     * Static function to convert generic objects into Dialogs
     * @param object $object
     * @return DIALOG
     */
    public static function ToDialog($object) {
        $dialog = new DIALOG($object->DialogTitle);
        $dialog->DialogButtons = $object->DialogButtons;
        $dialog->SetMessage($object->DialogMessage);
        $dialog->SetResult($object->DialogResult);
        $dialog->SetPageCallback($object->DialogPagecallback);
        $dialog->SetResult($object->DialogResult);
        return $dialog;
    }
    
}
