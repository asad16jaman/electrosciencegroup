<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        $ip_address = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // Check IP from shared internet
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $ip_address == '') {
            // Check IP from proxy
            $ip_address = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            // Get IP address from remote address
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        // Return the IP address
        return $ip_address;
    }
}

if (!function_exists('buttonAction')) {
    function buttonAction($action)
    {
        $CI = &get_instance();
        $userId = $CI->session->userdata('userId');
        $user = $CI->db->get_where('tbl_user', ['User_SlNo' => $userId])->row();
        if ($user->UserType == 'e' || $user->UserType == 'u') {
            $buttonAction = $user->action ? json_decode($user->action) : [];
            if (in_array($action, $buttonAction)) {
                return true;
            }
            return false;
        }

        return true;
    }
}
