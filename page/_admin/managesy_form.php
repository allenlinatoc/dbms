<?php
$postAcademicyear = '';
$postDescription = '';
$MODE = 'ADD';

$currentyear = intval(DATEMAN::getYear());
$postAcademicyear = $currentyear;

// array of year ranges for dropdown
$a_yearoptions = array();
for($x=$currentyear-1; $x<$currentyear+51; $x++) {
    // generating array of year ranges
    $key = $x . ' - ' . ($x+1);
    $a_yearoptions[$key] = $x;
}



if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- FILTERED $_GET data
    #
    if (DATA::__HasIntentData('TARGET_ID')) {
        $MODE = 'EDIT';
        $TARGET_ID = intval(DATA::__GetIntentSecurely('TARGET_ID', TRUE, TRUE));
        $sql = new DB();
        $sql->
                Select([ 'year', 'description' ])->
                From('sy')->
                Where('id='.$TARGET_ID);
        $result = $sql->Query()[0];
        DATA::SetIntent('postAcademicyear', $result['year']);
        DATA::SetIntent('postDescription', $result['description']);
    }
    if (DATA::__HasIntentData('MODE')) {
        if (DATA::__GetIntentSecurely('MODE', TRUE, TRUE) == 'CANCEL')
        {
            DATA::DestroyIntents(FALSE, TRUE);
            UI::RedirectTo('admin-manage-schoolyears');
        }
    }
}
// removing year ranges which are already in database
if ($MODE=='ADD') {
    $sql = new DB();
    $sql->
            Select([ 'year' ])->
            From('sy');
    $result_Yearranges = $sql->Query();
    for ( $x=0; $x<count($result_Yearranges); $x++ ) 
    {
        $year = intval($result_Yearranges[$x]['year']);
        do {
            if ($year == intval(current($a_yearoptions))) {
                unset($a_yearoptions[key($a_yearoptions)]);
            }
        } while(next($a_yearoptions));
        reset($a_yearoptions);
    }
}

if (DATA::__HasPostData()) {
    $postAcademicyear = DATA::__GetPOST('postAcademicyear');
    $postDescription = DATA::__GetPOST('postDescription', true, true);
    // Getting info about this academicyear
    $AY_YEAR = 3000;
    if ($MODE == 'EDIT') {
        $sql = new DB();
        $TARGET_ID = DATA::__GetIntentSecurely('TARGET_ID', true, true);
        $AY_YEAR = $sql->Select(['year'])->From('sy')->Where('id='.$TARGET_ID)->Query()[0]['year'];
    }
    
    $sql = new DB();
    $sql->Select()
        ->From('sy')
        ->Where('year=' . $postAcademicyear);
    $is_exist = count($sql->Query()) > 0;
    if ($is_exist && $MODE =='ADD') {
        FLASH::addFlash('This academic year already exists. Duplicate AY entries are not allowed.', Index::__GetPage(), 'ERROR', true);
    }
    else {
        FLASH::addFlash('null', '', 'PROMPT', true);
    }
    
    if (FLASH::__getType()=='PROMPT')
    {
        $sy_count = 0;
        $sql = new DB();
        $sy_count = count($sql->Select()->From('sy')->Query());
        
        $sql = new DB();
        if ($MODE=='ADD') {
            $sql->InsertInto('sy(year, description, created, is_default)')
                ->Values(array(
                    $postAcademicyear, $postDescription, 'localtime()', ($sy_count > 0 ? 0 : 1)
                ), [ 1 ]);
            $sql->Execute();
            if ($sql->rows_affected > 0) {
                FLASH::addFlash('Academic year '.$postAcademicyear.'-'.($postAcademicyear+1).' has been successfully created', 'admin-manage-schoolyears', 'PROMPT', true);
            } 
            else {
                FLASH::addFlash('Database failure. Please try again or contact admin.', Index::__GetPage(), 'ERROR', true);
            }
        }
        else if ($MODE == 'EDIT') {
            $sql->
                    Update('sy')->
                    Set(array(
                        'description' => '"'.$postDescription.'"'
                    ))->
                    Where('id='.$TARGET_ID);
            
            $is_success = $sql->Execute()->__IsSuccess();
            if ($is_success) {
                FLASH::addFlash('Changes to academic year '.$AY_YEAR.'-'.($AY_YEAR+1).' has been saved!', 'admin-manage-schoolyears', 'PROMPT', true);
            }
            else {
                FLASH::addFlash('No changes has been made', 'admin-manage-schoolyears', 'PROMPT', true);
            }
        }
    } 
    if (FLASH::__getType()=='PROMPT') 
    {
        DATA::FullDestroyIntents();
        UI::RedirectTo('admin-manage-schoolyears');
    }
    else {
        DATA::openPassage(Index::__GetPage());
        DATA::SetIntent('postDescription', $postDescription);
        DATA::SetIntent('postAcademicyear', $postAcademicyear);
        UI::RefreshPage();
    }
} 
else if (DATA::__IsPassageOpen()) {
    $postAcademicyear = DATA::__GetIntent('postAcademicyear');
    $postDescription = DATA::__GetIntent('postDescription');
}



?>