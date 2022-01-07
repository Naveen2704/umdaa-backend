<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

// include "phpqrcode/qrlib.php";
if ( ! function_exists('is_logged_in'))

{

    function is_logged_in()
    {
        $CI =& get_instance();

        $user = $CI->session->userdata('is_logged_in');

        if (!isset($user)) { return false; } else { return true; }

    }

    // Invoice Generation
    function generateInvoice($billing_type, $clinic_id) {
        $CI =& get_instance();
        $billInfo = $CI->db->query("select billing_id from billing where clinic_id='".$clinic_id."' and billing_type='".$billing_type."' order by billing_id DESC")->row();
        if(is_null($billInfo)) {
            return "INV-".substr(strtoupper($billing_type), 0, 3).date("ymd")."1";
        }
        else {
            return "INV-".substr(strtoupper($billing_type), 0, 3).date("ymd").($billInfo->billing_id + 1);
        }
    }

    //Encryption And Decryption
    function DataCrypt($string, $action)
    {

        $CI = &get_instance();

        $secret_key = 'Goldfish';
        $secret_iv = 'UMDAA';

        $output = "";
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($string == "") {
            return null;
        } else {
            if ($action == 'encrypt') {
                $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
            } else if ($action == 'decrypt') {
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }

            return $output;
        }
    }
    
    // get patient name from appointment_id
    function getPatientName($patient_id)
    {
        $CI = &get_instance();
        $patientInfo = $CI->db->query("select title,first_name,last_name from patients where patient_id='" . $patient_id . "'")->row();
        $title = $patientInfo->title;
        if ($title == "") {
            $fullname = $patientInfo->first_name . " " . $patientInfo->last_name;
        } else {
            $fullname = $title . ". " . $patientInfo->first_name . " " . $patientInfo->last_name;
        }
        return $fullname;
    }

    function getDoctorName($doctor_id)
    {
        $CI = &get_instance();
        $docInfo = $CI->db->query("select first_name,last_name from doctors where doctor_id='" . $doctor_id . "'")->row();
        $fullname = "Dr. " . $docInfo->first_name . " " . $docInfo->last_name;
        return $fullname;
    }

}

?>