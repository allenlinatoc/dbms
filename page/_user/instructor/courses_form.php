<?php
$FORM_MODE = 'ADD';
if (DATA::__IsPassageOpen(true)) {
    DATA::GenerateIntentsFromGET(['TARGET_ID']);
    # Filtered
    $FORM_MODE = 'EDIT';
    $postId = DATA::__GetIntent('TARGET_ID');
}
$postId = null;
$postCode = '';
$postName = '';
$postDescription = '';
$postTeacherId = USER::Get(USER::ID);

if (DATA::__HasPostData(['postName', 'postDescription'])) {
    # on( DATA POSTING )
    $postName = DATA::__GetPOST('postName', true, true);
    $postDescription = DATA::__GetPOST('postDescription', true, true);
    if ($FORM_MODE=='ADD') {
        // Generate new course code
        $nextindex = DB::__getFirstIndexval('course', 'id', 1);
        $postCode = CODES::CreateHashed($nextindex, 10);
        STR::InsertAt($postCode, '-', strlen($postCode)/2);

        // Database transaction
        $sql = new DB();
        $sql->InsertInto('course', [
            'id', 'code', 'name', 'description', 'teacher_id', 'created'
        ])->Values([
            $nextindex,
            $postCode,
            $postName,
            $postDescription,
            $postTeacherId,
            'localtime()'
        ], [ 1, 2, 3 ]);
        $success = $sql->Execute()->__IsSuccess();
        if ($success) {
            FLASH::addFlash('Course <i>' . $postName . '</i> has been successfully created', 
                    ['home', 'admin-home', 'instructor-courses-form', 'instructor-courses'], 'PROMPT', true);
            UI::RedirectTo('instructor-courses');
        } else {
            die($sql->query);
            FLASH::addFlash('Failed to add the course <b>' . $postName . '</b>. If error persists, please contact the admin.',
                    ['home', 'admin-home', 'instructor-courses-form', 'instructor-courses'], 'ERROR', true);
            UI::RedirectTo(Index::__GetPage());
        }
    } else if ($FORM_MODE=='EDIT') {
        
    }
    
} else if ($FORM_MODE=='EDIT') {
    # on( EDIT MODE )
    
}
?>