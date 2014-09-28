<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ACCOUNTS
 *
 * @author Allen
 */
final class ACCOUNTS {

    /**
     * Authenticate login details and return login result
     * @param String $username Raw username
     * @param String $password Raw(unencrypted) password
     * @return Array(assoc)|int Assoc-array of results containing:<br>
     * <ul>
     * <li>'IS_SUCCESS'</li>
     * <li>'USERID'</li>
     * <li>'USERTYPE'</li>
     * <li>'USERNAME'</li>
     * </ul>
     * <br>
     * <b>But</b>, if the user is BLOCKED or PENDING, it returns the STATUS CODE (int)
     *      of the user.
     */
    public static function Authenticate($username, $password) {
        $result = array(
            'IS_SUCCESS' => false,
            'USERTYPE' => null,
            'USERNAME' => $username
        );
        
        # Encrypt first the credential
        $auth_password = ACCOUNTS::Encryptor($password, 'ENCRYPT');
        # Authenticate with Admin flatfile first
        $admincredentials = parse_ini_file(DIR::$CONFIG . 'admin.ini');
        if (strtolower($username) == strtolower($admincredentials['username']) && $auth_password == $admincredentials['password'] && $password == ACCOUNTS::Encryptor($admincredentials['password'], 'DECRYPT')) {
            $result['IS_SUCCESS'] = true;
            $result['USERTYPE'] = 'SUPERADMIN';
            $result['USERNAME'] = $admincredentials['username'];
        } else {
            # Else, authenticate with User database
            $db = new DB();
            $db->Select([
                'user.id',
                'user.status',
                'username',
                'password',
                'userpower.label'
                ])->
                    From('user, userpower')->
                    Where('username="' . $username . '" AND '
                            . 'password="' . $auth_password . '" AND '
                            . 'user.userpower_id=userpower.id');
            $dbresult = $db->Query();
            if (count($dbresult) > 0) {
                $dbresult = $dbresult[0];
                if ($dbresult['username'] == $username && $dbresult['password'] == $auth_password) {
                    $user_status = intval($dbresult['status']);
                    switch ($user_status) {
                        case 0: {
                            $result['IS_SUCCESS'] = true;
                            $result['USERID'] = $dbresult['id'];
                            $result['USERTYPE'] = strtoupper($dbresult['label']);
                            $result['USERNAME'] = $dbresult['username'];
                            break;
                        }
                        default: {
                            return $user_status;
                        }
                    }
                }
            } else {
                # If Authentication is OVERALL FAILURE
                return $result;
            }
        }
        # If Authentication is SUCCESS, log to the user credential session
        USER::Setmany(array(
            USER::ID => $result['USERID'],
            USER::USERNAME => $username,
            USER::PASSWORD => $auth_password,
            USER::TYPE => $result['USERTYPE']
        ));
        return $result;
    }

    /**
     * Create a user account entry (not including the profile)
     * @param Array(Assoc) $formdata The $_POST supplied
     * @return Boolean If success or not
     */
    public static function Create($formdata) {
        # Data preparation
        # -- Secure the contents of password
        $postPass1 = self::Encryptor(DATA::__GetPOST('postPass1'), "ENCRYPT");
        $postSecquestion = self::Encryptor(DATA::__GetPOST('postSecquestion', true, true), "ENCRYPT");
        $postSecanswer = self::Encryptor(DATA::__GetPOST('postSecanswer', true, true), "ENCRYPT");

        $mysql = new DB();
        $mysql->InsertInto('user', array(
                    'username', 'password', 'email', 'secquestion', 'secanswer', 'status', 'is_online', 'userpower_id'
                ))->Values(array(
                    '"' . strtolower(DATA::__GetPOST('postUsername', true, true)) . '"',
                    '"' . $postPass1 . '"',
                    '"' . strtolower(DATA::__GetPOST('postEmail', true, true)) . '"',
                    '"' . $postSecquestion . '"',
                    '"' . $postSecanswer . '"',
                    2,
                    0,
                    DATA::__GetPOST('postType')
                ));
        return $mysql->Execute()->rows_affected > 0;
    }

    /**
     * Create a user profile entry
     * @param Array(Assoc) $formdata The $_PO
     * @return type
     */
    public static function CreateProfile($formdata) {
        $pdosql = new DB();
        $result = $pdosql->Select(array('id'))
                ->From('user')
                ->Where('`username`="' . strtolower(DATA::__GetPOST('postUsername', true, true)) . '"')
                ->Query();
        if (count($result) <= 0) {
            return false;
        }

        # Data extraction
        $user_id = $result[0]['id'];
        $fname = ucwords(DATA::__ExtractPost($formdata, 'postFname', true, true, true));
        $mname = ucwords(DATA::__ExtractPost($formdata, 'postMname', true, true, true));
        $lname = ucwords(DATA::__ExtractPost($formdata, 'postLname', true, true, true));
        $gender = DATA::__ExtractPost($formdata, 'postGender', true, true, true);
        $address1 = DATA::__ExtractPost($formdata, 'postAddress1', true, true);
        $address2 = DATA::__ExtractPost($formdata, 'postAddress2', true, true);
        $city = DATA::__ExtractPost($formdata, 'postCity', true, true);
        $province = DATA::__ExtractPost($formdata, 'postProvince', true, true);
        $birthdate = 'STR_TO_DATE("' . DATA::__ExtractPost($formdata, 'postBirthday', true, true) . '", "%m/%d/%Y")';
        $mobile = DATA::__ExtractPost($formdata, 'postMobile', true, true, true);

        $success = $pdosql->InsertInto("profile", array(
                    'user_id', 'fname', 'mname', 'lname', 'gender', 'address1', 'address2', 'city', 'province', 'birthdate', 'mobile'
                ))->Values(array(
                    $user_id,
                    '"' . $fname . '"',
                    '"' . $mname . '"',
                    '"' . $lname . '"',
                    '"' . $gender . '"',
                    '"' . $address1 . '"',
                    '"' . $address2 . '"',
                    '"' . $city . '"',
                    '"' . $province . '"', $birthdate, '"' . $mobile . '"'
                ))->Execute()
                ->rows_affected > 0;
        return $success;
    }

    /**
     * Deletes a user account (including profile) from database
     * @param INT $user_id The ID of the user to be deleted
     * @return Boolean If deletion is success or not
     */
    public static function Delete($user_id) {
        $mysql = new DB();
        $success_profile = $mysql->DeleteFrom('profile')
                        ->Where('`user_id`=' . $user_id)
                        ->Execute()
                ->rows_affected > 0;
        if ($success_profile) {
            $mysql = new DB();
            $success_user = $mysql->DeleteFrom('user')
                            ->Where('`id`=' . $user_id)
                            ->Execute()
                    ->rows_affected > 0;
        }
        return $success_profile && $success_user;
    }

    /**
     * Encrypts or decrypts sensitive (binary) data
     * @param String $data The data to be processed
     * @param String $str_mode Choose: "ENCRYPT" or "DECRYPT"
     */
    public static function Encryptor($data, $str_mode) {
        $mode = strtoupper($str_mode);
        if ($mode == 'ENCRYPT') {
            $encrypted = base64_encode(
                    bin2hex(
                            strrev(
                                    base64_encode(
                                            bin2hex($data)))));
            $encrypted = str_replace('=', '@', $encrypted);
            return $encrypted;
        } else {
            $data = str_replace('@', '=', $data);
            $decrypted = hex2bin(base64_decode(strrev(hex2bin(base64_decode($data)))));
            return $decrypted;
        }
    }

    /**
     * Check for existence of user properties
     * @param Array(Assoc) $a_field_value Assoc-array containing >> [field] => [value]
     */
    public static function Exists($a_field_value) {
        $mysql = new DB();
        return $mysql->__checkPositive('user', $a_field_value);
    }

    /**
     * Very simple validation of a date value according to provided mask
     * @param String $datevalue The date value to be inspected.
     * @param String $formatmask Date format masking, %% for date or month, %%%% for year (e.g. %%/%%/%%%%)
     */
    public static function ValidateDate($datevalue, $formatmask) {
        $value = trim($datevalue);
        if (strlen($value) != strlen($formatmask)) {
            return false;
        }
        for ($x = 0; $x < strlen($datevalue); $x++) {
            if ($datevalue[$x] != $formatmask[$x]) {
                # FALSE if current char is not numeric but the corresponding mask is %
                if (!ctype_digit($datevalue[$x]) && $formatmask[$x] == '%') {
                    return false;
                }
            } else {
                # FALSE if current char is in format mask character (%)
                if ($datevalue[$x] == '%' && $formatmask[$x] == '%') {
                    return false;
                }
            }
        }
        return true;
    }

    # ACCOUNT DATA Fetching methods --------------------------------------------

    /**
     * Returns a QueryResult (Assoc-array) of Active users
     * @param Array $a_fields (Optional) Array of user fields to be selected
     * @param Boolean $is_includeprofile (Optional) Boolean value if profile
     *      of each user should also be included
     * @return Array(assoc)
     */
    public static function getActiveUsers($a_fields = array(), $is_includeprofile = false) {
        $db = new DB();
        $db->Select($a_fields)
                ->From('user' . ($is_includeprofile ? ', profile' : ''))
                ->Where('user.status = 0 AND '
                        . 'user.id=profile.user_id');
        return $db->Query();
    }

    /**
     * Returns a QueryResult (Assoc-array) of pending-for-signup users
     * @param Array(assoc) $a_fields (Optional) Userfields to be selected
     * @param Boolean $is_includeprofile (Optional) Boolean value if profile
     *      of each user should also be included
     * @return Array(assoc)
     */
    public static function getPendingUsers($a_fields = array(), $is_includeprofile = false) {
        $db = new DB();
        $db->Select($a_fields)
                ->From('user' . ($is_includeprofile ? ', profile' : ''))
                ->Where('status=2 AND '
                        . 'user.id = profile.user_id');
        return $db->Query();
    }

}
