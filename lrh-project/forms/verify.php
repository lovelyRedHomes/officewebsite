<?php 
// Include configuration file 
include_once 'config.php'; 
 
function sendSMS($mobile_no, $message){ 
    // Request parameters array  
    $requestParams = array(  
        'api_key' => API_KEY,  
        'sender_id' => SENDER_ID,  
        'receipient_no' => COUNTRY_CODE.$mobile_no, 
        'message' => $message 
    );  
     
    // Append parameters to API URL  
    $apiURL = API_URL.'?';  
    foreach($requestParams as $key => $val){  
        $apiURL .= $key.'='.urlencode($val).'&';  
    }  
    $apiURL = rtrim($apiURL, "&");  
     
    // Send the GET request with cURL  
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, $apiURL);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    $response = curl_exec($ch);  
    curl_close($ch);  
     
    // Return API response 
    return $response; 
} 
 
// Load and initialize database class 
require_once 'DB.class.php'; 
$db = new DB(); 
         
$statusMsg = $receipient_no = ''; 
$otpDisplay = $verified = 0; 
 
// If mobile number submitted by the user 
if(isset($_POST['submit_mobile']) || isset($_POST['resend_otp'])){ 
    // Recipient mobile number 
    $recipient_no = $_POST['mobile_no']; 
 
    if(!empty($recipient_no) && preg_match('/^[0-9]{10}+$/', $recipient_no)){ 
        // Generate 6 digits random verification code 
        $rand_no = rand(100000, 999999); 
         
        // Check previous entry 
        $conditions = array( 
            'mobile_number' => $recipient_no, 
        ); 
        $checkPrev = $db->checkRow($conditions); 
         
        // Insert or update otp in the database 
        if($checkPrev){ 
            $otpData = array( 
                'verification_code' => $rand_no 
            ); 
            $insert = $db->update($otpData, $conditions); 
        }else{ 
            $otpData = array( 
                'mobile_number' => $recipient_no, 
                'verification_code' => $rand_no, 
                'verified' => 0 
            ); 
            $insert = $db->insert($otpData); 
        } 
         
        if($insert){ 
            // Send otp to user via SMS 
            $message = 'Dear User, OTP for mobile number verification is '.$rand_no.'. Thanks CODEXWORLD'; 
            $send = sendSMS($recipient_no, $message); 
            if($send){ 
                $otpDisplay = 1; 
                $statusMsg = array( 
                    'status' => 'success', 
                    'msg' => "OTP has been successfully sent to your mobile no." 
                ); 
            }else{ 
                $statusMsg = array( 
                    'status' => 'error', 
                    'msg' => "We're facing some issues with sending SMS, please try again." 
                ); 
            } 
        }else{ 
            $statusMsg = array( 
                'status' => 'error', 
                'msg' => 'Something went wrong, please try again.' 
            ); 
        } 
    }else{ 
        $statusMsg = array( 
            'status' => 'error', 
            'msg' => 'Please enter a valid mobile number.' 
        ); 
    } 
// If verification code submitted by the user 
}elseif(isset($_POST['submit_otp']) && !empty($_POST['mobile_no'])){ 
    $otpDisplay = 1; 
    $recipient_no = $_POST['mobile_no']; 
    $otp_code = $_POST['otp_code']; 
 
    if(!empty($otp_code)){ 
        // Verify otp code 
        $conditions = array( 
            'mobile_number' => $recipient_no, 
            'verification_code' => $otp_code 
        ); 
        $check = $db->checkRow($conditions); 
         
        if($check){ 
            $otpData = array( 
                'verified' => 1 
            ); 
            $update = $db->update($otpData, $conditions); 
             
            $statusMsg = array( 
                'status' => 'success', 
                'msg' => 'Thank you! Your phone number has been verified successfully.' 
            ); 
             
            $verified = 1; 
        }else{ 
            $statusMsg = array( 
                'status' => 'error', 
                'msg' => 'Given verification code is incorrect, please try again.' 
            ); 
        } 
    }else{ 
        $statusMsg = array( 
            'status' => 'error', 
            'msg' => 'Please enter the verification code.' 
        ); 
    } 
}