<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */


if ( ! function_exists('get_user_role'))
{
	function get_user_role($type = "", $user_id = '') {
		$CI	=&	get_instance();
		$CI->load->database();

        $role_id	=	$CI->db->get_where('users' , array('id' => $user_id))->row()->role_id;
        $user_role	=	$CI->db->get_where('role' , array('id' => $role_id))->row()->name;

        if ($type == "user_role") {
            return $user_role;
        }else {
            return $role_id;
        }
	}
}


if ( ! function_exists('is_purchased'))
{
	function is_purchased($course_id = "", $user_id = "") {
		$CI	=&	get_instance();
		$CI->load->library('session');
		$CI->load->database();

		if (!$CI->session->userdata('user_login'))
			return false;

		if($user_id == "")
			$user_id = $CI->session->userdata('user_id');

		$enrolled_history = $CI->db->get_where('enrol' , ['user_id' => $user_id, 'course_id' => $course_id]);
		if ($enrolled_history->num_rows() > 0) {
			$expiry_date = $enrolled_history->row('expiry_date');
			if($expiry_date == null || $expiry_date >= time()){
				return true;
			}else{
				return false;
			}
		}else {
			return false;
		}
	}
}
if ( ! function_exists('enroll_status'))
{
	function enroll_status($course_id = "", $user_id = "") {
		$CI	=&	get_instance();
		$CI->load->library('session');
		$CI->load->database();


		if($user_id == "")
			$user_id = $CI->session->userdata('user_id');


		$enrolled_history = $CI->db->get_where('enrol' , ['user_id' => $user_id, 'course_id' => $course_id]);
		if ($enrolled_history->num_rows() > 0) {
			$expiry_date = $enrolled_history->row('expiry_date');
			if($expiry_date == null || $expiry_date >= time()){
				return 'valid';
			}else{
				return 'expired';
			}
		}else {
			return false;
		}
	}
}
if ( ! function_exists('is_pharmacist_licence_missing'))
{
	function is_pharmacist_licence_missing($user_id = null) {
		$CI	=&	get_instance();
		$CI->load->library('session');
		$CI->load->database();

		if ($CI->session->userdata('user_login') != 1) {
			return false;
		}

		if ($user_id === null) {
			$user_id = $CI->session->userdata('user_id');
		}

		if (!$user_id) {
			return false;
		}

		$user = $CI->db->select('role_id, licence_no')->where('id', $user_id)->get('users')->row_array();
		if ($user && $user['role_id'] == 2) {
			$licence_no = trim($user['licence_no'] ?? '');
			return empty($licence_no);
		}
		return false;
	}
}

// ------------------------------------------------------------------------
/* End of file user_helper.php */
/* Location: ./system/helpers/user_helper.php */
