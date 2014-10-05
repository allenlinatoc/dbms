<?php

$USER_ID = null;
$USER_INFOS = array();

// Declaration of FORM pre-defined values
$postGender = '';
$postBirthday = '';
$postEmail = '';
$postMobile = '';
$postAddress1 = '';
$postAddress2 = '';
$postProvince = '';

if (DATA::__IsPassageOpen())
{
    DATA::GenerateIntentsFromGET();
    # 
    # ---- filtered
    if (DATA::__HasIntentData('USER_ID'))
    {
        $USER_ID = DATA::__GetIntentSecurely('USER_ID');
        $sql = new DB();
        $sql->Select()
                ->From('user,profile,userpower')
                ->Where('user.id=profile.user_id '
                        . 'AND user.userpower_id=userpower.id '
                        . 'AND user.id='.$USER_ID);
        $USER_INFOS = $sql->Query()[0];
        
        $postGender = $USER_INFOS['gender'];
        $postBirthday = $USER_INFOS['birthdate'];
        $postBirthday = date_format(date_create($postBirthday), DATEMAN::DATE_FORMAT_JQUERY);
        $postEmail = $USER_INFOS['email'];
        $postMobile = $USER_INFOS['mobile'];
        $postAddress1 = $USER_INFOS['address1'];
        $postAddress2 = $USER_INFOS['address2'];
        $postCity = $USER_INFOS['city'];
        $postProvince = $USER_INFOS['province'];
    }
}

if ( DATA::__HasPostData() )
{
    $sql = new DB();
    $sql
            ->Update('user')
            ->Set(array(
                'email' => '"'.DATA::__GetPOST('postEmail', TRUE, TRUE).'"'
            ))
            ->Where('user.id='.USER::Get(USER::ID));
    $is_success = $sql->Execute()->__IsSuccess();
    if ($is_success) {
        $sql
                ->Update('profile')
                ->Set(array(
                    'gender' => '"'.DATA::__GetPOST('postGender', TRUE, TRUE, TRUE).'"',
                    'address1' => '"'.DATA::__GetPOST('postAddress1', true, true).'"',
                    'address2' => '"'.DATA::__GetPOST('postAddress2', true, true).'"',
                    'city' => '"'.DATA::__GetPOST('postCity', true, true).'"',
                    'province' => '"'.DATA::__GetPOST('postProvince', true, true).'"',
                    'birthdate' => '"'.DATA::__GetPOST('postBirthday', true, true).'"',
                    'mobile' => '"'.DATA::__GetPOST('postMobile', true, true).'"'
                ))
                ->Where('profile.user_id='.USER::Get(USER::ID));
        $is_success = $sql->Execute()->__IsSuccess();
    }
    
    if ($is_success)
    {
        FLASH::addFlash('Changes have been successfully saved', Index::__GetPage(), 'PROMPT', true);
    }
    else {
        FLASH::addFlash('Something went wrong. Geeks are on their way to fix it.', Index::__GetPage(), 'ERROR', true);
    }
}


$DEFAULT_SIZE = 32;


?>