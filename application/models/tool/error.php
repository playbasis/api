<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Error extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => CACHE_ADAPTER));
    }
    public function setError($code, $dataArray = array())
    {
        $errorData = array();
        $errorData['success'] = false;
        $errorData['response'] = array();
        $errorData['error_code'] = '';
        switch($code)
        {
        case 'INVALID_TOKEN':
            $errorData['message'] = "Invalid token Key";
            $errorData['error_code'] = '0900';
            break;
        case 'REQUEST_EXCEEDED':
            $errorData['message'] = "Request exceeded , Too much request";
            $errorData['error_code'] = '0901';
            break;
        case 'TOKEN_REQUIRED':
            $errorData['message'] = "Token key required";
            $errorData['error_code'] = '0902';
            break;
        case 'PARAMETER_MISSING':
            if(is_array($dataArray)) // array
            {
                $errorData['message'] = "Invalid parameter , [ " . implode(' , ', $dataArray) . " ] require , must not be blank or special character";
            }
            else if (!empty($dataArray)) { // str
                $errorData['message'] = "Invalid parameter , [ " .$dataArray. " ] require , must not be blank or special character";
            } else
            {
                $errorData['message'] = "Invalid parameter , must not be blank and special character";
            }
            $errorData['error_code'] = '0903';
            break;
        case 'INTERNAL_ERROR':
            $errorData['message'] = "There is an internal server error: ".print_r($dataArray,true);
            $errorData['error_code'] = '0800';
            break;
        case 'CANNOT_SEND_EMAIL':
            $errorData['message'] = "Email error, cannot send email to: ".$dataArray;
            $errorData['error_code'] = '0801';
            break;
        case 'ALL_EMAILS_IN_BLACKLIST':
            $errorData['message'] = "Email error, all designated recipients are in black list: ".$dataArray;
            $errorData['error_code'] = '0802';
            break;
        case 'EMAIL_ALREADY_IN_BLACKLIST':
            $errorData['message'] = "Email is already in black list: ".$dataArray;
            $errorData['error_code'] = '0803';
            break;
        case 'EMAIL_NOT_IN_BLACKLIST':
            $errorData['message'] = "Email is not in black list: ".$dataArray;
            $errorData['error_code'] = '0804';
            break;
        case 'UNKNOWN_SNS_MESSAGE_TYPE':
            $errorData['message'] = "This Amazon SNS message type is not supported: ".$dataArray;
            $errorData['error_code'] = '0805';
            break;
        case 'UNKNOWN_NOTIFICATION_MESSAGE':
            $errorData['message'] = "Unknown notification message";
            $errorData['error_code'] = '0806';
            break;
        case 'CANNOT_VERIFY_PAYPAL_IPN':
            $errorData['message'] = "Cannot verify the authenticity of PayPal IPN message: ".$dataArray;
            $errorData['error_code'] = '0807';
            break;
        case 'INVALID_PAYPAL_IPN':
            $errorData['message'] = "Invalid PayPal IPN";
            $errorData['error_code'] = '0808';
            break;
        case 'TEMPLATE_NOT_FOUND':
            $errorData['message'] = "Template cannot be found: ".$dataArray;
            $errorData['error_code'] = '0809';
            break;
        case 'INVALID_JIVE_TENANT_ID':
            $errorData['message'] = "Invalid Jive tenant ID";
            $errorData['error_code'] = '0810';
            break;
        case 'INVALID_JIVE_MESSAGE':
            $errorData['message'] = "Invalid Jive message";
            $errorData['error_code'] = '0811';
            break;
        case 'LITHIUM_RECORD_NOT_FOUND':
            $errorData['message'] = "Lithium record is not found";
            $errorData['error_code'] = '0812';
            break;
        case 'LITHIUM_SUBSCRIPTION_RECORD_NOT_FOUND':
            $errorData['message'] = "Lithium subscription record is not found";
            $errorData['error_code'] = '0813';
            break;
        case 'INVALID_API_KEY_OR_SECRET':
            $errorData['message'] = "Invalid API-KEY OR API-SECRET";
            $errorData['error_code'] = '0001';
            break;
        case 'ACCESS_DENIED':
            $errorData['message'] = "Can\'t Access ,Permission Denied";
            $errorData['error_code'] = '0002';
            break;
        case 'LIMIT_EXCEED':
            $errorData['message'] = "Limit Exceed, Contact Admin";
            $errorData['error_code'] = '0003';
            break;
        case 'SETUP_MOBILE':
            $errorData['message'] = "Please set up mobile phone on dashboard";
            $errorData['error_code'] = '0004';
            break;
        case 'NOT_IMPLEMENTED':
            $errorData['message'] = "There is no implementation for the requested service";
            $errorData['error_code'] = '0005';
            break;
        case 'UNSUPPORTED_RESOURCE_STATE':
            $errorData['message'] = "Unsupported resource state";
            $errorData['error_code'] = '0006';
            break;
        case 'NOT_SETUP_GOOGLE':
            $errorData['message'] = "Google API setup cannot be found";
            $errorData['error_code'] = '0007';
            break;
        case 'NOT_TOKEN_GOOGLE':
            $errorData['message'] = "Google API access token cannot be found";
            $errorData['error_code'] = '0008';
            break;
        case 'NOT_SUPPORTED_GOOGLE_SERVICE':
            $errorData['message'] = "This Google API is not supported";
            $errorData['error_code'] = '0009';
            break;
        case 'MISSING_STRIPE_EVENT_ID':
            $errorData['message'] = "Missing required Stripe event Id";
            $errorData['error_code'] = '0010';
            break;
        case 'INVALID_STRIPE_EVENT':
            $errorData['message'] = "Invalid Stripe event";
            $errorData['error_code'] = '0011';
            break;
        case 'DUPLICATED_STRIPE_EVENT':
            $errorData['message'] = "Duplicated Stripe event";
            $errorData['error_code'] = '0014';
            break;
        case 'CANNOT_FIND_STRIPE_ID':
            $errorData['message'] = "Cannot find Stripe customer ID";
            $errorData['error_code'] = '0012';
            break;
        case 'CANNOT_FIND_CLIENT_ID':
            $errorData['message'] = "Cannot find client ID";
            $errorData['error_code'] = '0013';
            break;
        case 'CLIENTSITE_NOTFOUND':
            $errorData['message'] = "Cannot find client ID and site ID in playbasis_permission";
            $errorData['error_code'] = '0014';
            break;
        case 'USER_NOT_EXIST':
            $errorData['message'] = "User doesn't exist";
            $errorData['error_code'] = '0200';
            break;
        case 'USER_ALREADY_EXIST':
            $errorData['message'] = "User already exist";
            $errorData['error_code'] = '0201';
            break;
        case 'TOO_MANY_USERS':
            $errorData['message'] = "User registration limit exceed";
            $errorData['error_code'] = '0202';
            break;
        case "USER_OR_REWARD_NOT_EXIST":
            $errorData["message"] = "The user or reward type does not exist";
            $errorData["error_code"] = "0203";
            break;
        case "USER_ID_INVALID":
            $errorData["message"] = "cl_player_id format should be 0-9a-zA-Z_-";
            $errorData["error_code"] = "0204";
            break;
        case "USER_PHONE_INVALID":
            $errorData["message"] = "phone number format should be +[countrycode][number] example. +66861234567";
            $errorData["error_code"] = "0205";
            break;
        case "REWARD_FOR_USER_NOT_EXIST":
            $errorData["message"] = "The user has no such reward";
            $errorData["error_code"] = "0206";
            break;
        case "REWARD_FOR_USER_NOT_ENOUGH":
            $errorData["message"] = "The user has not enough reward";
            $errorData["error_code"] = "0207";
            break;
        case 'EVENT_NOT_EXIST':
            $errorData['message'] = "Event doesn't exist";
            $errorData['error_code'] = '0208';
            break;
        case 'SESSION_NOT_VALID':
            $errorData['message'] = "Session is invalid";
            $errorData['error_code'] = '0209';
            break;
        case 'PASSWORD_INCORRECT':
            $errorData['message'] = "Password incorrect";
            $errorData['error_code'] = '0210';
            break;
        case 'SMS_VERIFICATION_REQUIRED':
            $errorData['message'] = "SMS verification is required to proceed further";
            $errorData['error_code'] = '0211';
            break;
        case 'SMS_VERIFICATION_CODE_INVALID':
            $errorData['message'] = "SMS Verification code is invalid";
            $errorData['error_code'] = '0212';
            break;
        case 'SMS_VERIFICATION_CODE_EXPIRED':
            $errorData['message'] = "SMS Verification code is expired";
            $errorData['error_code'] = '0213';
            break;
        case 'SMS_VERIFICATION_PHONE_NUMBER_NOT_FOUND':
            $errorData['message'] = "Phone number is not found.";
            $errorData['error_code'] = '0214';
            break;
        case 'ACTION_NOT_FOUND':
            $errorData['message'] = "Action not available";
            $errorData['error_code'] = '0301';
            break;
        case 'REWARD_NOT_FOUND':
            $errorData['message'] = "Reward not available";
            $errorData['error_code'] = '0401';
            break;
        case 'GOODS_NOT_FOUND':
            $errorData['message'] = "Goods not available";
            $errorData['error_code'] = '0501';
            break;
        case 'OVER_LIMIT_REDEEM':
            $errorData['message'] = "User has exceeded redeem limit";
            $errorData['error_code'] = '0601';
            break;
        case "QUEST_JOINED":
            $errorData["message"] = "User has already join this quest";
            $errorData["error_code"] = "0701";
            break;
        case "QUEST_FINISHED":
            $errorData["message"] = "User has finished this quest";
            $errorData["error_code"] = "0702";
            break;
        case "QUEST_CONDITION":
            $errorData["message"] = "User has no permission to join this quest";
            $errorData["error_code"] = "0703";
            break;
        case "QUEST_CANCEL_FAILED":
            $errorData["message"] = "User has not yet join this quest";
            $errorData["error_code"] = "0704";
            break;
        case "QUEST_JOIN_OR_CANCEL_NOTFOUND":
            $errorData["message"] = "Quest not found";
            $errorData["error_code"] = "0705";
            break;
        case 'QUIZ_NOT_FOUND':
            $errorData['message'] = "Quiz not found";
            $errorData['error_code'] = '1001';
            break;
        case 'QUIZ_QUESTION_NOT_FOUND':
            $errorData['message'] = "Question not found";
            $errorData['error_code'] = '1002';
            break;
        case 'QUIZ_OPTION_NOT_FOUND':
            $errorData['message'] = "Option not found";
            $errorData['error_code'] = '1003';
            break;
        case 'QUIZ_QUESTION_ALREADY_COMPLETED':
            $errorData['message'] = "Question has already been completed by the player";
            $errorData['error_code'] = '1004';
            break;
        case 'RULE_NOT_FOUND':
            $errorData['message'] = "Rule not available";
            $errorData['error_code'] = '1101';
            break;
        case 'PROMO_CONTENT_NOT_FOUND':
            $errorData['message'] = "Promotional Content not found";
            $errorData['error_code'] = '2001';
            break;
        case 'PIN_CODE_INVALID':
            $errorData['message'] = "PIN code is invalid";
            $errorData['error_code'] = '2101';
            break;
        case 'REFERRAL_CODE_INVALID':
            $errorData['message'] = "Referral code is invalid";
            $errorData['error_code'] = '2201';
            break;
        case 'ANONYMOUS_NOT_FOUND':
            $errorData['message'] = "Anonymous not available";
            $errorData['error_code'] = '1102';
            break;
        case 'ANONYMOUS_SESSION_NOT_VALID':
            $errorData['message'] = "Anonymous session not valid";
            $errorData['error_code'] = '1103';
            break;
        case 'ANONYMOUS_CANNOT_REFERRAL':
            $errorData['message'] = "Anonymous cannot use referral code";
            $errorData['error_code'] = '1104';
            break;
        case 'REFERENCE_ID_INVALID':
                $errorData['message'] = "Reference id is invalid";
                $errorData['error_code'] = '2301';
                break;
        case 'PARAMETER_INVALID':
                $errorData['message'] = "Parameter is invalid";
                $errorData['error_code'] = '2302';
                break;
        case 'STORE_ORG_NODE_NOT_FOUND':
            $errorData['message'] = "Node value is not found";
            $errorData['error_code'] = '2401';
            break;
        case 'STORE_ORG_PLAYER_ALREADY_EXISTS_WITH_NODE':
            $errorData['message'] = "Player already exists with current node";
            $errorData['error_code'] = '2402';
            break;
        case 'STORE_ORG_PLAYER_NOT_EXISTS_WITH_NODE':
            $errorData['message'] = "Player is not exists with current node";
            $errorData['error_code'] = '2403';
            break;
        case 'STORE_ORG_PLAYER_ROLE_ALREADY_EXISTS':
            $errorData['message'] = "This role already exists for this Player";
            $errorData['error_code'] = '2404';
            break;
        case 'STORE_ORG_PLAYER_ROLE_NOT_EXISTS':
            $errorData['message'] = "This role is not set for this Player";
            $errorData['error_code'] = '2405';
            break;
        default:
            $errorData['message'] = "Unknown";
            $errorData['error_code'] = '9999';
            break;
        }
        $errorData['timestamp'] = (int) time();
        $errorData['time'] = date('r e');
        $version = $this->cache->get(CACHE_KEY_VERSION);
        if ($version === false) {
            $str = @file_get_contents('./pom.xml');
            $xml = new SimpleXMLElement($str);
            $version = (string) $xml->version;
            $obj = explode('-', $version);
            $version = $obj[0];
            $this->cache->save(CACHE_KEY_VERSION, $version, CACHE_TTL_IN_SEC);
        }
        $errorData['version'] = $version;
        return $errorData;
    }
}
?>
