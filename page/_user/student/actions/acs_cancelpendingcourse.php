<?php

if (DATA::__IsPassageOpen()) {
    DATA::GenerateIntentsFromGET();
    # ---- Filtered
    $ENTRY_ID = DATA::__GetIntent('TARGET_ENTRY_ID');
    $sql = new DB();
    $sql->Select()
        ->From('d_student_course')
        ->Where('id=' . $ENTRY_ID);
    $result = $sql->Query();
    if (count($result) == 0) {
        FLASH::addFlash('Supplied Course entry ID does not exist.', ['user-home'], 'ERROR', true);
        UI::RedirectTo('user-home');
    }
    
    # If it exists, delete it from entries
    $result = $result[0];
    $sql = new DB();
    $sql->DeleteFrom('d_student_course')
        ->Where('id=' . $ENTRY_ID);
    $rowsaffected = $sql->Execute()->rows_affected;
    if ($rowsaffected == 0) {
        FLASH::addFlash('Failed to delete a Course enrollment request entry. Please contact admin for further assistance.',
            ['student-mycourses'], 'ERROR', true);
        UI::RedirectTo('student-mycourses');
    }
    
    # If Deletion of request is success
    # ---- Fetch COURSE_NAME where the said deleted Course entry is associated
    $sql = new DB();
    $sql->Select(['name'])
        ->From('course')
        ->Where('id=' . $result['course_id']);
    $result_course = $sql->Query()[0];
    FLASH::addFlash('Course enrollment request entry for ' . $result_course['name'] . ' has been cancelled.',
            'student-mycourses', 'PROMPT', true);
    UI::RedirectTo('student-mycourses');
}

?>