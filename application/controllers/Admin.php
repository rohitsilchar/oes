<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        date_default_timezone_set(get_settings('timezone'));

        $this->load->database();
        $this->load->library('session');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        $this->user_model->check_session_data('admin');

        ini_set('memory_limit', '128M');
    }

    public function index()
    {
        if ($this->session->userdata('admin_login') == true) {
            $this->dashboard();
        } else {
            redirect(site_url('login'), 'refresh');
        }
    }

    public function dashboard()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // Pharmacist counts & license stats
        $total_pharmacists = $this->db->where('role_id', 2)->where('is_instructor', 0)->count_all_results('users');
        $licensed_pharmacists = $this->db->where('role_id', 2)->where('is_instructor', 0)->where("licence_no IS NOT NULL AND licence_no != ''", null, false)->count_all_results('users');
        $total_instructors = $this->db->where('is_instructor', 1)->count_all_results('users');

        // Stores count & staffing overview
        $total_stores = $this->db->table_exists('stores') ? $this->db->count_all_results('stores') : 0;
        $stores_list = [];
        $unstaffed_stores = [];
        $store_distribution = [];
        if ($this->db->table_exists('stores')) {
            // Unstaffed stores calculated via efficient join
            $unstaffed_query = $this->db->query("SELECT stores.id, stores.store_name, stores.store_code, stores.state, stores.city FROM stores LEFT JOIN users ON users.store_id = stores.id AND users.role_id = 2 WHERE users.id IS NULL GROUP BY stores.id ORDER BY stores.id DESC");
            $unstaffed_stores = $unstaffed_query ? $unstaffed_query->result_array() : [];

            // 10 Recent stores for Operational Command Hub
            $this->db->select('stores.*, (SELECT COUNT(*) FROM users WHERE users.store_id = stores.id AND users.role_id = 2) as pharmacist_count');
            $this->db->from('stores');
            $this->db->order_by('stores.id', 'DESC');
            $this->db->limit(10);
            $stores_list = $this->db->get()->result_array();

            $store_distribution = $stores_list;
        }

        // Course counts
        $status_wise_courses = $this->crud_model->get_status_wise_courses();
        $active_courses_count = isset($status_wise_courses['active']) ? $status_wise_courses['active']->num_rows() : 0;
        $pending_courses_count = isset($status_wise_courses['pending']) ? $status_wise_courses['pending']->num_rows() : 0;
        $total_courses = $active_courses_count + $pending_courses_count;

        // Enrollments
        $total_enrollments = $this->db->count_all_results('enrol');
        $month_start_ts = strtotime(date('Y-m-01 00:00:00'));
        $month_enrollments = $this->db->where('date_added >=', $month_start_ts)->count_all_results('enrol');

        // Recent enrollments with user, course, and store details (10 recent)
        $this->db->select('enrol.id as enrol_id, enrol.date_added as enrol_date, enrol.expiry_date, users.id as user_id, users.first_name, users.last_name, users.email, users.employee_id, users.licence_no, users.image, course.id as course_id, course.title as course_title, course.thumbnail as course_thumbnail, stores.store_name, stores.store_code');
        $this->db->from('enrol');
        $this->db->join('users', 'users.id = enrol.user_id', 'left');
        $this->db->join('course', 'course.id = enrol.course_id', 'left');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->order_by('enrol.id', 'DESC');
        $this->db->limit(10);
        $recent_enrollments = $this->db->get()->result_array();

        // Non-compliant / Unlicensed Pharmacists needing immediate action (10 recent)
        $this->db->select('users.id, users.first_name, users.last_name, users.email, users.employee_id, users.phone, users.image, stores.store_name, stores.store_code');
        $this->db->from('users');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);
        $this->db->where('users.is_instructor', 0);
        $this->db->where("(users.licence_no IS NULL OR users.licence_no = '')", null, false);
        $this->db->order_by('users.id', 'DESC');
        $this->db->limit(10);
        $unlicensed_pharmacists = $this->db->get()->result_array();

        // Top popular courses (top 10)
        $this->db->select('course.id, course.title, course.thumbnail, course.status, (SELECT COUNT(*) FROM enrol WHERE enrol.course_id = course.id) as enrol_count');
        $this->db->from('course');
        $this->db->order_by('enrol_count', 'DESC');
        $this->db->limit(10);
        $top_courses = $this->db->get()->result_array();

        // Assigned vs Unassigned Pharmacists
        $assigned_pharmacists = $this->db->where('role_id', 2)->where('is_instructor', 0)->where('store_id >', 0)->count_all_results('users');
        $unassigned_pharmacists = max(0, $total_pharmacists - $assigned_pharmacists);

        // Active vs Inactive Pharmacists
        $active_pharmacists = $this->db->where('role_id', 2)->where('is_instructor', 0)->where('status', 1)->count_all_results('users');
        $inactive_pharmacists = max(0, $total_pharmacists - $active_pharmacists);

        $page_data['page_name']              = 'dashboard';
        $page_data['page_title']             = get_phrase('dashboard');
        $page_data['total_pharmacists']      = $total_pharmacists;
        $page_data['licensed_pharmacists']   = $licensed_pharmacists;
        $page_data['total_instructors']      = $total_instructors;
        $page_data['total_stores']           = $total_stores;
        $page_data['active_courses_count']   = $active_courses_count;
        $page_data['pending_courses_count']  = $pending_courses_count;
        $page_data['total_courses']          = $total_courses;
        $page_data['total_enrollments']      = $total_enrollments;
        $page_data['month_enrollments']      = $month_enrollments;
        $page_data['recent_enrollments']     = $recent_enrollments;
        $page_data['stores_list']            = $stores_list;
        $page_data['unstaffed_stores']       = $unstaffed_stores;
        $page_data['unlicensed_pharmacists'] = $unlicensed_pharmacists;
        $page_data['top_courses']            = $top_courses;
        $page_data['assigned_pharmacists']   = $assigned_pharmacists;
        $page_data['unassigned_pharmacists'] = $unassigned_pharmacists;
        $page_data['active_pharmacists']     = $active_pharmacists;
        $page_data['inactive_pharmacists']   = $inactive_pharmacists;
        $page_data['store_distribution']     = $store_distribution;

        $this->load->view('backend/index.php', $page_data);
    }

    public function categories($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('category');

        if ($param1 == 'add') {

            $response = $this->crud_model->add_category();
            if ($response) {
                $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            } else {
                $this->session->set_flashdata('error_message', get_phrase('category_name_already_exists'));
            }
            redirect(site_url('admin/categories'), 'refresh');
        } elseif ($param1 == "edit") {

            $response = $this->crud_model->edit_category($param2);
            if ($response) {
                $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
            } else {
                $this->session->set_flashdata('error_message', get_phrase('category_name_already_exists'));
            }
            redirect(site_url('admin/categories'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->crud_model->delete_category($param2);
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(site_url('admin/categories'), 'refresh');
        } elseif ($param1 == "sub_category_image") {
            $this->crud_model->delete_subcategory_image($param2);
            $this->session->set_flashdata('flash_message', get_phrase('data_deleted'));
            redirect(site_url('admin/categories'), 'refresh');
        }

        $page_data['page_name']  = 'categories';
        $page_data['page_title'] = get_phrase('categories');
        $page_data['categories'] = $this->crud_model->get_categories($param2);
        $this->load->view('backend/index', $page_data);
    }

    public function category_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('category');

        if ($param1 == "add_category") {

            $page_data['page_name']  = 'category_add';
            $page_data['categories'] = $this->crud_model->get_categories()->result_array();
            $page_data['page_title'] = get_phrase('add_category');
        }
        if ($param1 == "edit_category") {

            $page_data['page_name']   = 'category_edit';
            $page_data['page_title']  = get_phrase('edit_category');
            $page_data['categories']  = $this->crud_model->get_categories()->result_array();
            $page_data['category_id'] = $param2;
        }

        $this->load->view('backend/index', $page_data);
    }

    public function sub_categories_by_category_id($category_id = 0)
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $category_id = $this->input->post('category_id');
        redirect(site_url("admin/sub_categories/$category_id"), 'refresh');
    }

    public function sub_category_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('category');

        if ($param1 == 'add_sub_category') {
            $page_data['page_name']  = 'sub_category_add';
            $page_data['page_title'] = get_phrase('add_sub_category');
        } elseif ($param1 == 'edit_sub_category') {
            $page_data['page_name']       = 'sub_category_edit';
            $page_data['page_title']      = get_phrase('edit_sub_category');
            $page_data['sub_category_id'] = $param2;
        }
        $page_data['categories'] = $this->crud_model->get_categories();
        $this->load->view('backend/index', $page_data);
    }

    public function instructors($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('instructor');

        if ($param1 == "add") {
            $this->user_model->add_user(true); // PROVIDING TRUE FOR INSTRUCTOR
            redirect(site_url('admin/instructors'), 'refresh');
        } elseif ($param1 == "edit") {
            $this->user_model->edit_user($param2);
            redirect(site_url('admin/instructors'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->user_model->delete_user($param2);
            redirect(site_url('admin/instructors'), 'refresh');
        }

        $page_data['page_name']  = 'instructors';
        $page_data['page_title'] = get_phrase('instructor');
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('instructor');

        if ($param1 == 'add_instructor_form') {
            $page_data['page_name']  = 'instructor_add';
            $page_data['page_title'] = get_phrase('instructor_add');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_instructor_form') {
            $page_data['page_name']  = 'instructor_edit';
            $page_data['user_id']    = $param2;
            $page_data['page_title'] = get_phrase('instructor_edit');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function users($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('student');

        if ($param1 == "add") {
            $this->user_model->add_user();
            redirect(site_url('admin/users'), 'refresh');
        } elseif ($param1 == "edit") {
            $this->user_model->edit_user($param2);
            redirect(site_url('admin/users'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->user_model->delete_user($param2);
            redirect(site_url('admin/users'), 'refresh');
        } elseif ($param1 == "reset_mac") {
            $this->user_model->reset_user_mac($param2);
            $this->session->set_flashdata('flash_message', get_phrase('mac_address_reset_successfully'));
            redirect(site_url('admin/users'), 'refresh');
        }

        $page_data['page_name']  = 'users';
        $page_data['page_title'] = get_phrase('pharmacists');
        $this->load->view('backend/index', $page_data);
    }

    public function pharmacists($param1 = "", $param2 = "")
    {
        $this->users($param1, $param2);
    }

    public function pharmacist_form($param1 = "", $param2 = "")
    {
        if ($param1 == 'add_pharmacist' || $param1 == 'add') {
            $this->user_form('add_user_form', $param2);
        } elseif ($param1 == 'edit_pharmacist' || $param1 == 'edit') {
            $this->user_form('edit_user_form', $param2);
        } else {
            $this->user_form($param1, $param2);
        }
    }

    public function report($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        redirect(site_url('admin/licence_report'), 'refresh');
    }

    public function reports($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        redirect(site_url('admin/licence_report'), 'refresh');
    }

    public function licence_report()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        if (!has_permission('user') && !has_permission('student') && !has_permission('report') && !has_permission('revenue')) {
            check_permission('user');
        }

        $this->user_model->check_licence_end_date_column();

        $selected_store_id = $this->input->get('store_id') ?: 'all';

        // Calculate summary statistics
        $where_store = "";
        if ($selected_store_id != 'all' && !empty($selected_store_id)) {
            if ($selected_store_id == 'no_store') {
                $where_store = " AND (users.store_id IS NULL OR users.store_id = 0)";
            } else {
                $where_store = " AND users.store_id = " . intval($selected_store_id);
            }
        }

        $stats_query = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN (licence_end_date IS NOT NULL AND licence_end_date != '' AND COALESCE(STR_TO_DATE(licence_end_date, '%Y-%m-%d'), STR_TO_DATE(licence_end_date, '%d-%m-%Y'), STR_TO_DATE(licence_end_date, '%d/%m/%Y')) > DATE_ADD(CURDATE(), INTERVAL 90 DAY)) THEN 1 ELSE 0 END) as valid_count,
                SUM(CASE WHEN (licence_end_date IS NOT NULL AND licence_end_date != '' AND COALESCE(STR_TO_DATE(licence_end_date, '%Y-%m-%d'), STR_TO_DATE(licence_end_date, '%d-%m-%Y'), STR_TO_DATE(licence_end_date, '%d/%m/%Y')) >= CURDATE() AND COALESCE(STR_TO_DATE(licence_end_date, '%Y-%m-%d'), STR_TO_DATE(licence_end_date, '%d-%m-%Y'), STR_TO_DATE(licence_end_date, '%d/%m/%Y')) <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)) THEN 1 ELSE 0 END) as expiring_soon_count,
                SUM(CASE WHEN (licence_end_date IS NOT NULL AND licence_end_date != '' AND COALESCE(STR_TO_DATE(licence_end_date, '%Y-%m-%d'), STR_TO_DATE(licence_end_date, '%d-%m-%Y'), STR_TO_DATE(licence_end_date, '%d/%m/%Y')) < CURDATE()) THEN 1 ELSE 0 END) as expired_count,
                SUM(CASE WHEN (licence_end_date IS NULL OR licence_end_date = '' OR COALESCE(STR_TO_DATE(licence_end_date, '%Y-%m-%d'), STR_TO_DATE(licence_end_date, '%d-%m-%Y'), STR_TO_DATE(licence_end_date, '%d/%m/%Y')) IS NULL) THEN 1 ELSE 0 END) as pending_count
            FROM users 
            WHERE role_id = 2 {$where_store}
        ");
        $stats = $stats_query ? $stats_query->row_array() : [
            'total' => 0, 'valid_count' => 0, 'expiring_soon_count' => 0, 'expired_count' => 0, 'pending_count' => 0
        ];

        $stores = $this->db->where('status', 1)->order_by('store_name', 'asc')->get('stores')->result_array();

        $page_data['page_name']         = 'licence_report';
        $page_data['page_title']        = get_phrase('licence_report');
        $page_data['stats']             = $stats;
        $page_data['stores']            = $stores;
        $page_data['selected_store_id'] = $selected_store_id;
        $this->load->view('backend/index', $page_data);
    }

    public function licence_validity()
    {
        redirect(site_url('admin/licence_report'), 'refresh');
    }

    public function server_side_licence_report_data()
    {
        if ($this->session->userdata('admin_login') != true) {
            echo json_encode(["draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []]);
            return;
        }

        $this->user_model->check_licence_end_date_column();

        $columns = ['users.id', 'users.first_name', 'stores.store_name', 'users.licence_no', 'users.licence_start_date', 'users.licence_end_date', 'users.licence_end_date', 'users.licence_end_date', 'users.id'];

        $limit = (int)$this->input->post('length');
        $start = (int)$this->input->post('start');
        if ($limit <= 0) $limit = 10;
        if ($start < 0) $start = 0;

        $order_col_idx = isset($this->input->post('order')[0]['column']) ? (int)$this->input->post('order')[0]['column'] : 1;
        $column_index = isset($columns[$order_col_idx]) ? $columns[$order_col_idx] : 'users.first_name';
        $dir = (isset($this->input->post('order')[0]['dir']) && strtolower($this->input->post('order')[0]['dir']) == 'desc') ? 'desc' : 'asc';

        $filter_status = $this->input->post('filter_status') ? trim($this->input->post('filter_status')) : 'all';
        $filter_store_id = $this->input->post('filter_store_id') ? trim($this->input->post('filter_store_id')) : 'all';
        $filter_date_from = $this->input->post('filter_date_from') ? trim($this->input->post('filter_date_from')) : '';
        $filter_date_to = $this->input->post('filter_date_to') ? trim($this->input->post('filter_date_to')) : '';
        $search = isset($this->input->post('search')['value']) ? trim($this->input->post('search')['value']) : '';

        $date_sql = "COALESCE(STR_TO_DATE(NULLIF(users.licence_end_date, ''), '%Y-%m-%d'), STR_TO_DATE(NULLIF(users.licence_end_date, ''), '%d-%m-%Y'), STR_TO_DATE(NULLIF(users.licence_end_date, ''), '%d/%m/%Y'))";

        $apply_common_filters = function($db_instance) use ($filter_status, $filter_store_id, $filter_date_from, $filter_date_to, $search, $date_sql) {
            if ($filter_store_id === 'no_store') {
                $db_instance->where('(users.store_id IS NULL OR users.store_id = 0)');
            } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
                $db_instance->where('users.store_id', (int)$filter_store_id);
            }

            if ($filter_status === 'valid') {
                $db_instance->where("users.licence_end_date IS NOT NULL AND users.licence_end_date != '' AND {$date_sql} > DATE_ADD(CURDATE(), INTERVAL 90 DAY)", NULL, FALSE);
            } elseif ($filter_status === 'expiring_soon') {
                $db_instance->where("users.licence_end_date IS NOT NULL AND users.licence_end_date != '' AND {$date_sql} >= CURDATE() AND {$date_sql} <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)", NULL, FALSE);
            } elseif ($filter_status === 'expired') {
                $db_instance->where("users.licence_end_date IS NOT NULL AND users.licence_end_date != '' AND {$date_sql} < CURDATE()", NULL, FALSE);
            } elseif ($filter_status === 'pending') {
                $db_instance->where("(users.licence_end_date IS NULL OR users.licence_end_date = '' OR {$date_sql} IS NULL)", NULL, FALSE);
            }

            if (!empty($filter_date_from)) {
                $db_instance->where("{$date_sql} >= " . $this->db->escape($filter_date_from), NULL, FALSE);
            }
            if (!empty($filter_date_to)) {
                $db_instance->where("{$date_sql} <= " . $this->db->escape($filter_date_to), NULL, FALSE);
            }

            if (!empty($search)) {
                $db_instance->group_start();
                $db_instance->like('users.first_name', $search);
                $db_instance->or_like('users.last_name', $search);
                $db_instance->or_like('users.email', $search);
                $db_instance->or_like('users.phone', $search);
                $db_instance->or_like('users.licence_no', $search);
                $db_instance->or_like('stores.store_name', $search);
                $db_instance->or_like('stores.store_code', $search);
                $db_instance->group_end();
            }
        };

        // Total count of pharmacists (with store filter applied if set)
        $this->db->from('users');
        $this->db->where('users.role_id', 2);
        if ($filter_store_id === 'no_store') {
            $this->db->where('(users.store_id IS NULL OR users.store_id = 0)');
        } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
            $this->db->where('users.store_id', (int)$filter_store_id);
        }
        $total_number_of_row = $this->db->count_all_results();

        // Filtered count
        $this->db->from('users');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);
        $apply_common_filters($this->db);
        $filtered_number_of_row = $this->db->count_all_results();

        // Stats counters for current store filter (cached in Fast_cache)
        $this->load->library('fast_cache');
        $stats_cache_key = 'licence_stats_' . md5($filter_store_id);
        $stats = $this->fast_cache->get($stats_cache_key);

        if (!$stats) {
            $store_cond = "";
            if ($filter_store_id === 'no_store') {
                $store_cond = "AND (store_id IS NULL OR store_id = 0)";
            } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
                $store_cond = "AND store_id = " . (int)$filter_store_id;
            }

            $stats_row = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN parsed_date > DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as valid_count,
                    SUM(CASE WHEN parsed_date >= CURDATE() AND parsed_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as expiring_soon_count,
                    SUM(CASE WHEN parsed_date < CURDATE() THEN 1 ELSE 0 END) as expired_count,
                    SUM(CASE WHEN parsed_date IS NULL THEN 1 ELSE 0 END) as pending_count
                FROM (
                    SELECT store_id, {$date_sql} as parsed_date
                    FROM users 
                    WHERE role_id = 2 {$store_cond}
                ) u
            ")->row_array();

            $stats = [
                'total'               => (int)($stats_row['total'] ?? 0),
                'valid_count'         => (int)($stats_row['valid_count'] ?? 0),
                'expiring_soon_count' => (int)($stats_row['expiring_soon_count'] ?? 0),
                'expired_count'       => (int)($stats_row['expired_count'] ?? 0),
                'pending_count'       => (int)($stats_row['pending_count'] ?? 0)
            ];
            $this->fast_cache->set($stats_cache_key, $stats, 120);
        }

        // Fetch data
        $this->db->select("users.*, stores.store_name, stores.store_code, {$date_sql} as parsed_end_date", FALSE);
        $this->db->from('users');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);
        $apply_common_filters($this->db);

        if ($column_index === 'users.licence_end_date') {
            $this->db->order_by('parsed_end_date', $dir);
        } else {
            $this->db->order_by($column_index, $dir);
        }

        $this->db->limit($limit, $start);
        $users = $this->db->get()->result_array();

        $data = [];
        $today = new DateTime('today');

        foreach ($users as $k => $user) {
            $key = $start + $k + 1;
            $user_image = $this->user_model->get_user_image_url($user['id'], $user['image'] ?? null);

            $name_html = '
                <div class="d-flex align-items-center">
                    <img src="' . $user_image . '" alt="" height="38" width="38" class="rounded-circle mr-2 shadow-sm">
                    <div>
                        <a href="' . site_url('admin/user_form/edit_user_form/' . $user['id']) . '" class="text-body font-weight-bold font-13">' . html_escape(trim($user['first_name'] . ' ' . $user['last_name'])) . '</a>
                        <br>
                        ' . (!empty($user['email']) ? '<small class="text-muted"><i class="mdi mdi-email-outline mr-1"></i>' . html_escape($user['email']) . '</small>' : '<small class="text-muted font-italic font-11"><i class="mdi mdi-email-off-outline mr-1"></i>' . get_phrase('no_email') . '</small>') . '
                        ' . (!empty($user['phone']) ? '<br><small class="text-muted"><i class="mdi mdi-phone mr-1"></i>' . html_escape($user['phone']) . '</small>' : '') . '
                    </div>
                </div>';

            $store_html = !empty($user['store_name'])
                ? '<span class="badge badge-outline-primary font-12"><i class="mdi mdi-store mr-1"></i>' . html_escape($user['store_name']) . '</span>' . (!empty($user['store_code']) ? '<br><small class="text-muted font-11">' . html_escape($user['store_code']) . '</small>' : '')
                : '<span class="badge badge-light border text-muted font-11">' . get_phrase('no_store_assigned') . '</span>';

            $licence_no_html = !empty($user['licence_no'])
                ? '<span class="badge badge-dark-lighten font-12 px-2 py-1"><i class="mdi mdi-card-account-details-outline mr-1"></i>' . html_escape($user['licence_no']) . '</span>'
                : '<span class="text-muted font-italic font-12">' . get_phrase('not_provided') . '</span>';

            $start_date_html = !empty($user['licence_start_date'])
                ? '<span class="font-12 font-weight-semibold text-dark"><i class="mdi mdi-calendar mr-1"></i>' . html_escape($user['licence_start_date']) . '</span>'
                : '<span class="text-muted font-italic font-12">' . get_phrase('not_set') . '</span>';

            $end_date_str = trim($user['licence_end_date'] ?? '');
            $end_date_obj = null;
            if (!empty($end_date_str)) {
                $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date_str)
                    ?: DateTime::createFromFormat('d-m-Y', $end_date_str)
                    ?: DateTime::createFromFormat('d/m/Y', $end_date_str);
            }

            if ($end_date_obj) {
                $formatted_end_date = $end_date_obj->format('d M Y');
                $end_date_html = '<span class="font-12 font-weight-semibold text-dark"><i class="mdi mdi-calendar-clock mr-1"></i>' . $formatted_end_date . '</span>';

                $diff = $today->diff($end_date_obj);
                $days_diff = (int)$diff->format("%r%a");

                if ($days_diff < 0) {
                    $abs_days = abs($days_diff);
                    $validity_html = '<span class="badge badge-danger font-12 py-1 px-2"><i class="mdi mdi-alert-circle mr-1"></i>' . get_phrase('expired') . ' (' . $abs_days . ' ' . get_phrase('days_ago') . ')</span>';
                    $status_html = '<span class="badge badge-danger-lighten font-12 py-1 px-2">' . get_phrase('expired') . '</span>';
                } elseif ($days_diff <= 90) {
                    $validity_html = '<span class="badge badge-warning font-12 py-1 px-2 text-dark font-weight-bold"><i class="mdi mdi-clock-alert-outline mr-1"></i>' . $days_diff . ' ' . get_phrase('days_left') . '</span>';
                    $status_html = '<span class="badge badge-warning-lighten font-12 py-1 px-2 text-warning font-weight-bold">' . get_phrase('expiring_soon') . '</span>';
                } else {
                    $validity_html = '<span class="badge badge-success font-12 py-1 px-2"><i class="mdi mdi-check-circle mr-1"></i>' . $days_diff . ' ' . get_phrase('days_left') . '</span>';
                    $status_html = '<span class="badge badge-success-lighten font-12 py-1 px-2 text-success font-weight-bold">' . get_phrase('active_valid') . '</span>';
                }
            } else {
                $end_date_html = '<span class="text-danger font-italic font-12"><i class="mdi mdi-calendar-question mr-1"></i>' . get_phrase('missing_date') . '</span>';
                $validity_html = '<span class="badge badge-secondary-lighten text-secondary font-12 py-1 px-2">' . get_phrase('unknown') . '</span>';
                $status_html = '<span class="badge badge-secondary font-12 py-1 px-2"><i class="mdi mdi-calendar-question mr-1"></i>' . get_phrase('missing_date') . '</span>';
            }

            $action_html = '
                <div class="dropright dropright">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="' . site_url('admin/user_form/edit_user_form/' . $user['id']) . '"><i class="mdi mdi-pencil mr-1 text-primary"></i> ' . get_phrase('edit_pharmacist') . '</a></li>
                        <li><a class="dropdown-item" href="' . site_url('admin/users') . '"><i class="mdi mdi-account mr-1 text-info"></i> ' . get_phrase('view_in_pharmacists') . '</a></li>
                    </ul>
                </div>';

            $data[] = [
                0 => $key,
                1 => $name_html,
                2 => $store_html,
                3 => $licence_no_html,
                4 => $start_date_html,
                5 => $end_date_html,
                6 => $validity_html,
                7 => $status_html,
                8 => $action_html
            ];
        }

        echo json_encode([
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => (int)$total_number_of_row,
            "recordsFiltered" => (int)$filtered_number_of_row,
            "data"            => $data,
            "stats"           => $stats
        ]);
    }

    public function server_side_licence_validity_data()
    {
        $this->server_side_licence_report_data();
    }

    public function export_licence_report_csv()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $filter_status = $this->input->get('status') ? trim($this->input->get('status')) : 'all';
        $filter_store_id = $this->input->get('store_id') ? trim($this->input->get('store_id')) : 'all';
        $filter_date_from = $this->input->get('date_from') ? trim($this->input->get('date_from')) : '';
        $filter_date_to = $this->input->get('date_to') ? trim($this->input->get('date_to')) : '';
        $search = $this->input->get('search') ? trim($this->input->get('search')) : '';

        $date_sql = "COALESCE(STR_TO_DATE(NULLIF(users.licence_end_date, ''), '%Y-%m-%d'), STR_TO_DATE(NULLIF(users.licence_end_date, ''), '%d-%m-%Y'), STR_TO_DATE(NULLIF(users.licence_end_date, ''), '%d/%m/%Y'))";

        $this->db->select("users.*, stores.store_name, stores.store_code, {$date_sql} as parsed_end_date", FALSE);
        $this->db->from('users');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);

        if ($filter_store_id === 'no_store') {
            $this->db->where('(users.store_id IS NULL OR users.store_id = 0)');
        } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
            $this->db->where('users.store_id', (int)$filter_store_id);
        }

        if ($filter_status === 'valid') {
            $this->db->where("users.licence_end_date IS NOT NULL AND users.licence_end_date != '' AND {$date_sql} > DATE_ADD(CURDATE(), INTERVAL 90 DAY)", NULL, FALSE);
        } elseif ($filter_status === 'expiring_soon') {
            $this->db->where("users.licence_end_date IS NOT NULL AND users.licence_end_date != '' AND {$date_sql} >= CURDATE() AND {$date_sql} <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)", NULL, FALSE);
        } elseif ($filter_status === 'expired') {
            $this->db->where("users.licence_end_date IS NOT NULL AND users.licence_end_date != '' AND {$date_sql} < CURDATE()", NULL, FALSE);
        } elseif ($filter_status === 'pending') {
            $this->db->where("(users.licence_end_date IS NULL OR users.licence_end_date = '' OR {$date_sql} IS NULL)", NULL, FALSE);
        }

        if (!empty($filter_date_from)) {
            $this->db->where("{$date_sql} >= " . $this->db->escape($filter_date_from), NULL, FALSE);
        }
        if (!empty($filter_date_to)) {
            $this->db->where("{$date_sql} <= " . $this->db->escape($filter_date_to), NULL, FALSE);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('users.first_name', $search);
            $this->db->or_like('users.last_name', $search);
            $this->db->or_like('users.email', $search);
            $this->db->or_like('users.phone', $search);
            $this->db->or_like('users.licence_no', $search);
            $this->db->or_like('stores.store_name', $search);
            $this->db->or_like('stores.store_code', $search);
            $this->db->group_end();
        }

        $this->db->order_by('users.first_name', 'asc');
        $rows = $this->db->get()->result_array();

        $filename = "licence_report_" . date('Y-m-d_H-i-s') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            '#',
            'Pharmacist Name',
            'Email',
            'Phone',
            'Assigned Store',
            'Store Code',
            'Licence Number',
            'Issue Date',
            'Expiry Date',
            'Validity Days Left',
            'Status'
        ]);

        $today = new DateTime('today');
        foreach ($rows as $idx => $r) {
            $end_date_str = trim($r['licence_end_date'] ?? '');
            $end_date_obj = null;
            if (!empty($end_date_str)) {
                $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date_str)
                    ?: DateTime::createFromFormat('d-m-Y', $end_date_str)
                    ?: DateTime::createFromFormat('d/m/Y', $end_date_str);
            }

            $status_text = 'Missing Date';
            $days_text = 'N/A';
            $exp_text = 'Not Set';

            if ($end_date_obj) {
                $exp_text = $end_date_obj->format('d-m-Y');
                $diff = $today->diff($end_date_obj);
                $days_diff = (int)$diff->format("%r%a");
                $days_text = $days_diff;
                if ($days_diff < 0) {
                    $status_text = 'Expired';
                } elseif ($days_diff <= 90) {
                    $status_text = 'Expiring Soon';
                } else {
                    $status_text = 'Active / Valid';
                }
            }

            fputcsv($out, [
                $idx + 1,
                $r['first_name'] . ' ' . $r['last_name'],
                $r['email'],
                $r['phone'],
                $r['store_name'] ?: 'No Store Assigned',
                $r['store_code'] ?: '-',
                $r['licence_no'] ?: 'Not Provided',
                $r['licence_start_date'] ?: 'Not Set',
                $exp_text,
                $days_text,
                $status_text
            ]);
        }

        fclose($out);
        exit;
    }

    public function pharmacist_evaluation_report()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['page_name']  = 'pharmacist_evaluation_report';
        $page_data['page_title'] = get_phrase('pharmacist_evaluation_report');

        $page_data['stores'] = $this->db->order_by('store_name', 'asc')->get('stores')->result_array();
        $page_data['courses'] = $this->db->where('status', 'active')->order_by('title', 'asc')->get('course')->result_array();

        $page_data['quizzes'] = $this->db->select('lesson.id, lesson.title, lesson.course_id')
            ->join('course', 'course.id = lesson.course_id')
            ->where('lesson.lesson_type', 'quiz')
            ->order_by('lesson.title', 'asc')
            ->get('lesson')->result_array();

        $eval_rows = $this->db->select('quiz_results.*, lesson.attachment')
            ->from('quiz_results')
            ->join('users', 'users.id = quiz_results.user_id')
            ->join('lesson', 'lesson.id = quiz_results.quiz_id')
            ->where('users.role_id', 2)
            ->get()->result_array();

        $total_evals = count($eval_rows);
        $passed_count = 0;
        $failed_count = 0;
        $total_percentage = 0;

        foreach ($eval_rows as $er) {
            $att = json_decode($er['attachment'] ?? '{}', true) ?: [];
            $total_m = !empty($att['total_marks']) ? floatval($att['total_marks']) : 0;
            $pass_m  = isset($att['pass_mark']) && $att['pass_mark'] !== '' ? floatval($att['pass_mark']) : 0;
            if ($total_m <= 0) {
                $total_m = $this->db->where('quiz_id', $er['quiz_id'])->count_all_results('question') ?: 1;
            }
            $obt = floatval($er['total_obtained_marks']);
            $pct = round(($obt / $total_m) * 100);
            $is_p = ($pass_m > 0) ? ($obt >= $pass_m) : ($pct >= 50);

            if ($is_p) {
                $passed_count++;
            } else {
                $failed_count++;
            }
            $total_percentage += min(100, $pct);
        }

        $pass_rate = $total_evals > 0 ? round(($passed_count / $total_evals) * 100, 1) : 0;
        $avg_score = $total_evals > 0 ? round($total_percentage / $total_evals, 1) : 0;

        $page_data['stats'] = [
            'total'        => $total_evals,
            'passed_count' => $passed_count,
            'failed_count' => $failed_count,
            'pass_rate'    => $pass_rate,
            'avg_score'    => $avg_score
        ];

        $this->load->view('backend/index', $page_data);
    }

    public function server_side_evaluation_report_data()
    {
        if ($this->session->userdata('admin_login') != true) {
            echo json_encode(["draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []]);
            return;
        }

        $limit = (int)$this->input->post('length');
        $start = (int)$this->input->post('start');
        if ($limit <= 0) $limit = 10;
        if ($start < 0) $start = 0;

        $filter_result = $this->input->post('filter_result') ? trim($this->input->post('filter_result')) : 'all';
        $filter_store_id = $this->input->post('filter_store_id') ? trim($this->input->post('filter_store_id')) : 'all';
        $filter_course_id = $this->input->post('filter_course_id') ? trim($this->input->post('filter_course_id')) : 'all';
        $filter_quiz_id = $this->input->post('filter_quiz_id') ? trim($this->input->post('filter_quiz_id')) : 'all';
        $filter_date_from = $this->input->post('filter_date_from') ? trim($this->input->post('filter_date_from')) : '';
        $filter_date_to = $this->input->post('filter_date_to') ? trim($this->input->post('filter_date_to')) : '';
        $search = isset($this->input->post('search')['value']) ? trim($this->input->post('search')['value']) : '';

        $apply_eval_filters = function($db_instance) use ($filter_store_id, $filter_course_id, $filter_quiz_id, $filter_date_from, $filter_date_to, $search) {
            if ($filter_store_id === 'no_store') {
                $db_instance->where('(users.store_id IS NULL OR users.store_id = 0)');
            } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
                $db_instance->where('users.store_id', (int)$filter_store_id);
            }

            if (is_numeric($filter_course_id) && $filter_course_id > 0) {
                $db_instance->where('lesson.course_id', (int)$filter_course_id);
            }

            if (is_numeric($filter_quiz_id) && $filter_quiz_id > 0) {
                $db_instance->where('quiz_results.quiz_id', (int)$filter_quiz_id);
            }

            if (!empty($filter_date_from)) {
                $from_ts = strtotime($filter_date_from . ' 00:00:00');
                if ($from_ts) $db_instance->where('quiz_results.date_added >=', $from_ts);
            }
            if (!empty($filter_date_to)) {
                $to_ts = strtotime($filter_date_to . ' 23:59:59');
                if ($to_ts) $db_instance->where('quiz_results.date_added <=', $to_ts);
            }

            if (!empty($search)) {
                $db_instance->group_start();
                $db_instance->like('users.first_name', $search);
                $db_instance->or_like('users.last_name', $search);
                $db_instance->or_like('users.email', $search);
                $db_instance->or_like('course.title', $search);
                $db_instance->or_like('lesson.title', $search);
                $db_instance->or_like('stores.store_name', $search);
                $db_instance->or_like('stores.store_code', $search);
                $db_instance->group_end();
            }
        };

        $this->db->select('quiz_results.*, users.first_name, users.last_name, users.email, users.phone, users.employee_id, users.store_id, users.image, stores.store_name, stores.store_code, course.title as course_title, lesson.title as quiz_title, lesson.attachment');
        $this->db->from('quiz_results');
        $this->db->join('users', 'users.id = quiz_results.user_id');
        $this->db->join('lesson', 'lesson.id = quiz_results.quiz_id');
        $this->db->join('course', 'course.id = lesson.course_id');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);
        $apply_eval_filters($this->db);
        $this->db->order_by('quiz_results.date_added', 'desc');

        $all_results = $this->db->get()->result_array();

        $processed_rows = [];
        $passed_count = 0;
        $failed_count = 0;
        $total_score_sum = 0;
        $quiz_q_count_map = [];

        foreach ($all_results as $row) {
            $att = json_decode($row['attachment'] ?? '{}', true) ?: [];
            $total_m = !empty($att['total_marks']) ? floatval($att['total_marks']) : 0;
            $pass_m  = isset($att['pass_mark']) && $att['pass_mark'] !== '' ? floatval($att['pass_mark']) : 0;
            if ($total_m <= 0) {
                if (!isset($quiz_q_count_map[$row['quiz_id']])) {
                    $quiz_q_count_map[$row['quiz_id']] = $this->db->where('quiz_id', $row['quiz_id'])->count_all_results('question') ?: 1;
                }
                $total_m = $quiz_q_count_map[$row['quiz_id']];
            }
            $obt = floatval($row['total_obtained_marks']);
            $pct = round(($obt / $total_m) * 100);
            $is_passed = ($pass_m > 0) ? ($obt >= $pass_m) : ($pct >= 50);

            if ($is_passed) {
                $passed_count++;
            } else {
                $failed_count++;
            }
            $total_score_sum += min(100, $pct);

            $row['total_marks'] = $total_m;
            $row['pass_mark']   = $pass_m;
            $row['percentage']  = $pct;
            $row['is_passed']   = $is_passed;

            if ($filter_result === 'passed' && !$is_passed) continue;
            if ($filter_result === 'failed' && $is_passed) continue;

            $processed_rows[] = $row;
        }

        $total_matching = count($all_results);
        $filtered_total = count($processed_rows);

        $stats = [
            'total'        => $total_matching,
            'passed_count' => $passed_count,
            'failed_count' => $failed_count,
            'pass_rate'    => $total_matching > 0 ? round(($passed_count / $total_matching) * 100, 1) : 0,
            'avg_score'    => $total_matching > 0 ? round($total_score_sum / $total_matching, 1) : 0
        ];

        $paginated_rows = array_slice($processed_rows, $start, $limit);

        $data = [];
        foreach ($paginated_rows as $idx => $r) {
            $row_idx = $start + $idx + 1;
            $user_image = $this->user_model->get_user_image_url($r['user_id'], $r['image'] ?? null);

            $pharmacist_html = '
                <div class="d-flex align-items-center">
                    <img src="' . $user_image . '" alt="" height="36" width="36" class="rounded-circle mr-2 shadow-sm">
                    <div>
                        <a href="' . site_url('admin/user_form/edit_user_form/' . $r['user_id']) . '" class="text-body font-weight-bold font-13">' . html_escape($r['first_name'] . ' ' . $r['last_name']) . '</a>
                        ' . (!empty($r['employee_id']) ? '<br><small class="text-muted"><i class="mdi mdi-badge-account-horizontal-outline mr-1"></i>' . html_escape($r['employee_id']) . '</small>' : '') . '
                        <br><small class="text-muted"><i class="mdi mdi-email-outline mr-1"></i>' . html_escape($r['email']) . '</small>
                    </div>
                </div>';

            $store_html = !empty($r['store_name'])
                ? '<span class="badge badge-outline-primary font-12"><i class="mdi mdi-store mr-1"></i>' . html_escape($r['store_name']) . '</span>' . (!empty($r['store_code']) ? '<br><small class="text-muted font-11">' . html_escape($r['store_code']) . '</small>' : '')
                : '<span class="badge badge-light border text-muted font-11">' . get_phrase('no_store_assigned') . '</span>';

            $course_html = '<span class="font-weight-bold text-dark font-12"><i class="mdi mdi-book-open-page-variant text-info mr-1"></i>' . html_escape($r['course_title']) . '</span>';

            $quiz_html = '<span class="text-dark font-12 font-weight-semibold">' . html_escape($r['quiz_title']) . '</span>';

            $marks_html = '<span class="font-weight-bold font-13 text-dark">' . $r['total_obtained_marks'] . '</span> / ' . $r['total_marks'];

            $bar_class = $r['is_passed'] ? 'bg-success' : 'bg-danger';
            $score_html = '
                <div class="d-flex align-items-center">
                    <span class="font-weight-bold font-13 mr-2 ' . ($r['is_passed'] ? 'text-success' : 'text-danger') . '">' . $r['percentage'] . '%</span>
                    <div class="progress flex-grow-1" style="height: 6px; min-width: 50px;">
                        <div class="progress-bar ' . $bar_class . '" style="width: ' . min(100, $r['percentage']) . '%;"></div>
                    </div>
                </div>';

            $result_html = $r['is_passed']
                ? '<span class="badge badge-success font-12 py-1 px-2"><i class="mdi mdi-check-circle mr-1"></i>' . get_phrase('passed') . '</span>'
                : '<span class="badge badge-danger font-12 py-1 px-2"><i class="mdi mdi-close-circle mr-1"></i>' . get_phrase('failed') . '</span>';

            $attempt_date_html = '<span class="font-12 text-muted"><i class="mdi mdi-clock-outline mr-1"></i>' . date('d M Y, h:i A', $r['date_added']) . '</span>';

            $action_html = '
                <div class="dropright dropright">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="showAjaxModal(\'' . site_url('modal/popup/quiz_submission_detail_modal/' . $r['quiz_result_id']) . '\', \'' . get_phrase('quiz_evaluation_details') . '\')"><i class="mdi mdi-eye mr-1 text-info"></i> ' . get_phrase('view_details') . '</a></li>
                        <li><a class="dropdown-item" href="' . site_url('admin/user_form/edit_user_form/' . $r['user_id']) . '"><i class="mdi mdi-account mr-1 text-primary"></i> ' . get_phrase('pharmacist_profile') . '</a></li>
                    </ul>
                </div>';

            $data[] = [
                0 => $row_idx,
                1 => $pharmacist_html,
                2 => $store_html,
                3 => $course_html,
                4 => $quiz_html,
                5 => $marks_html,
                6 => $score_html,
                7 => $result_html,
                8 => $attempt_date_html,
                9 => $action_html
            ];
        }

        echo json_encode([
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => (int)$total_matching,
            "recordsFiltered" => (int)$filtered_total,
            "data"            => $data,
            "stats"           => $stats
        ]);
    }

    public function quiz_submission_detail($quiz_result_id = 0)
    {
        if ($this->session->userdata('admin_login') != true) {
            echo '<div class="alert alert-danger font-14">' . get_phrase('unauthorized_access') . '</div>';
            return;
        }

        $result = $this->db->get_where('quiz_results', ['quiz_result_id' => $quiz_result_id])->row_array();
        if (empty($result)) {
            echo '<div class="alert alert-danger font-14">' . get_phrase('quiz_result_not_found') . '</div>';
            return;
        }

        $page_data['result']    = $result;
        $page_data['user']      = $this->db->get_where('users', ['id' => $result['user_id']])->row_array();
        $page_data['quiz']      = $this->db->get_where('lesson', ['id' => $result['quiz_id']])->row_array();
        $page_data['course']    = !empty($page_data['quiz']['course_id']) ? $this->db->get_where('course', ['id' => $page_data['quiz']['course_id']])->row_array() : [];
        $page_data['store']     = !empty($page_data['user']['store_id']) ? $this->db->get_where('stores', ['id' => $page_data['user']['store_id']])->row_array() : null;
        $page_data['questions'] = !empty($page_data['quiz']['id']) ? $this->db->get_where('question', ['quiz_id' => $page_data['quiz']['id']])->result_array() : [];

        $this->load->view('backend/admin/quiz_submission_detail_modal', $page_data);
    }

    public function export_pharmacist_evaluation_csv()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $filter_result = $this->input->get('result') ? trim($this->input->get('result')) : 'all';
        $filter_store_id = $this->input->get('store_id') ? trim($this->input->get('store_id')) : 'all';
        $filter_course_id = $this->input->get('course_id') ? trim($this->input->get('course_id')) : 'all';
        $filter_quiz_id = $this->input->get('quiz_id') ? trim($this->input->get('quiz_id')) : 'all';
        $filter_date_from = $this->input->get('date_from') ? trim($this->input->get('date_from')) : '';
        $filter_date_to = $this->input->get('date_to') ? trim($this->input->get('date_to')) : '';
        $search = $this->input->get('search') ? trim($this->input->get('search')) : '';

        $this->db->select('quiz_results.*, users.first_name, users.last_name, users.email, users.phone, users.employee_id, users.store_id, stores.store_name, stores.store_code, course.title as course_title, lesson.title as quiz_title, lesson.attachment');
        $this->db->from('quiz_results');
        $this->db->join('users', 'users.id = quiz_results.user_id');
        $this->db->join('lesson', 'lesson.id = quiz_results.quiz_id');
        $this->db->join('course', 'course.id = lesson.course_id');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);

        if ($filter_store_id === 'no_store') {
            $this->db->where('(users.store_id IS NULL OR users.store_id = 0)');
        } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
            $this->db->where('users.store_id', (int)$filter_store_id);
        }

        if (is_numeric($filter_course_id) && $filter_course_id > 0) {
            $this->db->where('lesson.course_id', (int)$filter_course_id);
        }

        if (is_numeric($filter_quiz_id) && $filter_quiz_id > 0) {
            $this->db->where('quiz_results.quiz_id', (int)$filter_quiz_id);
        }

        if (!empty($filter_date_from)) {
            $from_ts = strtotime($filter_date_from . ' 00:00:00');
            if ($from_ts) $this->db->where('quiz_results.date_added >=', $from_ts);
        }
        if (!empty($filter_date_to)) {
            $to_ts = strtotime($filter_date_to . ' 23:59:59');
            if ($to_ts) $this->db->where('quiz_results.date_added <=', $to_ts);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('users.first_name', $search);
            $this->db->or_like('users.last_name', $search);
            $this->db->or_like('users.email', $search);
            $this->db->or_like('course.title', $search);
            $this->db->or_like('lesson.title', $search);
            $this->db->or_like('stores.store_name', $search);
            $this->db->or_like('stores.store_code', $search);
            $this->db->group_end();
        }

        $this->db->order_by('quiz_results.date_added', 'desc');
        $rows = $this->db->get()->result_array();

        $filename = "pharmacist_evaluation_report_" . date('Y-m-d_H-i-s') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            '#',
            'Pharmacist Name',
            'Employee ID',
            'Email',
            'Assigned Store',
            'Store Code',
            'Course Title',
            'Quiz Title',
            'Obtained Marks',
            'Total Marks',
            'Score Percentage',
            'Result',
            'Attempt Date'
        ]);

        $idx = 0;
        foreach ($rows as $r) {
            $att = json_decode($r['attachment'] ?? '{}', true) ?: [];
            $total_m = !empty($att['total_marks']) ? floatval($att['total_marks']) : 0;
            $pass_m  = isset($att['pass_mark']) && $att['pass_mark'] !== '' ? floatval($att['pass_mark']) : 0;
            if ($total_m <= 0) {
                $total_m = $this->db->where('quiz_id', $r['quiz_id'])->count_all_results('question') ?: 1;
            }
            $obt = floatval($r['total_obtained_marks']);
            $pct = round(($obt / $total_m) * 100);
            $is_passed = ($pass_m > 0) ? ($obt >= $pass_m) : ($pct >= 50);

            if ($filter_result === 'passed' && !$is_passed) continue;
            if ($filter_result === 'failed' && $is_passed) continue;

            $idx++;
            fputcsv($out, [
                $idx,
                $r['first_name'] . ' ' . $r['last_name'],
                $r['employee_id'] ?: '-',
                $r['email'],
                $r['store_name'] ?: 'No Store Assigned',
                $r['store_code'] ?: '-',
                $r['course_title'],
                strip_tags($r['quiz_title']),
                $obt,
                $total_m,
                $pct . '%',
                $is_passed ? 'PASSED' : 'FAILED',
                date('Y-m-d H:i:s', $r['date_added'])
            ]);
        }

        fclose($out);
        exit;
    }

    public function pharmacist_progress_report()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['page_name']  = 'pharmacist_progress_report';
        $page_data['page_title'] = get_phrase('course_completion_report');

        $page_data['stores']  = $this->db->order_by('store_name', 'asc')->get('stores')->result_array();
        $page_data['courses'] = $this->db->where('status', 'active')->order_by('title', 'asc')->get('course')->result_array();

        $enrolments = $this->db->select('enrol.*, users.id as u_id')
            ->from('enrol')
            ->join('users', 'users.id = enrol.user_id')
            ->where('users.role_id', 2)
            ->get()->result_array();

        $total_enrol = count($enrolments);
        $completed_count = 0;
        $in_prog_count = 0;
        $total_progress_sum = 0;

        foreach ($enrolments as $e) {
            $prog = round(course_progress($e['course_id'], $e['user_id']));
            $total_progress_sum += $prog;
            if ($prog >= 100) {
                $completed_count++;
            } elseif ($prog > 0) {
                $in_prog_count++;
            }
        }

        $avg_prog = $total_enrol > 0 ? round($total_progress_sum / $total_enrol, 1) : 0;

        $page_data['stats'] = [
            'total'             => $total_enrol,
            'completed_count'   => $completed_count,
            'in_progress_count' => $in_prog_count,
            'avg_progress'      => $avg_prog
        ];

        $this->load->view('backend/index', $page_data);
    }

    public function server_side_progress_report_data()
    {
        if ($this->session->userdata('admin_login') != true) {
            echo json_encode(["draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []]);
            return;
        }

        $limit = (int)$this->input->post('length');
        $start = (int)$this->input->post('start');
        if ($limit <= 0) $limit = 10;
        if ($start < 0) $start = 0;

        $filter_status = $this->input->post('filter_status') ? trim($this->input->post('filter_status')) : 'all';
        $filter_store_id = $this->input->post('filter_store_id') ? trim($this->input->post('filter_store_id')) : 'all';
        $filter_course_id = $this->input->post('filter_course_id') ? trim($this->input->post('filter_course_id')) : 'all';
        $filter_date_from = $this->input->post('filter_date_from') ? trim($this->input->post('filter_date_from')) : '';
        $filter_date_to = $this->input->post('filter_date_to') ? trim($this->input->post('filter_date_to')) : '';
        $search = isset($this->input->post('search')['value']) ? trim($this->input->post('search')['value']) : '';

        $this->db->select('enrol.*, users.first_name, users.last_name, users.email, users.phone, users.employee_id, users.store_id, users.image, stores.store_name, stores.store_code, course.title as course_title');
        $this->db->from('enrol');
        $this->db->join('users', 'users.id = enrol.user_id');
        $this->db->join('course', 'course.id = enrol.course_id');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);

        if ($filter_store_id === 'no_store') {
            $this->db->where('(users.store_id IS NULL OR users.store_id = 0)');
        } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
            $this->db->where('users.store_id', (int)$filter_store_id);
        }

        if (is_numeric($filter_course_id) && $filter_course_id > 0) {
            $this->db->where('enrol.course_id', (int)$filter_course_id);
        }

        if (!empty($filter_date_from)) {
            $from_ts = strtotime($filter_date_from . ' 00:00:00');
            if ($from_ts) $this->db->where('enrol.date_added >=', $from_ts);
        }
        if (!empty($filter_date_to)) {
            $to_ts = strtotime($filter_date_to . ' 23:59:59');
            if ($to_ts) $this->db->where('enrol.date_added <=', $to_ts);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('users.first_name', $search);
            $this->db->or_like('users.last_name', $search);
            $this->db->or_like('users.email', $search);
            $this->db->or_like('course.title', $search);
            $this->db->or_like('stores.store_name', $search);
            $this->db->or_like('stores.store_code', $search);
            $this->db->group_end();
        }

        $this->db->order_by('enrol.date_added', 'desc');
        $all_enrols = $this->db->get()->result_array();

        $processed_rows = [];
        $completed_count = 0;
        $in_prog_count = 0;
        $total_prog_sum = 0;

        foreach ($all_enrols as $e) {
            $prog = round(course_progress($e['course_id'], $e['user_id']));
            $total_prog_sum += $prog;

            if ($prog >= 100) {
                $status_code = 'completed';
                $completed_count++;
            } elseif ($prog > 0) {
                $status_code = 'in_progress';
                $in_prog_count++;
            } else {
                $status_code = 'not_started';
            }

            if ($filter_status !== 'all' && $filter_status !== $status_code) {
                continue;
            }

            $e['calculated_progress'] = $prog;
            $e['status_code'] = $status_code;

            // Note: detailed lessons are calculated only for the paginated slice below
            $processed_rows[] = $e;
        }

        $total_matching = count($all_enrols);
        $filtered_total = count($processed_rows);

        $stats = [
            'total'             => $total_matching,
            'completed_count'   => $completed_count,
            'in_progress_count' => $in_prog_count,
            'avg_progress'      => $total_matching > 0 ? round($total_prog_sum / $total_matching, 1) : 0
        ];

        $paginated_rows = array_slice($processed_rows, $start, $limit);

        // Pre-fetch course lesson counts into a map (1 query instead of 5,000!)
        $course_lesson_map = [];
        if (!empty($paginated_rows)) {
            $p_cids = array_unique(array_column($paginated_rows, 'course_id'));
            if (!empty($p_cids)) {
                $cl_rows = $this->db->select('course_id, COUNT(id) as total_lessons')
                    ->where_in('course_id', $p_cids)
                    ->group_by('course_id')
                    ->get('lesson')->result_array();
                foreach ($cl_rows as $cl) {
                    $course_lesson_map[$cl['course_id']] = (int)$cl['total_lessons'];
                }
            }
        }

        $data = [];
        foreach ($paginated_rows as $idx => $r) {
            $row_idx = $start + $idx + 1;
            $user_image = $this->user_model->get_user_image_url($r['user_id'], $r['image'] ?? null);

            $r['total_lessons'] = $course_lesson_map[$r['course_id']] ?? $this->crud_model->get_lessons('course', $r['course_id'])->num_rows();
            $r['completed_lessons'] = count(course_progress($r['course_id'], $r['user_id'], 'completed_lesson_ids'));

            $pharmacist_html = '
                <div class="d-flex align-items-center">
                    <img src="' . $user_image . '" alt="" height="36" width="36" class="rounded-circle mr-2 shadow-sm">
                    <div>
                        <a href="' . site_url('admin/user_form/edit_user_form/' . $r['user_id']) . '" class="text-body font-weight-bold font-13">' . html_escape($r['first_name'] . ' ' . $r['last_name']) . '</a>
                        ' . (!empty($r['employee_id']) ? '<br><small class="text-muted"><i class="mdi mdi-badge-account-horizontal-outline mr-1"></i>' . html_escape($r['employee_id']) . '</small>' : '') . '
                        <br><small class="text-muted"><i class="mdi mdi-email-outline mr-1"></i>' . html_escape($r['email']) . '</small>
                    </div>
                </div>';

            $store_html = !empty($r['store_name'])
                ? '<span class="badge badge-outline-primary font-12"><i class="mdi mdi-store mr-1"></i>' . html_escape($r['store_name']) . '</span>' . (!empty($r['store_code']) ? '<br><small class="text-muted font-11">' . html_escape($r['store_code']) . '</small>' : '')
                : '<span class="badge badge-light border text-muted font-11">' . get_phrase('no_store_assigned') . '</span>';

            $course_html = '<span class="font-weight-bold text-dark font-12"><i class="mdi mdi-book-open-page-variant text-info mr-1"></i>' . html_escape($r['course_title']) . '</span>';

            $enrol_date_html = '<span class="font-12 text-muted"><i class="mdi mdi-calendar mr-1"></i>' . date('d M Y', $r['date_added']) . '</span>';

            $lessons_html = '<span class="badge badge-light border font-12 py-1 px-2">' . $r['completed_lessons'] . ' / ' . $r['total_lessons'] . '</span>';

            $bar_color = ($r['calculated_progress'] >= 100) ? 'bg-success' : (($r['calculated_progress'] > 0) ? 'bg-info' : 'bg-secondary');
            $progress_html = '
                <div class="d-flex align-items-center">
                    <span class="font-weight-bold font-13 mr-2">' . $r['calculated_progress'] . '%</span>
                    <div class="progress flex-grow-1" style="height: 6px; min-width: 50px;">
                        <div class="progress-bar ' . $bar_color . '" style="width: ' . $r['calculated_progress'] . '%;"></div>
                    </div>
                </div>';

            if ($r['status_code'] === 'completed') {
                $status_html = '<span class="badge badge-success font-12 py-1 px-2"><i class="mdi mdi-check-circle mr-1"></i>' . get_phrase('completed') . '</span>';
            } elseif ($r['status_code'] === 'in_progress') {
                $status_html = '<span class="badge badge-warning font-12 py-1 px-2 text-dark"><i class="mdi mdi-clock-outline mr-1"></i>' . get_phrase('in_progress') . '</span>';
            } else {
                $status_html = '<span class="badge badge-secondary font-12 py-1 px-2">' . get_phrase('not_started') . '</span>';
            }

            $last_active = !empty($r['date_updated']) ? date('d M Y', $r['date_updated']) : date('d M Y', $r['date_added']);
            $date_html = '<span class="font-12 text-muted">' . $last_active . '</span>';

            $action_html = '
                <div class="dropright dropright">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="' . site_url('admin/course_form/course_edit/' . $r['course_id']) . '"><i class="mdi mdi-pencil mr-1 text-primary"></i> ' . get_phrase('edit_course') . '</a></li>
                        <li><a class="dropdown-item" href="' . site_url('admin/user_form/edit_user_form/' . $r['user_id']) . '"><i class="mdi mdi-account mr-1 text-info"></i> ' . get_phrase('pharmacist_profile') . '</a></li>
                    </ul>
                </div>';

            $data[] = [
                0 => $row_idx,
                1 => $pharmacist_html,
                2 => $store_html,
                3 => $course_html,
                4 => $enrol_date_html,
                5 => $lessons_html,
                6 => $progress_html,
                7 => $status_html,
                8 => $date_html,
                9 => $action_html
            ];
        }

        echo json_encode([
            "draw"            => (int)$this->input->post('draw'),
            "recordsTotal"    => (int)$total_matching,
            "recordsFiltered" => (int)$filtered_total,
            "data"            => $data,
            "stats"           => $stats
        ]);
    }

    public function export_pharmacist_progress_csv()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $filter_status = $this->input->get('status') ? trim($this->input->get('status')) : 'all';
        $filter_store_id = $this->input->get('store_id') ? trim($this->input->get('store_id')) : 'all';
        $filter_course_id = $this->input->get('course_id') ? trim($this->input->get('course_id')) : 'all';
        $filter_date_from = $this->input->get('date_from') ? trim($this->input->get('date_from')) : '';
        $filter_date_to = $this->input->get('date_to') ? trim($this->input->get('date_to')) : '';
        $search = $this->input->get('search') ? trim($this->input->get('search')) : '';

        $this->db->select('enrol.*, users.first_name, users.last_name, users.email, users.phone, users.employee_id, users.store_id, stores.store_name, stores.store_code, course.title as course_title');
        $this->db->from('enrol');
        $this->db->join('users', 'users.id = enrol.user_id');
        $this->db->join('course', 'course.id = enrol.course_id');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id', 2);

        if ($filter_store_id === 'no_store') {
            $this->db->where('(users.store_id IS NULL OR users.store_id = 0)');
        } elseif (is_numeric($filter_store_id) && $filter_store_id > 0) {
            $this->db->where('users.store_id', (int)$filter_store_id);
        }

        if (is_numeric($filter_course_id) && $filter_course_id > 0) {
            $this->db->where('enrol.course_id', (int)$filter_course_id);
        }

        if (!empty($filter_date_from)) {
            $from_ts = strtotime($filter_date_from . ' 00:00:00');
            if ($from_ts) $this->db->where('enrol.date_added >=', $from_ts);
        }
        if (!empty($filter_date_to)) {
            $to_ts = strtotime($filter_date_to . ' 23:59:59');
            if ($to_ts) $this->db->where('enrol.date_added <=', $to_ts);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('users.first_name', $search);
            $this->db->or_like('users.last_name', $search);
            $this->db->or_like('users.email', $search);
            $this->db->or_like('course.title', $search);
            $this->db->or_like('stores.store_name', $search);
            $this->db->or_like('stores.store_code', $search);
            $this->db->group_end();
        }

        $this->db->order_by('enrol.date_added', 'desc');
        $rows = $this->db->get()->result_array();

        $filename = "course_completion_report_" . date('Y-m-d_H-i-s') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            '#',
            'Pharmacist Name',
            'Employee ID',
            'Email',
            'Assigned Store',
            'Store Code',
            'Course Title',
            'Enrolment Date',
            'Completed Lessons',
            'Total Lessons',
            'Progress Percentage',
            'Status'
        ]);

        $idx = 0;
        foreach ($rows as $r) {
            $prog = round(course_progress($r['course_id'], $r['user_id']));
            if ($prog >= 100) {
                $status_code = 'completed';
                $status_label = 'Completed';
            } elseif ($prog > 0) {
                $status_code = 'in_progress';
                $status_label = 'In Progress';
            } else {
                $status_code = 'not_started';
                $status_label = 'Not Started';
            }

            if ($filter_status !== 'all' && $filter_status !== $status_code) {
                continue;
            }

            $total_lessons = $this->crud_model->get_lessons('course', $r['course_id'])->num_rows();
            $completed_lessons = count(course_progress($r['course_id'], $r['user_id'], 'completed_lesson_ids'));

            $idx++;
            fputcsv($out, [
                $idx,
                $r['first_name'] . ' ' . $r['last_name'],
                $r['employee_id'] ?: '-',
                $r['email'],
                $r['store_name'] ?: 'No Store Assigned',
                $r['store_code'] ?: '-',
                $r['course_title'],
                date('Y-m-d', $r['date_added']),
                $completed_lessons,
                $total_lessons,
                $prog . '%',
                $status_label
            ]);
        }

        fclose($out);
        exit;
    }

    private function get_fast_store_performance_data()
    {
        $this->load->library('fast_cache');
        $cache_key = 'store_performance_report_dataset';
        $cached = $this->fast_cache->get($cache_key);
        if ($cached) {
            return $cached;
        }

        $stores = $this->db->order_by('store_name', 'asc')->get('stores')->result_array();
        $date_sql = "COALESCE(STR_TO_DATE(NULLIF(licence_end_date, ''), '%Y-%m-%d'), STR_TO_DATE(NULLIF(licence_end_date, ''), '%d-%m-%Y'), STR_TO_DATE(NULLIF(licence_end_date, ''), '%d/%m/%Y'))";

        // 1. Grouped licence stats by store_id in 1 single fast query (instead of 1940 * 5 = 9700 queries!)
        $user_stats_grouped = [];
        $gu_rows = $this->db->query("
            SELECT 
                u.store_id,
                COUNT(*) as total_pharmacists,
                SUM(CASE WHEN parsed_date > DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as valid_licences,
                SUM(CASE WHEN parsed_date >= CURDATE() AND parsed_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as expiring_soon,
                SUM(CASE WHEN parsed_date < CURDATE() THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN parsed_date IS NULL THEN 1 ELSE 0 END) as missing_date
            FROM (
                SELECT store_id, {$date_sql} as parsed_date
                FROM users 
                WHERE role_id = 2
            ) u
            GROUP BY u.store_id
        ")->result_array();

        $unassigned_stats = [
            'total_pharmacists' => 0, 'valid_licences' => 0, 'expiring_soon' => 0, 'expired' => 0, 'missing_date' => 0
        ];

        foreach ($gu_rows as $gu) {
            $sid = (int)($gu['store_id'] ?? 0);
            if ($sid > 0) {
                $user_stats_grouped[$sid] = $gu;
            } else {
                $unassigned_stats = $gu;
            }
        }

        // 2. Grouped quiz evaluation stats by store_id in 1 query
        $eval_stats_grouped = [];
        $eq_rows = $this->db->query("
            SELECT 
                users.store_id,
                quiz_results.total_obtained_marks,
                lesson.attachment
            FROM quiz_results
            JOIN users ON users.id = quiz_results.user_id
            JOIN lesson ON lesson.id = quiz_results.quiz_id
            WHERE users.role_id = 2 AND users.store_id IS NOT NULL AND users.store_id > 0
        ")->result_array();

        foreach ($eq_rows as $er) {
            $sid = (int)$er['store_id'];
            if (!isset($eval_stats_grouped[$sid])) {
                $eval_stats_grouped[$sid] = ['total' => 0, 'passed' => 0];
            }
            $eval_stats_grouped[$sid]['total']++;

            $att = json_decode($er['attachment'] ?? '{}', true) ?: [];
            $total_m = !empty($att['total_marks']) ? floatval($att['total_marks']) : 0;
            $pass_m  = isset($att['pass_mark']) && $att['pass_mark'] !== '' ? floatval($att['pass_mark']) : 0;
            if ($total_m <= 0) $total_m = 100;
            $obt = floatval($er['total_obtained_marks']);
            $pct = round(($obt / $total_m) * 100);
            $is_p = ($pass_m > 0) ? ($obt >= $pass_m) : ($pct >= 50);
            if ($is_p) {
                $eval_stats_grouped[$sid]['passed']++;
            }
        }

        $store_rows = [];
        $total_pharmacists_all = 0;
        $total_valid_all = 0;
        $total_evals_all = 0;
        $total_passed_all = 0;

        foreach ($stores as $st) {
            $sid = (int)$st['id'];
            $st_stat = $user_stats_grouped[$sid] ?? [
                'total_pharmacists' => 0, 'valid_licences' => 0, 'expiring_soon' => 0, 'expired' => 0, 'missing_date' => 0
            ];
            $ev_stat = $eval_stats_grouped[$sid] ?? ['total' => 0, 'passed' => 0];

            $total_p        = (int)$st_stat['total_pharmacists'];
            $valid_licences = (int)$st_stat['valid_licences'];
            $expiring_soon  = (int)$st_stat['expiring_soon'];
            $expired        = (int)$st_stat['expired'];
            $missing        = (int)$st_stat['missing_date'];
            $total_evals    = (int)$ev_stat['total'];
            $passed_evals   = (int)$ev_stat['passed'];

            $licence_compliance_rate = $total_p > 0 ? round(($valid_licences / $total_p) * 100, 1) : 0;
            $eval_pass_rate = $total_evals > 0 ? round(($passed_evals / $total_evals) * 100, 1) : 0;

            $store_rows[] = [
                'id'                       => $st['id'],
                'store_name'               => $st['store_name'],
                'store_code'               => $st['store_code'] ?? '',
                'store_category'           => $st['store_category'] ?? '',
                'city'                     => $st['city'] ?? '',
                'state'                    => $st['state'] ?? '',
                'zone'                     => $st['zone'] ?? '',
                'address'                  => $st['address'] ?? '',
                'total_pharmacists'        => $total_p,
                'pharmacist_count'         => $total_p,
                'valid_licences'           => $valid_licences,
                'valid_licence_count'      => $valid_licences,
                'expiring_soon'            => $expiring_soon,
                'expiring_soon_count'      => $expiring_soon,
                'expired'                  => $expired,
                'expired_licence_count'    => $expired,
                'missing_date'             => $missing,
                'licence_compliance_rate'  => $licence_compliance_rate,
                'licence_compliance_pct'   => $licence_compliance_rate,
                'total_evaluations'        => $total_evals,
                'quiz_attempts'            => $total_evals,
                'passed_evaluations'       => $passed_evals,
                'quiz_passed'              => $passed_evals,
                'evaluation_pass_rate'     => $eval_pass_rate,
                'quiz_pass_rate'           => $eval_pass_rate
            ];

            $total_pharmacists_all += $total_p;
            $total_valid_all += $valid_licences;
            $total_evals_all += $total_evals;
            $total_passed_all += $passed_evals;
        }

        // Add Unassigned Pharmacists row if any exist
        $un_total = (int)$unassigned_stats['total_pharmacists'];
        if ($un_total > 0) {
            $un_valid = (int)$unassigned_stats['valid_licences'];
            $un_exp_soon = (int)$unassigned_stats['expiring_soon'];
            $un_exp = (int)$unassigned_stats['expired'];
            $un_missing = (int)$unassigned_stats['missing_date'];
            $un_comp_rate = $un_total > 0 ? round(($un_valid / $un_total) * 100, 1) : 0;

            $store_rows[] = [
                'id'                       => 'no_store',
                'store_name'               => get_phrase('no_store_assigned'),
                'store_code'               => 'N/A',
                'store_category'           => '-',
                'city'                     => '',
                'state'                    => '',
                'zone'                     => '',
                'address'                  => get_phrase('unassigned_pharmacists'),
                'total_pharmacists'        => $un_total,
                'pharmacist_count'         => $un_total,
                'valid_licences'           => $un_valid,
                'valid_licence_count'      => $un_valid,
                'expiring_soon'            => $un_exp_soon,
                'expiring_soon_count'      => $un_exp_soon,
                'expired'                  => $un_exp,
                'expired_licence_count'    => $un_exp,
                'missing_date'             => $un_missing,
                'licence_compliance_rate'  => $un_comp_rate,
                'licence_compliance_pct'   => $un_comp_rate,
                'total_evaluations'        => 0,
                'quiz_attempts'            => 0,
                'passed_evaluations'       => 0,
                'quiz_passed'              => 0,
                'evaluation_pass_rate'     => 0,
                'quiz_pass_rate'           => 0
            ];

            $total_pharmacists_all += $un_total;
            $total_valid_all += $un_valid;
        }

        $overview = [
            'total_stores'             => count($stores),
            'total_pharmacists'        => $total_pharmacists_all,
            'assigned_pharmacists'     => $total_pharmacists_all,
            'licence_compliance_rate'  => $total_pharmacists_all > 0 ? round(($total_valid_all / $total_pharmacists_all) * 100, 1) : 0,
            'evaluation_pass_rate'     => $total_evals_all > 0 ? round(($total_passed_all / $total_evals_all) * 100, 1) : 0
        ];

        $payload = [
            'store_rows' => $store_rows,
            'overview'   => $overview
        ];

        $this->fast_cache->set($cache_key, $payload, 300);
        return $payload;
    }

    public function store_performance_report()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['page_name']  = 'store_performance_report';
        $page_data['page_title'] = get_phrase('store_performance_report');

        $dataset = $this->get_fast_store_performance_data();
        $page_data['store_rows'] = $dataset['store_rows'];
        $page_data['overview']   = $dataset['overview'];

        $this->load->view('backend/index', $page_data);
    }

    public function export_store_performance_csv()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $dataset = $this->get_fast_store_performance_data();
        $store_rows = $dataset['store_rows'];

        $filename = "store_performance_report_" . date('Y-m-d_H-i-s') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            '#',
            'Store Name',
            'Store Code',
            'Category',
            'Zone',
            'State',
            'City',
            'Total Pharmacists',
            'Valid Licences',
            'Expiring Soon',
            'Expired',
            'Missing Expiry Date',
            'Licence Compliance Rate',
            'Quiz Evaluations Taken',
            'Evaluations Passed',
            'Evaluation Pass Rate'
        ]);

        foreach ($store_rows as $idx => $st) {
            fputcsv($out, [
                $idx + 1,
                $st['store_name'],
                $st['store_code'] ?: '-',
                $st['store_category'] ?: '-',
                $st['zone'] ?: '-',
                $st['state'] ?: '-',
                $st['city'] ?: '-',
                $st['total_pharmacists'],
                $st['valid_licences'],
                $st['expiring_soon'],
                $st['expired'],
                $st['missing_date'],
                $st['licence_compliance_rate'] . '%',
                $st['total_evaluations'],
                $st['passed_evaluations'],
                $st['evaluation_pass_rate'] . '%'
            ]);
        }

        fclose($out);
        exit;
    }

    public function extract_licence_ocr()
    {
        if ($this->session->userdata('admin_login') != true && $this->session->userdata('user_login') != 1) {
            echo json_encode(['status' => false, 'message' => get_phrase('unauthorized_access')]);
            return;
        }

        $this->user_model->process_licence_ocr_request();
    }

    private function parse_date_parts_to_ymd($d, $m, $y)
    {
        $months = [
            'jan' => '01', 'january' => '01', 'feb' => '02', 'february' => '02',
            'mar' => '03', 'march' => '03', 'apr' => '04', 'april' => '04',
            'may' => '05', 'jun' => '06', 'june' => '06', 'jul' => '07', 'july' => '07',
            'aug' => '08', 'august' => '08', 'sep' => '09', 'september' => '09',
            'oct' => '10', 'october' => '10', 'nov' => '11', 'november' => '11',
            'dec' => '12', 'december' => '12'
        ];

        $d = str_pad(trim($d), 2, '0', STR_PAD_LEFT);
        $y = trim($y);
        if (strlen($y) == 2) {
            $y = ((int)$y > 50 ? '19' : '20') . $y;
        }

        $m = trim($m);
        if (is_numeric($m)) {
            $month = str_pad($m, 2, '0', STR_PAD_LEFT);
        } else {
            $m_lower = strtolower($m);
            if (isset($months[$m_lower])) {
                $month = $months[$m_lower];
            } else {
                $month = '';
                foreach ($months as $k => $v) {
                    if (strpos($m_lower, $k) === 0) {
                        $month = $v;
                        break;
                    }
                }
            }
        }

        if (is_numeric($month) && (int)$month >= 1 && (int)$month <= 12 && (int)$d >= 1 && (int)$d <= 31) {
            return "$y-$month-$d";
        }
        return '';
    }

    private function parse_date_to_ymd($date_str)
    {
        if (empty($date_str)) return '';
        $date_str = trim($date_str);
        $date_str = str_replace(
            ['–', '—', '−', "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x88\x92", "\xc2\xa0", "\t"],
            ['-', '-', '-', '-', '-', '-', ' ', ' '],
            $date_str
        );
        $date_str = preg_replace('/\s*([\-\/\._])\s*/', '$1', $date_str);
        $date_str = trim($date_str);

        // DD-MM-YYYY or DD-Month-YYYY
        if (preg_match('/^([0-9]{1,2})[\-\/\._]([0-9]{1,2}|[A-Za-z]{3,9})[\-\/\._]([0-9]{2,4})$/', $date_str, $m)) {
            return $this->parse_date_parts_to_ymd($m[1], $m[2], $m[3]);
        }

        // YYYY-MM-DD
        if (preg_match('/^([0-9]{4})[\-\/\._]([0-9]{1,2})[\-\/\._]([0-9]{1,2})$/', $date_str, $m)) {
            $year = $m[1];
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $day = str_pad($m[3], 2, '0', STR_PAD_LEFT);
            if ((int)$day >= 1 && (int)$day <= 31 && (int)$month >= 1 && (int)$month <= 12) {
                return "$year-$month-$day";
            }
        }

        $ts = strtotime(str_replace('/', '-', $date_str));
        if ($ts) {
            return date('Y-m-d', $ts);
        }
        return '';
    }

    private function parse_licence_data_from_text($text)
    {
        return $this->user_model->parse_licence_data_from_text($text);
    }

    public function server_side_users_data()
    {

        $data = [];
        //mentioned all with colum of database table that related with html table
        $columns = ['id', 'id', 'id', 'first_name', 'designation', 'pharmacy_name', 'state', 'employee_id', 'gender', 'email', 'phone', 'licence_no', 'licence_start_date', 'licence_end_date', 'id', 'email_sent', 'id'];

        $store_id = $this->input->post('store_id') ?: $this->input->get('store_id');
        $apply_store_filter = function($db) use ($store_id) {
            if ($store_id === 'no_store') {
                $db->where('(store_id IS NULL OR store_id = 0)');
            } elseif (is_numeric($store_id) && $store_id > 0) {
                $db->where('store_id', (int)$store_id);
            }
        };

        $limit = htmlspecialchars_($this->input->post('length'));
        $start = htmlspecialchars_($this->input->post('start'));

        $order_col = isset($this->input->post('order')[0]['column']) ? intval($this->input->post('order')[0]['column']) : 1;
        $column_index = isset($columns[$order_col]) ? $columns[$order_col] : 'id';

        $dir                 = $this->input->post('order')[0]['dir'];
        $this->db->where('role_id !=', 1);
        $apply_store_filter($this->db);
        $total_number_of_row = $this->db->get('users')->num_rows();

        $filtered_number_of_row = $total_number_of_row;
        $search                 = $this->input->post('search')['value'];

        if (empty($search)) {
            $this->db->select('*');
            $this->db->limit($limit, $start);
            $this->db->order_by($column_index, $dir);
            $this->db->where('role_id', 2);
            $apply_store_filter($this->db);
            $students = $this->db->get('users')->result_array();
        } else {
            $this->db->select('*');
            $this->db->group_start();
            $this->db->like('first_name', $search);
            $this->db->or_like('last_name', $search);
            $this->db->or_like('designation', $search);
            $this->db->or_like('pharmacy_name', $search);
            $this->db->or_like('state', $search);
            $this->db->or_like('region', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('employee_id', $search);
            $this->db->or_like('gender', $search);
            $this->db->or_like('licence_no', $search);
            $this->db->or_like('licence_start_date', $search);
            $this->db->or_like('licence_end_date', $search);
            $this->db->group_end();
            $this->db->where('role_id', 2);
            $apply_store_filter($this->db);
            $this->db->limit($limit, $start);
            $this->db->order_by($column_index, $dir);
            $students = $this->db->get('users')->result_array();

            $this->db->select('*');
            $this->db->group_start();
            $this->db->like('first_name', $search);
            $this->db->or_like('last_name', $search);
            $this->db->or_like('designation', $search);
            $this->db->or_like('pharmacy_name', $search);
            $this->db->or_like('state', $search);
            $this->db->or_like('region', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('employee_id', $search);
            $this->db->or_like('gender', $search);
            $this->db->or_like('licence_no', $search);
            $this->db->or_like('licence_start_date', $search);
            $this->db->or_like('licence_end_date', $search);
            $this->db->group_end();
            $this->db->where('role_id', 2);
            $apply_store_filter($this->db);
            $filtered_number_of_row = $this->db->get('users')->num_rows();
        }

        foreach ($students as $key => $student):

            //photo
            $photo = '<img src="' . $this->user_model->get_user_image_url($student['id']) . '" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">';

            //user name
            if ($student['status'] != 1) {
                $status = '<small><p>' . get_phrase('status') . '<span class="badge badge-danger-lighten">' . get_phrase('unverified') . '</span></p></small>';
            } else {
                $status = '';
            }
            $name = $student['first_name'] . ' ' . $student['last_name'] . $status;

            //user email
            $email = $student['email'];
            if (!empty($student['mac_address'])) {
                $email .= '<br><span class="badge font-11 mt-1" style="background-color: #fff3ed; color: #f05a28; border: 1px solid rgba(240, 90, 40, 0.4); font-weight: 600; padding: 3px 8px; border-radius: 4px;" title="' . get_phrase('registered_mac_address') . '"><i class="mdi mdi-lan-connect mr-1" style="color: #f05a28;"></i>' . htmlspecialchars($student['mac_address']) . '</span>';
            } else {
                $email .= '<br><span class="badge badge-light text-muted font-11 mt-1" style="border: 1px solid #e2e8f0; padding: 3px 7px; border-radius: 4px;"><i class="mdi mdi-lan-disconnect mr-1"></i>' . get_phrase('no_mac_registered') . '</span>';
            }

            //enrolled courses
            $enrolled_courses       = $this->crud_model->enrol_history_by_user_id($student['id']);
            $enrolled_courses_title = '<ul>';
            foreach ($enrolled_courses->result_array() as $enrolled_course):
                $course_details = $this->crud_model->get_course_by_id($enrolled_course['course_id'])->row_array();
                $enrolled_courses_title .= '<li>' . $course_details['title'] . '</li>';
            endforeach;
            $enrolled_courses_title .= '</ul>';

            $email_sent_badge = (!empty($student['email_sent']) && $student['email_sent'] == 1)
                ? '<span class="badge badge-success-lighten"><i class="mdi mdi-check mr-1"></i>' . get_phrase('yes') . '</span>'
                : '<span class="badge badge-secondary-lighten">' . get_phrase('no') . '</span>';

            $action = '<div class="dropright dropright">
		                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                <i class="mdi mdi-dots-vertical"></i>
		                            </button>
		                            <ul class="dropdown-menu">
		                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="sendRegistrationMailSingle(' . $student['id'] . ', \'user\')"><i class="mdi mdi-email-fast-outline mr-1"></i>' . get_phrase('send_registration_mail') . '</a></li>
		                                <li><a class="dropdown-item" href="' . site_url('admin/user_form/edit_user_form/' . $student['id']) . '">' . get_phrase('edit') . '</a></li>
		                                <li><a class="dropdown-item" href="' . site_url('admin/users/reset_mac/' . $student['id']) . '" onclick="return confirm(\'' . get_phrase('are_you_sure_you_want_to_reset_mac_address') . '?\');"><i class="mdi mdi-refresh text-warning mr-1"></i>' . get_phrase('reset_mac_address') . '</a></li>
		                                <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/users/delete/' . $student['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                            </ul>
		                        </div>';

            $state_region = '';
            if (!empty($student['state'])) {
                $state_region .= htmlspecialchars($student['state']);
            }
            if (!empty($student['region'])) {
                $state_region .= ($state_region ? ' / ' : '') . '<span class="badge badge-primary-lighten">' . htmlspecialchars($student['region']) . '</span>';
            }

            $nestedData['checkbox']           = '<input type="checkbox" class="user-checkbox" value="' . $student['id'] . '" data-row-id="' . $student['id'] . '">';
            $nestedData['key']                = ++$key;
            $nestedData['photo']              = $photo;
            $nestedData['name']               = $name;
            $nestedData['designation']        = !empty($student['designation']) ? '<span class="badge badge-info-lighten px-2 py-1 font-12"><i class="mdi mdi-certificate mr-1"></i>' . htmlspecialchars($student['designation']) . '</span>' : '-';
            $nestedData['pharmacy_name']      = !empty($student['pharmacy_name']) ? '<strong>' . htmlspecialchars($student['pharmacy_name']) . '</strong>' : '-';
            $nestedData['state_region']       = !empty($state_region) ? $state_region : '-';
            $nestedData['employee_id']        = !empty($student['employee_id']) ? html_escape($student['employee_id']) : '-';
            $nestedData['gender']             = !empty($student['gender']) ? ucfirst(html_escape($student['gender'])) : '-';
            $nestedData['email']              = $email;
            $nestedData['phone']              = !empty($student['phone']) ? html_escape($student['phone']) : '-';
            $nestedData['licence_no']         = !empty($student['licence_no']) ? html_escape($student['licence_no']) : '-';
            $nestedData['licence_start_date'] = !empty($student['licence_start_date']) ? html_escape($student['licence_start_date']) : '-';
            $nestedData['licence_end_date']   = !empty($student['licence_end_date']) ? html_escape($student['licence_end_date']) : '-';
            $nestedData['enrolled_courses']   = $enrolled_courses_title;
            $nestedData['email_sent']         = $email_sent_badge;
            $nestedData['action']             = $action . '<script>$("a, i").tooltip();</script>';
            $data[]                           = $nestedData;
        endforeach;

        $json_data = [
            "draw"            => intval($this->input->post('draw')),
            "recordsTotal"    => intval($total_number_of_row),
            "recordsFiltered" => intval($filtered_number_of_row),
            "data"            => $data,
        ];
        echo json_encode($json_data);
    }

    public function server_side_instructors_data()
    {

        $data = [];
        //mentioned all with colum of database table that related with html table
        $columns = ['id', 'id', 'id', 'first_name', 'email', 'phone', 'id', 'email_sent', 'id'];

        $limit = htmlspecialchars_($this->input->post('length'));
        $start = htmlspecialchars_($this->input->post('start'));

        $order_col = isset($this->input->post('order')[0]['column']) ? intval($this->input->post('order')[0]['column']) : 1;
        $column_index = isset($columns[$order_col]) ? $columns[$order_col] : 'id';

        $dir                 = $this->input->post('order')[0]['dir'];
        $total_number_of_row = $this->db->where('is_instructor', 1)->where('role_id !=', 1)->get('users')->num_rows();

        $filtered_number_of_row = $total_number_of_row;
        $search                 = $this->input->post('search')['value'];

        if (empty($search)) {
            $this->db->select('*');
            $this->db->limit($limit, $start);
            $this->db->order_by($column_index, $dir);
            $this->db->group_start();
            $this->db->where('role_id', 2);
            $this->db->where('is_instructor', 1);
            $this->db->group_end();
            $instructors = $this->db->get('users')->result_array();
        } else {
            $this->db->select('*');
            $this->db->group_start();
            $this->db->like('first_name', $search);
            $this->db->or_like('last_name', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phone', $search);
            $this->db->group_end();
            $this->db->group_start();
            $this->db->where('role_id', 2);
            $this->db->where('is_instructor', 1);
            $this->db->group_end();
            $this->db->limit($limit, $start);
            $this->db->order_by($column_index, $dir);
            $instructors = $this->db->get('users')->result_array();

            $this->db->select('*');
            $this->db->group_start();
            $this->db->like('first_name', $search);
            $this->db->or_like('last_name', $search);
            $this->db->or_like('email', $search);
            $this->db->or_like('phone', $search);
            $this->db->group_end();
            $this->db->group_start();
            $this->db->where('role_id', 2);
            $this->db->where('is_instructor', 1);
            $this->db->group_end();
            $filtered_number_of_row = $this->db->get('users')->num_rows();
        }

        foreach ($instructors as $key => $instructor):

            //photo
            $photo = '<img src="' . $this->user_model->get_user_image_url($instructor['id']) . '" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">';

            //user name
            if ($instructor['status'] != 1) {
                $status = '<small><p>' . get_phrase('status') . '<span class="badge badge-danger-lighten">' . get_phrase('unverified') . '</span></p></small>';
            } else {
                $status = '';
            }
            $name = $instructor['first_name'] . ' ' . $instructor['last_name'] . $status;

            //user email
            $email = $instructor['email'];

            //enrolled courses
            $enrolled_courses       = $this->crud_model->enrol_history_by_user_id($instructor['id']);
            $enrolled_courses_title = '<ul>';
            foreach ($enrolled_courses->result_array() as $enrolled_course):
                $course_details = $this->crud_model->get_course_by_id($enrolled_course['course_id'])->row_array();
                $enrolled_courses_title .= '<li>' . $course_details['title'] . '</li>';
            endforeach;
            $enrolled_courses_title .= '</ul>';

            $email_sent_badge = (!empty($instructor['email_sent']) && $instructor['email_sent'] == 1)
                ? '<span class="badge badge-success-lighten"><i class="mdi mdi-check mr-1"></i>' . get_phrase('yes') . '</span>'
                : '<span class="badge badge-secondary-lighten">' . get_phrase('no') . '</span>';

            $action = '<div class="dropright dropright">
		                            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                <i class="mdi mdi-dots-vertical"></i>
		                            </button>
		                            <ul class="dropdown-menu">
		                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="sendRegistrationMailSingle(' . $instructor['id'] . ', \'user\')"><i class="mdi mdi-email-fast-outline mr-1"></i>' . get_phrase('send_registration_mail') . '</a></li>
		                                <li><a class="dropdown-item" href="' . site_url('admin/courses?category_id=all&status=all&instructor_id=' . $instructor['id'] . '&price=all') . '">' . get_phrase('view_courses') . '</a></li>
		                                <li><a class="dropdown-item" href="' . site_url('admin/instructor_form/edit_instructor_form/' . $instructor['id']) . '">' . get_phrase('edit') . '</a></li>
		                                <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/instructors/delete/' . $instructor['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                            </ul>
		                        </div>';

            $nestedData['checkbox']         = '<input type="checkbox" class="user-checkbox" value="' . $instructor['id'] . '" data-row-id="' . $instructor['id'] . '">';
            $nestedData['key']              = ++$key;
            $nestedData['photo']            = $photo;
            $nestedData['name']             = $name;
            $nestedData['email']            = $email;
            $nestedData['phone']            = $instructor['phone'];
            $nestedData['enrolled_courses'] = $enrolled_courses_title;
            $nestedData['email_sent']       = $email_sent_badge;
            $nestedData['action']           = $action . '<script>$("a, i").tooltip();</script>';
            $data[]                         = $nestedData;
        endforeach;

        $json_data = [
            "draw"            => intval($this->input->post('draw')),
            "recordsTotal"    => intval($total_number_of_row),
            "recordsFiltered" => intval($filtered_number_of_row),
            "data"            => $data,
        ];
        echo json_encode($json_data);
    }

    public function send_registration_mail()
    {
        if ($this->session->userdata('admin_login') != true) {
            echo json_encode(['status' => 'error', 'message' => get_phrase('session_expired_please_login_again')]);
            return;
        }

        $type = $this->input->post('type') ?: 'user'; // 'user' or 'store_user'
        $ids = $this->input->post('ids');

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['status' => 'error', 'message' => get_phrase('no_records_selected')]);
            return;
        }

        $clean_ids = [];
        foreach ($ids as $id) {
            $id = intval(trim($id));
            if ($id > 0) {
                $clean_ids[] = $id;
            }
        }

        if (empty($clean_ids)) {
            echo json_encode(['status' => 'error', 'message' => get_phrase('invalid_records_selected')]);
            return;
        }

        // Fetch template settings for 'signup' (New user registration template in settings)
        $notification = $this->db->where('type', 'signup')->get('notification_settings')->row_array();
        $template_name = $notification ? ($notification['setting_title'] ?: 'New user registration') : 'New user registration';

        $updated_count = 0;

        if ($type == 'store_user') {
            foreach ($clean_ids as $su_id) {
                $store_user = $this->db->get_where('store_users', ['id' => $su_id])->row_array();
                if ($store_user) {
                    $this->db->where('id', $su_id)->update('store_users', ['email_sent' => 1]);
                    if (!empty($store_user['pharmacist_id'])) {
                        $this->db->where('id', $store_user['pharmacist_id'])->update('users', ['email_sent' => 1]);
                        
                        // In-system notification using new user registration template
                        if ($notification && method_exists($this->email_model, 'notify')) {
                            $subjects = json_decode($notification['subject'], true);
                            $subject = isset($subjects['user']) ? $subjects['user'] : 'Registered successfully';
                            $this->email_model->notify('signup', $store_user['pharmacist_id'], $subject, 'You have been registered with access to store portal.');
                        }
                    }
                    $updated_count++;
                }
            }
        } else {
            // Type is 'user' (pharmacist or instructor)
            foreach ($clean_ids as $user_id) {
                $user = $this->db->get_where('users', ['id' => $user_id])->row_array();
                if ($user) {
                    $this->db->where('id', $user_id)->update('users', ['email_sent' => 1]);
                    
                    // Also update linked store_users if any
                    $this->db->where('pharmacist_id', $user_id)->update('store_users', ['email_sent' => 1]);

                    // In-system notification using new user registration template
                    if ($notification && method_exists($this->email_model, 'notify')) {
                        $subjects = json_decode($notification['subject'], true);
                        $subject = isset($subjects['user']) ? $subjects['user'] : 'Registered successfully';
                        $this->email_model->notify('signup', $user_id, $subject, 'You have successfully registered with us at ' . get_settings('system_name'));
                    }
                    $updated_count++;
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => get_phrase('registration_mail_sent_successfully') . ' (' . $updated_count . ' ' . get_phrase('users') . ')',
            'template_used' => $template_name,
            'updated_count' => $updated_count
        ]);
    }

    public function add_shortcut_student()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('student');

        $is_instructor = 0;
        echo $this->user_model->add_shortcut_user($is_instructor);
    }

    public function user_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('student');

        if ($param1 == 'add_user_form') {
            $page_data['page_name']  = 'user_add';
            $page_data['page_title'] = get_phrase('pharmacist_add');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_user_form') {
            $page_data['page_name']  = 'user_edit';
            $page_data['user_id']    = $param2;
            $page_data['page_title'] = get_phrase('pharmacist_edit');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function enrol_history($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        $date_range = $this->input->get('date_range');
        if (!empty($date_range) && strpos($date_range, ' - ') !== false) {
            $date_range_parts             = explode(" - ", $date_range);
            $page_data['timestamp_start'] = strtotime(trim($date_range_parts[0]) . ' 00:00:00');
            $page_data['timestamp_end']   = strtotime(trim($date_range_parts[1] ?? $date_range_parts[0]) . ' 23:59:59');
        } else {
            $first_day_of_month           = "1 " . date("M") . " " . date("Y") . ' 00:00:00';
            $last_day_of_month            = date("t") . " " . date("M") . " " . date("Y") . ' 23:59:59';
            $page_data['timestamp_start'] = strtotime($first_day_of_month);
            $page_data['timestamp_end']   = strtotime($last_day_of_month);
        }

        $selected_course_id = $this->input->get('course_id');
        if (empty($selected_course_id)) {
            $selected_course_id = 'all';
        }
        $page_data['selected_course_id'] = $selected_course_id;

        $page_data['courses']       = $this->db->order_by('title', 'asc')->get('course')->result_array();
        $page_data['page_name']     = 'enrol_history';
        $page_data['enrol_history'] = $this->crud_model->enrol_history_by_date_range($page_data['timestamp_start'], $page_data['timestamp_end'], $selected_course_id);
        $page_data['page_title']    = get_phrase('enrol_history');
        $this->load->view('backend/index', $page_data);
    }

    public function enrol_student($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        if ($param1 == 'enrol') {
            $this->crud_model->enrol_a_student_manually();
            $redirect_url = $this->input->post('redirect_to') ?: 'admin/enrol_student';
            redirect(site_url($redirect_url), 'refresh');
        }

        if ($param1 == 'update' || $param1 == 'edit') {
            $this->crud_model->update_student_enrolment();
            $redirect_url = $this->input->post('redirect_to') ?: 'admin/enrol_student';
            redirect(site_url($redirect_url), 'refresh');
        }

        $selected_store_id     = $this->input->get('store_id') ?: 'all';
        $selected_course_id    = $this->input->get('course_id') ?: 'all';
        $selected_enrol_status = $this->input->get('enrol_status') ?: 'all';
        $selected_status       = ($this->input->get('status') !== null && $this->input->get('status') !== '') ? $this->input->get('status') : 'all';
        $selected_role         = $this->input->get('role') ?: 'all';

        // Stores for filter dropdown
        $page_data['stores'] = $this->db->where('status', 1)->order_by('store_name', 'asc')->get('stores')->result_array();

        // Courses for filter dropdown & modal
        $page_data['courses'] = $this->db->where('status', 'active')->or_where('status', 'private')->order_by('title', 'asc')->get('course')->result_array();

        $page_data['selected_store_id']     = $selected_store_id;
        $page_data['selected_course_id']    = $selected_course_id;
        $page_data['selected_enrol_status'] = $selected_enrol_status;
        $page_data['selected_status']       = $selected_status;
        $page_data['selected_role']         = $selected_role;

        $page_data['page_name']  = 'enrol_student';
        $page_data['page_title'] = get_phrase('course_enrolment');
        $this->load->view('backend/index', $page_data);
    }

    // Applies the course enrolment page filters (and optional search) to the users query
    private function apply_enrol_student_filters($filters, $search = '')
    {
        $this->db->from('users');
        $this->db->join('stores', 'stores.id = users.store_id', 'left');
        $this->db->where('users.role_id !=', 1);

        if ($filters['store_id'] != 'all' && !empty($filters['store_id'])) {
            if ($filters['store_id'] == 'no_store') {
                $this->db->where('(users.store_id IS NULL OR users.store_id = 0)');
            } else {
                $this->db->where('users.store_id', intval($filters['store_id']));
            }
        }

        if ($filters['status'] != 'all' && $filters['status'] !== '') {
            $this->db->where('users.status', intval($filters['status']));
        }

        if ($filters['role'] == 'instructor') {
            $this->db->where('users.is_instructor', 1);
        } elseif ($filters['role'] == 'pharmacist' || $filters['role'] == 'student') {
            $this->db->where('users.is_instructor', 0);
        }

        if ($filters['course_id'] != 'all' && !empty($filters['course_id'])) {
            $course_id_int = intval($filters['course_id']);
            if ($filters['enrol_status'] == 'not_enrolled') {
                $this->db->where("users.id NOT IN (SELECT user_id FROM enrol WHERE course_id = {$course_id_int})");
            } else {
                $this->db->where("users.id IN (SELECT user_id FROM enrol WHERE course_id = {$course_id_int})");
            }
        } else {
            if ($filters['enrol_status'] == 'enrolled') {
                $this->db->where("users.id IN (SELECT user_id FROM enrol)");
            } elseif ($filters['enrol_status'] == 'not_enrolled') {
                $this->db->where("users.id NOT IN (SELECT user_id FROM enrol)");
            }
        }

        if ($search !== '') {
            $like = $this->db->escape('%' . $this->db->escape_like_str($search) . '%');
            $this->db->where("(CONCAT_WS(' ', users.first_name, users.last_name) LIKE {$like} ESCAPE '!'
                OR users.email LIKE {$like} ESCAPE '!'
                OR users.employee_id LIKE {$like} ESCAPE '!'
                OR stores.store_name LIKE {$like} ESCAPE '!'
                OR stores.store_code LIKE {$like} ESCAPE '!')", null, false);
        }
    }

    // Server-side DataTables source for the course enrolment users table
    public function enrol_student_data()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        $filters = [
            'store_id'     => $this->input->get('store_id') ?: 'all',
            'course_id'    => $this->input->get('course_id') ?: 'all',
            'enrol_status' => $this->input->get('enrol_status') ?: 'all',
            'status'       => ($this->input->get('status') !== null && $this->input->get('status') !== '') ? $this->input->get('status') : 'all',
            'role'         => $this->input->get('role') ?: 'all',
        ];

        $draw   = intval($this->input->get('draw'));
        $start  = max(0, intval($this->input->get('start')));
        $length = intval($this->input->get('length'));
        $length = ($length > 0 && $length <= 500) ? $length : 10;
        $search = trim((string) ($this->input->get('search')['value'] ?? ''));

        // Sortable table columns (by column index) => database columns
        $order_columns = [
            1 => ['users.id'],
            3 => ['users.first_name', 'users.last_name'],
            4 => ['users.email'],
            5 => ['users.is_instructor', 'store_role_title'],
            6 => ['users.employee_id'],
            7 => ['stores.store_name'],
            9 => ['users.status'],
        ];
        $order     = $this->input->get('order');
        $order_col = isset($order[0]['column']) ? intval($order[0]['column']) : null;
        $order_dir = (isset($order[0]['dir']) && strtolower($order[0]['dir']) == 'asc') ? 'asc' : 'desc';

        $records_total = $this->db->where('role_id !=', 1)->count_all_results('users');

        $this->apply_enrol_student_filters($filters, $search);
        $records_filtered = $this->db->count_all_results();

        $this->db->select('users.id, users.first_name, users.last_name, users.email, users.image, users.employee_id, users.is_instructor, users.status, stores.store_name, stores.store_code');
        $this->db->select('(SELECT su.role_title FROM store_users su WHERE su.pharmacist_id = users.id ORDER BY su.id LIMIT 1) AS store_role_title', false);
        $this->apply_enrol_student_filters($filters, $search);
        if ($order_col !== null && isset($order_columns[$order_col])) {
            foreach ($order_columns[$order_col] as $column) {
                $this->db->order_by($column, $order_dir);
            }
        } else {
            $this->db->order_by('users.id', 'desc');
        }
        $this->db->limit($length, $start);
        $users = $this->db->get()->result_array();

        // Enrolments with course titles, only for the users on this page
        $user_enrolments = [];
        $user_ids = array_column($users, 'id');
        if (!empty($user_ids)) {
            $enrolments = $this->db->select('enrol.id as enrol_id, enrol.user_id, enrol.course_id, enrol.expiry_date, enrol.date_added, course.title as course_title, course.status as course_status')
                ->from('enrol')
                ->join('course', 'course.id = enrol.course_id', 'left')
                ->where_in('enrol.user_id', $user_ids)
                ->get()->result_array();
            foreach ($enrolments as $e) {
                $user_enrolments[$e['user_id']][] = $e;
            }
        }

        $phrases = [];
        foreach (['instructor', 'pharmacist', 'none', 'lifetime_access', 'expired_on', 'expires_on', 'not_enrolled', 'active', 'inactive', 'enrol_course', 'enrol', 'edit_course_enrolment', 'edit'] as $phrase) {
            $phrases[$phrase] = get_phrase($phrase);
        }

        $data = [];
        foreach ($users as $key => $user) {
            $user_id       = $user['id'];
            $full_name     = trim($user['first_name'] . ' ' . $user['last_name']);
            $enrolled_list = $user_enrolments[$user_id] ?? [];
            $user_photo    = $this->user_model->get_user_image_url($user_id, $user['image']);

            if (!empty($user['is_instructor'])) {
                $role = '<span class="badge badge-info-lighten">' . $phrases['instructor'] . '</span>';
            } elseif (!empty($user['store_role_title'])) {
                $role = '<span class="badge badge-primary-lighten">' . htmlspecialchars($user['store_role_title']) . '</span>';
            } else {
                $role = '<span class="badge badge-secondary-lighten">' . $phrases['pharmacist'] . '</span>';
            }

            if (!empty($user['store_name'])) {
                $store = '<span class="badge badge-primary-lighten">' . htmlspecialchars($user['store_name']) . '</span>';
                if (!empty($user['store_code'])) {
                    $store .= '<small class="text-muted d-block">' . htmlspecialchars($user['store_code']) . '</small>';
                }
            } else {
                $store = '<span class="text-muted">' . $phrases['none'] . '</span>';
            }

            if (count($enrolled_list) > 0) {
                $courses = '<div class="d-flex flex-wrap" style="gap: 3px; max-width: 260px;">';
                foreach ($enrolled_list as $enrol_item) {
                    $is_expired   = (!empty($enrol_item['expiry_date']) && $enrol_item['expiry_date'] < time());
                    $expiry_label = empty($enrol_item['expiry_date']) ? $phrases['lifetime_access'] : ($is_expired ? $phrases['expired_on'] . ' ' . date('d M Y', $enrol_item['expiry_date']) : $phrases['expires_on'] . ' ' . date('d M Y', $enrol_item['expiry_date']));
                    $badge_class  = $is_expired ? 'badge-danger-lighten' : 'badge-success-lighten';
                    $course_title = $enrol_item['course_title'] ?? 'Course';
                    $courses .= '<span class="badge ' . $badge_class . ' course-badge" data-toggle="tooltip" data-placement="top" title="' . htmlspecialchars($course_title . ' — ' . $expiry_label) . '">'
                        . '<i class="mdi mdi-book-open-page-variant mr-1"></i>' . htmlspecialchars($course_title)
                        . ($is_expired ? '<span class="text-danger ml-1 font-weight-bold">•</span>' : '')
                        . '</span>';
                }
                $courses .= '</div>';
            } else {
                $courses = '<span class="badge badge-secondary-lighten">' . $phrases['not_enrolled'] . '</span>';
            }

            $status = $user['status'] == 1
                ? '<span class="badge badge-success">' . $phrases['active'] . '</span>'
                : '<span class="badge badge-danger">' . $phrases['inactive'] . '</span>';

            $action = '<div class="d-flex align-items-center" style="gap: 5px;">'
                . '<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-single-enrol" data-user-id="' . $user_id . '" data-name="' . htmlspecialchars($full_name) . '" data-email="' . htmlspecialchars($user['email']) . '" title="' . $phrases['enrol_course'] . '">'
                . '<i class="mdi mdi-school mr-1"></i>' . $phrases['enrol'] . '</button>';
            if (count($enrolled_list) > 0) {
                $role_label = !empty($user['is_instructor']) ? $phrases['instructor'] : (!empty($user['store_role_title']) ? $user['store_role_title'] : $phrases['pharmacist']);
                $action .= '<button type="button" class="btn btn-sm btn-outline-info btn-rounded btn-edit-enrol" data-user-id="' . $user_id . '" data-name="' . htmlspecialchars($full_name) . '" data-email="' . htmlspecialchars($user['email']) . '"'
                    . ' data-role="' . htmlspecialchars($role_label) . '"'
                    . ' data-store="' . htmlspecialchars($user['store_name'] ?? $phrases['none']) . '"'
                    . " data-enrolments='" . json_encode($enrolled_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . "'"
                    . ' title="' . $phrases['edit_course_enrolment'] . '">'
                    . '<i class="mdi mdi-pencil mr-1"></i>' . $phrases['edit'] . '</button>';
            }
            $action .= '</div>';

            $data[] = [
                'DT_RowId'    => 'user_row_' . $user_id,
                'checkbox'    => '<input type="checkbox" class="user-checkbox" value="' . $user_id . '" data-name="' . htmlspecialchars($full_name) . '" data-email="' . htmlspecialchars($user['email']) . '">',
                'key'         => $start + $key + 1,
                'photo'       => '<img src="' . $user_photo . '" alt="" height="36" width="36" class="img-fluid rounded-circle img-thumbnail shadow-sm">',
                'name'        => '<strong>' . htmlspecialchars($full_name) . '</strong>',
                'email'       => htmlspecialchars($user['email']),
                'role'        => $role,
                'employee_id' => !empty($user['employee_id']) ? '<code>' . htmlspecialchars($user['employee_id']) . '</code>' : '<span class="text-muted">-</span>',
                'store'       => $store,
                'courses'     => $courses,
                'status'      => $status,
                'action'      => $action,
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'draw'            => $draw,
            'recordsTotal'    => $records_total,
            'recordsFiltered' => $records_filtered,
            'data'            => $data,
        ]));
    }

    public function shortcut_enrol_student()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        echo $this->crud_model->shortcut_enrol_a_student_manually();
    }

    public function admin_revenue($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('revenue');

        if ($param1 != "") {
            $date_range                   = $this->input->get('date_range');
            $date_range                   = explode(" - ", $date_range);
            $page_data['timestamp_start'] = strtotime($date_range[0] . ' 00:00:00');
            $page_data['timestamp_end']   = strtotime($date_range[1] . ' 23:59:59');
        } else {
            $page_data['timestamp_start'] = strtotime(date("m/01/Y 00:00:00"));
            $page_data['timestamp_end']   = strtotime(date("m/t/Y 23:59:59"));
        }

        $page_data['page_name']       = 'admin_revenue';
        $page_data['payment_history'] = $this->crud_model->get_revenue_by_user_type($page_data['timestamp_start'], $page_data['timestamp_end'], 'admin_revenue');
        $page_data['page_title']      = get_phrase('admin_revenue');

        $this->load->view('backend/index', $page_data);
    }

    public function instructor_revenue($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('revenue');

        $page_data['page_name']       = 'instructor_revenue';
        $page_data['payment_history'] = $this->crud_model->get_revenue_by_user_type("", "", 'instructor_revenue');
        $page_data['page_title']      = get_phrase('instructor_revenue');
        $this->load->view('backend/index', $page_data);
    }

    public function invoice($payout_id = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name']  = 'invoice';
        $page_data['payout_id']  = $payout_id;
        $page_data['page_title'] = get_phrase('invoice');
        $this->load->view('backend/index', $page_data);
    }

    public function enrol_history_edit($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        $this->crud_model->update_single_enrol_history($param1);
        redirect(site_url('admin/enrol_history'), 'refresh');
    }

    public function enrol_history_delete($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('enrolment');

        $this->crud_model->delete_enrol_history($param1);
        $this->session->set_flashdata('flash_message', get_phrase('data_deleted_successfully'));
        redirect(site_url('admin/enrol_history'), 'refresh');
    }

    public function purchase_history()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name']        = 'purchase_history';
        $page_data['purchase_history'] = $this->crud_model->purchase_history();
        $page_data['page_title']       = get_phrase('purchase_history');
        $this->load->view('backend/index', $page_data);
    }

    public function system_settings($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'system_update') {
            $this->crud_model->update_system_settings();
            $this->session->set_flashdata('flash_message', get_phrase('system_settings_updated'));
            redirect(site_url('admin/system_settings'), 'refresh');
        }

        if ($param1 == 'logo_upload') {
            move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/backend/logo.png');
            $this->session->set_flashdata('flash_message', get_phrase('backend_logo_updated'));
            redirect(site_url('admin/system_settings'), 'refresh');
        }

        if ($param1 == 'favicon_upload') {
            move_uploaded_file($_FILES['favicon']['tmp_name'], 'assets/favicon.png');
            $this->session->set_flashdata('flash_message', get_phrase('favicon_updated'));
            redirect(site_url('admin/system_settings'), 'refresh');
        }

        $page_data['languages']  = $this->crud_model->get_all_languages();
        $page_data['page_name']  = 'system_settings';
        $page_data['page_title'] = get_phrase('system_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function frontend_settings($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'frontend_update') {
            $this->crud_model->update_frontend_settings();
            $this->session->set_flashdata('flash_message', get_phrase('frontend_settings_updated'));
            redirect(site_url('admin/frontend_settings?tab=frontendsettings'), 'refresh');
        }

        if ($param1 == 'recaptcha_update') {
            $this->crud_model->update_recaptcha_settings();
            $this->session->set_flashdata('flash_message', get_phrase('recaptcha_settings_updated'));
            redirect(site_url('admin/frontend_settings?tab=recaptcha'), 'refresh');
        }

        if ($param1 == 'banner_image_update') {
            $this->crud_model->update_frontend_banner();
            $this->session->set_flashdata('flash_message', get_phrase('banner_image_update'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'light_logo') {
            $this->crud_model->update_light_logo();
            $this->session->set_flashdata('flash_message', get_phrase('logo_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'dark_logo') {
            $this->crud_model->update_dark_logo();
            $this->session->set_flashdata('flash_message', get_phrase('logo_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'small_logo') {
            $this->crud_model->update_small_logo();
            $this->session->set_flashdata('flash_message', get_phrase('logo_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }
        if ($param1 == 'favicon') {
            $this->crud_model->update_favicon();
            $this->session->set_flashdata('flash_message', get_phrase('favicon_updated'));
            redirect(site_url('admin/frontend_settings?tab=logo_and_images'), 'refresh');
        }

        if ($param1 == 'motivational_speech') {
            $this->crud_model->update_motivational_speech();
            $this->session->set_flashdata('flash_message', get_phrase('Motivational speech updated successfully'));
            redirect(site_url('admin/home_page_builder?tab=pre-built-home-settings'), 'refresh');
        }

        if ($param1 == 'website_faq') {
            $this->crud_model->update_website_faq();
            $this->session->set_flashdata('flash_message', get_phrase('Website FAQS updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=websitefaqs'), 'refresh');
        }

        if ($param1 == 'contact_info') {
            $this->crud_model->update_contact_info();
            $this->session->set_flashdata('flash_message', get_phrase('Contact information updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=contact_information'), 'refresh');
        }

        if ($param1 == 'custom_codes') {
            $this->crud_model->update_custom_codes();
            $this->session->set_flashdata('flash_message', get_phrase('Your custom codes updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=custom_codes'), 'refresh');
        }

        if ($param1 == 'home_page_settings') {
            echo $this->crud_model->update_home_page_settings($param2);
            return;
        }

        if ($param1 == 'water_mark') {
            $this->crud_model->update_water_mark();
            $this->session->set_flashdata('flash_message', get_phrase('video water marks updated successfully'));
            redirect(site_url('admin/frontend_settings?tab=water_mark'), 'refresh');
        }

        if ($param1 == 'review_store') {
            $this->crud_model->review_store();
            $this->session->set_flashdata('flash_message', get_phrase('Review  Added successfully'));
            redirect(site_url('admin/frontend_settings?tab=review'), 'refresh');
        }

        if ($param1 == 'review_update') {
            $this->crud_model->review_update();
            $this->session->set_flashdata('flash_message', get_phrase('Review  Update successfully'));
            redirect(site_url('admin/frontend_settings?tab=review'), 'refresh');
        }

        $page_data['page_name']  = 'frontend_settings';
        $page_data['page_title'] = get_phrase('frontend_settings');
        $this->load->view('backend/index', $page_data);
    }
    public function payment_settings($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'system_currency') {
            $this->crud_model->update_system_currency();
            redirect(site_url('admin/payment_settings'), 'refresh');
        }

        if (isset($_POST['identifier'])) {
            $this->crud_model->update_payment_settings();
            redirect(site_url('admin/payment_settings'), 'refresh');
        }

        $page_data['payment_gateways'] = $this->crud_model->get_payment_gateways()->result_array();
        $page_data['page_name']        = 'payment_settings';
        $page_data['page_title']       = get_phrase('payment_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function notification_settings($param1 = "", $param2 = "", $param3 = "")
    {
        if ($param1 == 'smtp_settings') {
            $this->crud_model->update_smtp_settings();
            $this->session->set_flashdata('flash_message', get_phrase('smtp_settings_updated_successfully'));
            redirect(site_url('admin/notification_settings'), 'refresh');
        }

        if ($param1 == 'notification_enable_diable') {
            echo $this->crud_model->notification_enable_diable();
            return;
        }

        if (isset($_GET['tab'])) {
            $page_data['tab'] = $_GET['tab'];
        } else {
            $page_data['tab'] = 'smtp-settings';
        }

        $page_data['page_name']  = 'notification_settings';
        $page_data['page_title'] = get_phrase('Notification settings');
        $this->load->view('backend/index', $page_data);
    }

    public function edit_email_template($id = "", $param2 = "")
    {

        if ($param2 == 'update') {
            $data['subject']  = json_encode($this->input->post('subject'));
            $data['template'] = json_encode($this->input->post('template'));
            $this->db->where('id', $id)->update('notification_settings', $data);
            $this->session->set_flashdata('flash_message', get_phrase('Email template updated successfully'));
            redirect(site_url('admin/notification_settings?tab=email-template'), 'refresh');
        }
        $page_data['notification'] = $this->db->where('id', $id)->get('notification_settings')->row_array();
        $this->load->view('backend/admin/edit_email_template', $page_data);
    }

    public function social_login_settings($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'update') {
            $this->crud_model->update_social_login_settings();
            $this->session->set_flashdata('flash_message', get_phrase('social_login_settings_updated_successfully'));
            redirect(site_url('admin/social_login_settings'), 'refresh');
        }

        $page_data['page_name']  = 'social_login';
        $page_data['page_title'] = get_phrase('social_login');
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_settings($param1 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('user');
        check_permission('instructor');

        if ($param1 == 'update') {
            $this->crud_model->update_instructor_settings();
            $this->session->set_flashdata('flash_message', get_phrase('instructor_settings_updated'));
            redirect(site_url('admin/instructor_settings'), 'refresh');
        }

        $page_data['page_name']  = 'instructor_settings';
        $page_data['page_title'] = get_phrase('instructor_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function theme_settings($action = '')
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('theme');

        $page_data['page_name']  = 'theme_settings';
        $page_data['page_title'] = get_phrase('theme_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function theme_actions($action = "", $theme = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('theme');

        if ($action == 'activate') {
            $theme_to_active  = $this->input->post('theme');
            $installed_themes = $this->crud_model->get_installed_themes();
            if (in_array($theme_to_active, $installed_themes)) {
                $this->crud_model->activate_theme($theme_to_active);
                echo true;
            } else {
                echo false;
            }
        } elseif ($action == 'remove') {
            if ($theme == get_frontend_settings('theme')) {
                $this->session->set_flashdata('error_message', get_phrase('activate_a_theme_first'));
            } else {
                $this->crud_model->remove_files_and_folders(APPPATH . '/views/frontend/' . $theme);
                $this->crud_model->remove_files_and_folders(FCPATH . '/assets/frontend/' . $theme);
                $this->session->set_flashdata('flash_message', $theme . ' ' . get_phrase('theme_removed_successfully'));
            }
            redirect(site_url('admin/theme_settings'), 'refresh');
        }
    }

    public function courses()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        $page_data['selected_category_id']   = isset($_GET['category_id']) ? $_GET['category_id'] : "all";
        $page_data['selected_instructor_id'] = isset($_GET['instructor_id']) ? $_GET['instructor_id'] : "all";
        $page_data['selected_price']         = isset($_GET['price']) ? $_GET['price'] : "all";
        $page_data['selected_status']        = isset($_GET['status']) ? $_GET['status'] : "all";

        $page_data['page_name']  = 'courses-server-side';
        $page_data['categories'] = $this->crud_model->get_categories();
        $page_data['page_title'] = get_phrase('active_courses');
        $this->load->view('backend/index', $page_data);
    }

    // This function is responsible for loading the course data from server side for datatable SILENTLY
    public function get_courses()
    {
        $data = [];
        //mentioned all with colum of database table that related with html table
        $columns = ['id', 'title', 'sub_category_id', 'section', 'id', 'status', 'price', 'id'];

        // Filter portion
        $category_id   = $this->input->post('selected_category_id');
        $instructor_id = $this->input->post('selected_instructor_id');
        $price         = $this->input->post('selected_price');
        $status        = $this->input->post('selected_status');

        $limit = htmlspecialchars_($this->input->post('length'));
        $start = htmlspecialchars_($this->input->post('start'));

        $column_index = $columns[$this->input->post('order')[0]['column']];

        $dir = $this->input->post('order')[0]['dir'];

        $total_number_of_row = $this->crud_model->get_courses()->num_rows();
        $search              = $this->input->post('search')['value'];

        //FILTERED DATA
        $this->db->select('*');
        if (! empty($search)) {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('status', $search);
            $this->db->or_like('price', $search);
            $this->db->or_like('discounted_price', $search);
            $this->db->group_end();
        }
        if (! empty($category_id) && $category_id != 'all') {
            $this->db->where('sub_category_id', $category_id);
        }
        if (! empty($instructor_id) && $instructor_id != 'all') {
            $this->db->where('creator', $instructor_id);
        }
        if (! empty($price) && $price != 'all') {
            if ($price == 'free') {
                $this->db->where('is_free_course', 1);
            } elseif ($price == 'paid') {
                $this->db->where('is_free_course', null);
            }
        }

        if (! empty($status) && $status != 'all') {
            $this->db->group_start();
            $this->db->where('status', $status);
            $this->db->group_end();
        }

        $this->db->limit($limit, $start);
        $this->db->order_by($column_index, $dir);
        $courses = $this->db->get('course')->result_array();

        //WITHOUT FILTERED DATA
        $this->db->select('*');
        if (! empty($search)) {
            $this->db->group_start();
            $this->db->like('title', $search);
            $this->db->or_like('status', $search);
            $this->db->or_like('price', $search);
            $this->db->or_like('discounted_price', $search);
            $this->db->group_end();
        }
        if (! empty($category_id) && $category_id != 'all') {
            $this->db->where('sub_category_id', $category_id);
        }
        if (! empty($instructor_id) && $instructor_id != 'all') {
            $this->db->where('creator', $instructor_id);
        }
        if (! empty($price) && $price != 'all') {
            if ($price == 'free') {
                $this->db->where('is_free_course', 1);
            } elseif ($price == 'paid') {
                $this->db->where('is_free_course', null);
            }
        }
        if (! empty($status) && $status != 'all') {
            $this->db->group_start();
            $this->db->where('status', $status);
            $this->db->group_end();
        }
        $filtered_number_of_row = $this->db->get('course')->num_rows();

        // Fetch the data and make it as JSON format and return it.
        if (! empty($courses)) {
            foreach ($courses as $key => $row) {
                $instructor_details = $this->user_model->get_all_user($row['creator'])->row_array();
                $category_details   = $this->crud_model->get_category_details_by_id($row['sub_category_id'])->row_array();
                $sections           = $this->crud_model->get_section('course', $row['id']);
                $lessons            = $this->crud_model->get_lessons('course', $row['id']);
                $enroll_history     = $this->crud_model->enrol_history($row['id']);

                $status_badge = "badge-success-lighten";
                if ($row['status'] == 'pending') {
                    $status_badge = "badge-danger-lighten";
                } elseif ($row['status'] == 'draft') {
                    $status_badge = "badge-dark-lighten";
                } elseif ($row['status'] == 'private') {
                    $status_badge = "badge-dark";
                } elseif ($row['status'] == 'upcoming') {
                    $status_badge = "badge-warning-lighten";
                }

                $price_badge = "badge-dark-lighten";
                $price       = 0;
                if ($row['is_free_course'] == null) {
                    if ($row['discount_flag'] == 1) {
                        $price = currency($row['discounted_price']);
                    } else {
                        $price = currency($row['price']);
                    }
                } elseif ($row['is_free_course'] == 1) {
                    $price_badge = "badge-success-lighten";
                    $price       = get_phrase('free');
                }

                $price_field = '<span class="badge ' . $price_badge . '">' . $price . '</span>';
                if ($row['expiry_period'] > 0) {
                    $price_field .= '<p class="text-12">' . '( ' . $row['expiry_period'] . ' ' . get_phrase('Months') . ' )' . '</p>';
                } else {
                    $price_field .= '<p class="text-12">' . '( ' . get_phrase('Lifetime') . ' )' . '</p>';
                }

                $view_course_on_frontend_url = site_url('home/course/' . rawurlencode(slugify($row['title'])) . '/' . $row['id']);
                $go_to_course_playing_page   = site_url('home/lesson/' . rawurlencode(slugify($row['title'])) . '/' . $row['id']);
                $edit_this_course_url        = site_url('admin/course_form/course_edit/' . $row['id']);
                $duplicate_this_course_url   = site_url('admin/course_form/course_duplicate/' . $row['id']);
                $section_and_lesson_url      = site_url('admin/course_form/course_edit/' . $row['id']);
                $academic_progress_url       = site_url('admin/course_form/course_edit/' . $row['id'] . '?tab=academic_progress');

                if ($row['status'] == 'active') {
                    $course_status_changing_message = get_phrase('mark_as_pending');
                    if ($row['user_id'] != $this->session->userdata('user_id')) {
                        $course_status_changing_action = "showAjaxModal('" . site_url('modal/popup/mail_on_course_status_changing_modal/pending/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "', '" . $course_status_changing_message . "')";
                    } else {
                        $course_status_changing_action = "confirm_modal('" . site_url('admin/change_course_status_for_admin/pending/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "')";
                    }
                } else {
                    $course_status_changing_message = get_phrase('mark_as_active');
                    if ($row['user_id'] != $this->session->userdata('user_id')) {
                        $course_status_changing_action = "showAjaxModal('" . site_url('modal/popup/mail_on_course_status_changing_modal/active/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "', '" . $course_status_changing_message . "')";
                    } else {
                        $course_status_changing_action = "confirm_modal('" . site_url('admin/change_course_status_for_admin/active/' . $row['id'] . '/' . $category_id . '/' . $instructor_id . '/all/' . $status) . "')";
                    }
                }

                $delete_course_url = "confirm_modal('" . site_url('admin/course_actions/delete/' . $row['id']) . "')";

                if ($row['course_type'] == 'general') {
                    $section_and_lesson_menu = '<li><a class="dropdown-item" href="' . $section_and_lesson_url . '">' . get_phrase("section_and_lesson") . '</a></li>';
                } else {
                    $section_and_lesson_menu = "";
                }

                $course_academic_progress_menu = '<li><a class="dropdown-item" href="' . $academic_progress_url . '">' . get_phrase("Academic progress") . '</a></li>';

                $course_edit_menu = '<li><a class="dropdown-item" href="' . $edit_this_course_url . '">' . get_phrase("edit_this_course") . '</a></li>';

                $course_duplicate_menu = '<li><a class="dropdown-item" href="' . $duplicate_this_course_url . '">' . get_phrase("duplicate_this_course") . '</a></li>';

                $course_delete_menu = '<li><a class="dropdown-item" href="javascript:;" onclick="' . $delete_course_url . '">' . get_phrase("delete") . '</a></li>';

                $action = '
                <div class="dropright dropright">
                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="' . $view_course_on_frontend_url . '" target="_blank">' . get_phrase("view_course_on_frontend") . '</a></li>
                <li><a class="dropdown-item" href="' . $go_to_course_playing_page . '" target="_blank">' . get_phrase("go_to_course_playing_page") . '</a></li>
                ' . $course_academic_progress_menu . $course_edit_menu . $course_duplicate_menu . $section_and_lesson_menu . '
                <li><a class="dropdown-item" href="javascript:;" onclick="' . $course_status_changing_action . '">' . $course_status_changing_message . '</a></li>
                ' . $course_delete_menu . '
                </ul>
                </div>
                ';

                $nestedData['#'] = $key + 1;

                $instructor_names = "";
                foreach ($this->crud_model->get_course_instructors_id($row['id']) as $instructor_id) {
                    $multi_instructor = $this->user_model->get_all_user($instructor_id)->row_array();
                    $instructor_names = $multi_instructor['first_name'] . ' ' . $multi_instructor['last_name'];
                }

                $nestedData['title'] = '<strong><a href="' . site_url('admin/course_form/course_edit/' . $row['id']) . '">' . $row['title'] . '</a></strong><br>
                <small class="text-muted">' . get_phrase('instructor') . ': <b>' . $instructor_names . '</b></small>';

                $nestedData['category'] = '<span class="badge badge-dark-lighten">' . $category_details['name'] . '</span>';

                if ($row['course_type'] == 'scorm') {
                    $nestedData['lesson_and_section'] = '<span class="badge badge-info-lighten">' . get_phrase('scorm_course') . '</span>';
                } elseif ($row['course_type'] == 'h5p') {
                    $nestedData['lesson_and_section'] = '<span class="badge badge-info-lighten">' . get_phrase('h5p_course') . '</span>';
                } elseif ($row['course_type'] == 'general') {
                    $nestedData['lesson_and_section'] = '
                    <small class="text-muted"><b>' . get_phrase('Section') . '</b>: ' . $sections->num_rows() . '</small><br>
                    <small class="text-muted"><b>' . get_phrase('Lesson') . '</b>: ' . $lessons->num_rows() . '</small>';
                }

                $nestedData['enrolled_student'] = '<small class="text-muted"><b>' . get_phrase('Enrollments') . '</b>: ' . $enroll_history->num_rows() . '</small>';

                $nestedData['status'] = '<span class="badge ' . $status_badge . '">' . get_phrase($row['status']) . '</span>';

                $nestedData['price'] = $price_field;

                $nestedData['actions'] = $action;

                $nestedData['course_id'] = $row['id'];

                $data[] = $nestedData;
            }
        }

        $json_data = [
            "draw"            => intval($this->input->post('draw')),
            "recordsTotal"    => intval($total_number_of_row),
            "recordsFiltered" => intval($filtered_number_of_row),
            "data"            => $data,
        ];

        echo json_encode($json_data);
    }

    public function pending_courses()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        $page_data['page_name']  = 'pending_courses';
        $page_data['page_title'] = get_phrase('pending_courses');
        $this->load->view('backend/index', $page_data);
    }

    public function course_actions($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($param1 == "add") {
            $course_id = $this->crud_model->add_course();
            redirect(site_url('admin/course_form/course_edit/' . $course_id), 'refresh');
        } elseif ($param1 == 'add_shortcut') {
            echo $this->crud_model->add_shortcut_course();
        } elseif ($param1 == "edit") {

            $this->crud_model->update_course($param2); 

            // CHECK IF LIVE CLASS ADDON EXISTS, ADD OR UPDATE IT TO ADDON MODEL
            if (addon_status('live-class')) {
                $this->load->model('addons/Liveclass_model', 'liveclass_model');
                $this->liveclass_model->update_live_class($param2);
            }

            // CHECK IF JITSI LIVE CLASS ADDON EXISTS, ADD OR UPDATE IT TO ADDON MODEL
            if (addon_status('jitsi-live-class')) {
                $this->load->model('addons/jitsi_liveclass_model', 'jitsi_liveclass_model');
                $this->jitsi_liveclass_model->update_live_class($param2);
            }

            redirect(site_url('admin/course_form/course_edit/' . $param2));
        } elseif ($param1 == 'delete') {

            $this->is_drafted_course($param2);
            $this->crud_model->delete_course($param2);
            redirect(site_url('admin/courses'), 'refresh');
        }
    }

    public function course_form($param1 = "", $param2 = "")
    {

        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($param1 == 'add_course') {

            $page_data['languages']  = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $page_data['page_name']  = 'course_add';
            $page_data['page_title'] = get_phrase('add_course');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'add_course_shortcut') {
            $page_data['languages']  = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $this->load->view('backend/admin/course_add_shortcut', $page_data);
        } elseif ($param1 == 'course_edit') {

            $this->is_drafted_course($param2);
            $page_data['page_name']  = 'course_edit';
            $page_data['course_id']  = $param2;
            $page_data['page_title'] = get_phrase('edit_course');
            $page_data['languages']  = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'course_duplicate') {
            $this->duplicate_course($param2);
            $this->session->set_flashdata('flash_message', get_phrase('Course Duplicate Successfully'));
            redirect(site_url('admin/courses'), 'refresh');
        }
    }

    public function duplicate_course($id)
    {
        $course        = $this->db->where('id', $id)->get('course')->row_array();
        $max_course_id = $this->db->select_max('id')->get('course')->row_array();
        $course['id']  = $max_course_id['id'] + 1;
        $this->db->insert('course', $course);
    }

    private function is_drafted_course($course_id)
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
        if ($course_details['status'] == 'draft') {
            $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
            redirect(site_url('admin/courses'), 'refresh');
        }
    }

    public function change_course_status($updated_status = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $course_id     = $this->input->post('course_id');
        $category_id   = $this->input->post('category_id');
        $instructor_id = $this->input->post('instructor_id');
        $price         = $this->input->post('price');
        $status        = $this->input->post('status');
        if (isset($_POST['mail_subject']) && isset($_POST['mail_body'])) {
            $mail_subject = $this->input->post('mail_subject');
            $mail_body    = $this->input->post('mail_body');
            $this->email_model->send_mail_on_course_status_changing($course_id, $mail_subject, $mail_body);
        }
        $this->crud_model->change_course_status($updated_status, $course_id);
        $this->session->set_flashdata('flash_message', get_phrase('course_status_updated'));
        //redirect(site_url('admin/courses?category_id=' . $category_id . '&status=' . $status . '&instructor_id=' . $instructor_id . '&price=' . $price), 'refresh');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function change_course_status_for_admin($updated_status = "", $course_id = "", $category_id = "", $status = "", $instructor_id = "", $price = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->crud_model->change_course_status($updated_status, $course_id);
        $this->session->set_flashdata('flash_message', get_phrase('course_status_updated'));
        redirect(site_url('admin/courses?category_id=' . $category_id . '&status=' . $status . '&instructor_id=' . $instructor_id . '&price=' . $price), 'refresh');
    }

    public function sections($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($param2 == 'add') {
            $this->crud_model->add_section($param1);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_added_successfully'));
        } elseif ($param2 == 'edit') {
            $this->crud_model->edit_section($param3);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_updated_successfully'));
        } elseif ($param2 == 'delete') {
            $this->crud_model->delete_section($param1, $param3);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_deleted_successfully'));
        }
        redirect(site_url('admin/course_form/course_edit/' . $param1));
    }

    public function lessons($course_id = "", $param1 = "", $param2 = "")
    {
        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($param1 == 'add') {
            $response = $this->crud_model->add_lesson();
            echo $response;
            return;
        } elseif ($param1 == 'edit') {
            $response = $this->crud_model->edit_lesson($param2);
            echo $response;
            return;
        } elseif ($param1 == 'delete') {
            $this->crud_model->delete_lesson($param2);
            $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_deleted_successfully'));
            redirect('admin/course_form/course_edit/' . $course_id);
        } elseif ($param1 == 'filter') {
            redirect('admin/lessons/' . $this->input->post('course_id'));
        }
        $page_data['page_name']  = 'lessons';
        $page_data['lessons']    = $this->crud_model->get_lessons('course', $course_id);
        $page_data['course_id']  = $course_id;
        $page_data['page_title'] = get_phrase('lessons');
        $this->load->view('backend/index', $page_data);
    }

    public function watch_video($slugified_title = "", $lesson_id = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $lesson_details          = $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();
        $page_data['provider']   = $lesson_details['video_type'];
        $page_data['video_url']  = $lesson_details['video_url'];
        $page_data['lesson_id']  = $lesson_id;
        $page_data['page_name']  = 'video_player';
        $page_data['page_title'] = get_phrase('video_player');
        $this->load->view('backend/index', $page_data);
    }

    // Language Functions
    public function manage_language($param1 = '', $param2 = '', $param3 = '')
    {

        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('settings');

        if ($param1 == 'add_language') {
            $language = strtolower(trimmer($this->input->post('language')));
            if ($language == 'n-a') {
                $this->session->set_flashdata('error_message', get_phrase('language_name_can_not_be_empty_or_can_not_have_special_characters'));
                redirect(site_url('admin/manage_language'), 'refresh');
            }

            if (! $this->db->field_exists($language, 'language')) {
                $this->load->dbforge();
                $fields = [
                    $language => [
                        'type'      => 'TEXT',
                        'default'   => null,
                        'null'      => true,
                        'collation' => 'utf8_unicode_ci',
                    ],
                ];
                $this->dbforge->add_column('language', $fields);
            }

            saveDefaultJSONFile($language);
            $this->session->set_flashdata('flash_message', get_phrase('language_added_successfully'));
            redirect(site_url('admin/manage_language'), 'refresh');
        }
        if ($param1 == 'add_phrase') {
            $new_phrase = get_phrase($this->input->post('phrase'));
            $this->session->set_flashdata('flash_message', $new_phrase . ' ' . get_phrase('has_been_added_successfully'));
            redirect(site_url('admin/manage_language'), 'refresh');
        }

        if ($param1 == 'edit_phrase') {
            $page_data['edit_profile'] = $param2;
        }

        if ($param1 == 'delete_language') {
            if (file_exists('application/language/' . $param2 . '.json')) {
                unlink('application/language/' . $param2 . '.json');
                $this->session->set_flashdata('flash_message', get_phrase('language_deleted_successfully'));
                redirect(site_url('admin/manage_language'), 'refresh');
            }
        }
        $page_data['languages']  = $this->crud_model->get_all_languages();
        $page_data['page_name']  = 'manage_language';
        $page_data['page_title'] = get_phrase('multi_language_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function update_phrase_with_ajax()
    {
        $current_editing_language = $this->input->post('currentEditingLanguage');
        $updatedValue             = $this->input->post('updatedValue');
        $key                      = $this->input->post('key');
        saveJSONFile($current_editing_language, $key, $updatedValue);
        echo $current_editing_language . ' ' . $key . ' ' . $updatedValue;
    }

    public function message($param1 = 'message_home', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('messaging');

        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('flash_message', get_phrase('message_sent'));
            redirect(site_url('admin/message/message_read/' . $message_thread_code), 'refresh');
        }

        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->set_flashdata('flash_message', get_phrase('message_sent'));
            redirect(site_url('admin/message/message_read/' . $param2), 'refresh');
        }

        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
            $this->crud_model->mark_thread_messages_read($param2);
        }

        $page_data['message_inner_page_name'] = $param1;
        $page_data['page_name']               = 'message';
        $page_data['page_title']              = get_phrase('private_messaging');
        $this->load->view('backend/index', $page_data);
    }

    /******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
    public function manage_profile($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'update_profile_info') {
            $this->user_model->edit_user($param2);
            redirect(site_url('admin/manage_profile'), 'refresh');
        }
        if ($param1 == 'change_password') {
            $this->user_model->change_password($param2);
            redirect(site_url('admin/manage_profile'), 'refresh');
        }
        $page_data['page_name']  = 'manage_profile';
        $page_data['page_title'] = get_phrase('manage_profile');
        $page_data['edit_data']  = $this->db->get_where('users', [
            'id' => $this->session->userdata('user_id'),
        ])->result_array();
        $this->load->view('backend/index', $page_data);
    }

    public function paypal_checkout_for_instructor_revenue()
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['amount_to_pay']        = $this->input->post('amount_to_pay');
        $page_data['payout_id']            = $this->input->post('payout_id');
        $page_data['instructor_name']      = $this->input->post('instructor_name');
        $page_data['production_client_id'] = $this->input->post('production_client_id');

        // BEFORE, CHECK PAYOUT AMOUNTS ARE VALID
        $payout_details = $this->crud_model->get_payouts($page_data['payout_id'], 'payout')->row_array();
        if ($payout_details['amount'] == $page_data['amount_to_pay'] && $payout_details['status'] == 0) {
            $this->load->view('backend/admin/paypal_checkout_for_instructor_revenue', $page_data);
        } else {
            $this->session->set_flashdata('error_message', get_phrase('invalid_payout_data'));
            redirect(site_url('admin/instructor_payout'), 'refresh');
        }
    }

    // PAYPAL CHECKOUT ACTIONS
    public function paypal_payment($payout_id = "", $paypalPaymentID = "", $paypalPaymentToken = "", $paypalPayerID = "")
    {
        $payout_details  = $this->crud_model->get_payouts($payout_id, 'payout')->row_array();
        $instructor_id   = $payout_details['user_id'];
        $instructor_data = $this->db->get_where('users', ['id' => $instructor_id])->row_array();

        $payment_keys          = json_decode($instructor_data['payment_keys'], true);
        $paypal_keys           = $payment_keys['paypal'];
        $production_client_id  = $paypal_keys['production_client_id'];
        $production_secret_key = $paypal_keys['production_secret_key'];

        //THIS IS HOW I CHECKED THE PAYPAL PAYMENT STATUS
        $status = $this->payment_model->paypal_payment($paypalPaymentID, $paypalPaymentToken, $paypalPayerID, $production_client_id, $production_secret_key);
        if (! $status) {
            $this->session->set_flashdata('error_message', get_phrase('an_error_occurred_during_payment'));
            redirect(site_url('admin/instructor_payout'), 'refresh');
        }
        $this->crud_model->update_payout_status($payout_id, 'paypal');
        $this->session->set_flashdata('flash_message', get_phrase('payout_updated_successfully'));
        redirect(site_url('admin/instructor_payout'), 'refresh');
    }

    public function stripe_checkout_for_instructor_revenue($payout_id)
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // BEFORE, CHECK PAYOUT AMOUNTS ARE VALID
        $payout_details = $this->crud_model->get_payouts($payout_id, 'payout')->row_array();
        if ($payout_details['amount'] > 0 && $payout_details['status'] == 0) {
            $page_data['user_details']  = $this->user_model->get_user($payout_details['user_id'])->row_array();
            $page_data['amount_to_pay'] = $payout_details['amount'];
            $page_data['payout_id']     = $payout_details['id'];
            $this->load->view('backend/admin/stripe_checkout_for_instructor_revenue', $page_data);
        } else {
            $this->session->set_flashdata('error_message', get_phrase('invalid_payout_data'));
            redirect(site_url('admin/instructor_payout'), 'refresh');
        }
    }

    // STRIPE CHECKOUT ACTIONS
    public function stripe_payment($payout_id = "", $session_id = "")
    {
        $payout_details = $this->crud_model->get_payouts($payout_id, 'payout')->row_array();
        $instructor_id  = $payout_details['user_id'];
        //THIS IS HOW I CHECKED THE STRIPE PAYMENT STATUS
        $response = $this->payment_model->stripe_payment($instructor_id, $session_id, true);

        if ($response['payment_status'] === 'succeeded') {
            $this->crud_model->update_payout_status($payout_id, 'stripe');
            $this->session->set_flashdata('flash_message', get_phrase('payout_updated_successfully'));
        } else {
            $this->session->set_flashdata('error_message', $response['status_msg']);
        }

        redirect(site_url('admin/instructor_payout'), 'refresh');
    }

    public function razorpay_checkout_for_instructor_revenue($user_id = "", $payout_id = "", $param1 = "", $razorpay_order_id = "", $payment_id = "", $amount = "", $signature = "")
    {
        if ($param1 == 'paid') {
            $status = $this->payment_model->razorpay_payment($razorpay_order_id, $payment_id, $amount, $signature);
            if ($status == true) {
                $this->crud_model->update_payout_status($payout_id, 'razorpay');
                $this->session->set_flashdata('flash_message', get_phrase('payout_updated_successfully'));
            } else {
                $this->session->set_flashdata('error_message', $response['status_msg']);
            }

            redirect(site_url('admin/instructor_payout'), 'refresh');
        }

        $page_data['payout_id']     = $payout_id;
        $page_data['user_details']  = $this->user_model->get_user($user_id)->row_array();
        $page_data['amount_to_pay'] = $this->input->post('total_price_of_checking_out');
        $this->load->view('backend/admin/razorpay_checkout', $page_data);
    }

    public function preview($course_id = '')
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $this->is_drafted_course($course_id);
        if ($course_id > 0) {
            $courses = $this->crud_model->get_course_by_id($course_id);
            if ($courses->num_rows() > 0) {
                $course_details = $courses->row_array();
                redirect(site_url('home/lesson/' . rawurlencode(slugify($course_details['title'])) . '/' . $course_details['id']), 'refresh');
            }
        }
        redirect(site_url('admin/courses'), 'refresh');
    }

    // Manage Quizes
    public function quizes($course_id = "", $action = "", $quiz_id = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('course');

        if ($action == 'add') {
            $this->crud_model->add_quiz($course_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_added_successfully'));
        } elseif ($action == 'edit') {
            $this->crud_model->edit_quiz($quiz_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_updated_successfully'));
        } elseif ($action == 'delete') {
            $this->crud_model->delete_section($course_id, $quiz_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_deleted_successfully'));
        }
        redirect(site_url('admin/course_form/course_edit/' . $course_id));
    }

    // Manage Quize Questions
    public function quiz_questions($quiz_id = "", $action = "", $question_id = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->row_array();

        if ($action == 'add' || $action == 'edit') {
            echo $this->crud_model->manage_quiz_questions($quiz_id, $question_id, $action);
        } elseif ($action == 'delete') {
            $response = $this->crud_model->delete_quiz_question($question_id);
            $this->session->set_flashdata('flash_message', get_phrase('question_has_been_deleted'));
            redirect(site_url('admin/course_form/course_edit/' . $quiz_details['course_id']), 'refresh');
        }
    }

    // software about page
    public function about()
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['application_details'] = $this->crud_model->get_application_details();
        $page_data['page_name']           = 'about';
        $page_data['page_title']          = get_phrase('about');
        $this->load->view('backend/index', $page_data);
    }

    public function install_theme($theme_to_install = '')
    {

        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('theme');

        $uninstalled_themes = $this->crud_model->get_uninstalled_themes();
        if (! in_array($theme_to_install, $uninstalled_themes)) {
            $this->session->set_flashdata('error_message', get_phrase('this_theme_is_not_available'));
            redirect(site_url('admin/theme_settings'));
        }

        if (! class_exists('ZipArchive')) {
            $this->session->set_flashdata('error_message', get_phrase('your_server_is_unable_to_extract_the_zip_file') . '. ' . get_phrase('please_enable_the_zip_extension_on_your_server') . ', ' . get_phrase('then_try_again'));
            redirect(site_url('admin/theme_settings'));
        }

        $zipped_file_name   = $theme_to_install;
        $unzipped_file_name = substr($zipped_file_name, 0, -4);
        // Create update directory.
        $views_directory  = 'application/views/frontend';
        $assets_directory = 'assets/frontend';

        //Unzip theme zip file and remove zip file.
        $theme_path   = 'themes/' . $zipped_file_name;
        $theme_zip    = new ZipArchive;
        $theme_result = $theme_zip->open($theme_path);
        if ($theme_result === true) {
            $theme_zip->extractTo('themes');
            $theme_zip->close();
        }

        // unzip the views zip file to the application>views folder
        $views_path   = 'themes/' . $unzipped_file_name . '/views/' . $zipped_file_name;
        $views_zip    = new ZipArchive;
        $views_result = $views_zip->open($views_path);
        if ($views_result === true) {
            $views_zip->extractTo($views_directory);
            $views_zip->close();
        }

        // unzip the assets zip file to the assets/frontend folder
        $assets_path   = 'themes/' . $unzipped_file_name . '/assets/' . $zipped_file_name;
        $assets_zip    = new ZipArchive;
        $assets_result = $assets_zip->open($assets_path);
        if ($assets_result === true) {
            $assets_zip->extractTo($assets_directory);
            $assets_zip->close();
        }

        unlink($theme_path);
        $this->crud_model->remove_files_and_folders('themes/' . $unzipped_file_name);
        $this->session->set_flashdata('flash_message', get_phrase('theme_imported_successfully'));
        redirect(site_url('admin/theme_settings'));
    }

    //ADDON MANAGER PORTION STARTS HERE
    public function addon($param1 = "", $param2 = "", $param3 = "")
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('addon');

        // ADD NEW ADDON FORM
        if ($param1 == 'add') {

            // CHECK ACCESS PERMISSION
            check_permission('addon');
            $page_data['page_name']  = 'addon_add';
            $page_data['page_title'] = get_phrase('add_addon');
        }

        if ($param1 == 'update') {
            // CHECK ACCESS PERMISSION
            check_permission('addon');

            $page_data['page_name']  = 'addon_update';
            $page_data['page_title'] = get_phrase('add_update');
        }

        // INSTALLING AN ADDON
        if ($param1 == 'install' || $param1 == 'version_update') {
            // CHECK ACCESS PERMISSION
            check_permission('addon');

            $this->addon_model->install_addon($param1);
        }

        // ACTIVATING AN ADDON
        if ($param1 == 'activate') {

            $update_message = $this->addon_model->addon_activate($param2);
            $this->session->set_flashdata('flash_message', get_phrase($update_message));
            redirect(site_url('admin/addon'), 'refresh');
        }

        // DEACTIVATING AN ADDON
        if ($param1 == 'deactivate') {
            $update_message = $this->addon_model->addon_deactivate($param2);
            $this->session->set_flashdata('flash_message', get_phrase($update_message));
            redirect(site_url('admin/addon'), 'refresh');
        }

        // REMOVING AN ADDON
        if ($param1 == 'delete') {
            $this->addon_model->addon_delete($param2);
            $this->session->set_flashdata('flash_message', get_phrase('addon_is_deleted_successfully'));
            redirect(site_url('admin/addon'), 'refresh');
        }

        // SHOWING LIST OF INSTALLED ADDONS
        if (empty($param1)) {
            $page_data['page_name']  = 'addons';
            $page_data['addons']     = $this->addon_model->addon_list()->result_array();
            $page_data['page_title'] = get_phrase('addon_manager');
        }
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_application($param1 = "", $param2 = "")
    { // param1 is the status and param2 is the application id
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('instructor');

        if ($param1 == 'approve' || $param1 == 'delete') {
            $this->user_model->update_status_of_application($param1, $param2);
        }
        $page_data['page_name']             = 'application_list';
        $page_data['page_title']            = get_phrase('instructor_application');
        $page_data['approved_applications'] = $this->user_model->get_approved_applications();
        $page_data['pending_applications']  = $this->user_model->get_pending_applications();
        $this->load->view('backend/index', $page_data);
    }

    // INSTRUCTOR PAYOUT SECTION
    public function instructor_payout($param1 = "")
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('instructor');

        if ($param1 != "") {
            $date_range                   = $this->input->get('date_range');
            $date_range                   = explode(" - ", $date_range);
            $page_data['timestamp_start'] = strtotime($date_range[0]);
            $page_data['timestamp_end']   = strtotime($date_range[1]);
        } else {
            $page_data['timestamp_start'] = strtotime(date('m/01/Y'));
            $page_data['timestamp_end']   = strtotime(date('m/t/Y'));
        }

        $page_data['page_name']         = 'instructor_payout';
        $page_data['page_title']        = get_phrase('instructor_payout');
        $page_data['completed_payouts'] = $this->crud_model->get_completed_payouts_by_date_range($page_data['timestamp_start'], $page_data['timestamp_end']);
        $page_data['pending_payouts']   = $this->crud_model->get_pending_payouts();
        $this->load->view('backend/index', $page_data);
    }

    // ADMINS SECTION STARTS
    public function admins($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('admin');

        if ($param1 == "add") {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $this->user_model->add_user(false, true); // PROVIDING TRUE FOR INSTRUCTOR
            redirect(site_url('admin/admins'), 'refresh');
        } elseif ($param1 == "edit") {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $this->user_model->edit_user($param2);
            redirect(site_url('admin/admins'), 'refresh');
        } elseif ($param1 == "delete") {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $this->user_model->delete_user($param2);
            redirect(site_url('admin/admins'), 'refresh');
        }

        $page_data['page_name']  = 'admins';
        $page_data['page_title'] = get_phrase('admins');
        $page_data['admins']     = $this->user_model->get_admins()->result_array();
        $this->load->view('backend/index', $page_data);
    }

    public function admin_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($param1 == 'add_admin_form') {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $page_data['page_name']  = 'admin_add';
            $page_data['page_title'] = get_phrase('admin_add');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_admin_form') {
            // CHECK ACCESS PERMISSION
            check_permission('admin');

            $page_data['page_name']  = 'admin_edit';
            $page_data['user_id']    = $param2;
            $page_data['page_title'] = get_phrase('admin_edit');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function permissions()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        // CHECK ACCESS PERMISSION
        check_permission('admin');

        if (! isset($_GET['permission_assing_to']) || empty($_GET['permission_assing_to'])) {
            $this->session->set_flashdata('error_message', get_phrase('you_have_select_an_admin_first'));
            redirect(site_url('admin/admins'), 'refresh');
        }

        $page_data['permission_assing_to'] = $this->input->get('permission_assing_to');
        $user_details                      = $this->user_model->get_all_user($page_data['permission_assing_to']);
        if ($user_details->num_rows() == 0) {
            $this->session->set_flashdata('error_message', get_phrase('invalid_admin'));
            redirect(site_url('admin/admins'), 'refresh');
        } else {
            $user_details = $user_details->row_array();
            if ($user_details['role_id'] != 1) {
                $this->session->set_flashdata('error_message', get_phrase('invalid_admin'));
                redirect(site_url('admin/admins'), 'refresh');
            }
            if (is_root_admin($user_details['id'])) {
                $this->session->set_flashdata('error_message', get_phrase('you_can_not_set_permission_to_the_root_admin'));
                redirect(site_url('admin/admins'), 'refresh');
            }
        }

        $page_data['permission_assign_to'] = $user_details;
        $page_data['page_name']            = 'admin_permission';
        $page_data['page_title']           = get_phrase('assign_permission');
        $this->load->view('backend/index', $page_data);
    }

    // ASSIGN PERMISSION TO ADMIN
    public function assign_permission()
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('admin');

        echo $this->user_model->assign_permission();
    }

    // REMOVING INSTRUCTOR FROM COURSE
    public function remove_an_instructor($course_id, $instructor_id)
    {
        // CHECK ACCESS PERMISSION
        check_permission('course');

        $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();

        if ($course_details['creator'] == $instructor_id) {
            $this->session->set_flashdata('error_message', get_phrase('course_creator_can_be_removed'));
            redirect('admin/course_form/course_edit/' . $course_id);
        }

        if ($course_details['multi_instructor']) {
            $instructor_ids = explode(',', $course_details['user_id']);

            if (in_array($instructor_id, $instructor_ids)) {
                if (count($instructor_ids) > 1) {
                    if (($key = array_search($instructor_id, $instructor_ids)) !== false) {
                        unset($instructor_ids[$key]);

                        $data['user_id'] = implode(",", $instructor_ids);
                        $this->db->where('id', $course_id);
                        $this->db->update('course', $data);

                        $this->session->set_flashdata('flash_message', get_phrase('instructor_has_been_removed'));
                        if ($this->session->userdata('user_id') == $instructor_id) {
                            redirect('admin/courses/');
                        } else {
                            redirect('admin/course_form/course_edit/' . $course_id);
                        }
                    }
                } else {
                    $this->session->set_flashdata('error_message', get_phrase('a_course_should_have_at_least_one_instructor'));
                    redirect('admin/course_form/course_edit/' . $course_id);
                }
            } else {
                $this->session->set_flashdata('error_message', get_phrase('invalid_instructor_id'));
                redirect('admin/course_form/course_edit/' . $course_id);
            }
        } else {
            $this->session->set_flashdata('error_message', get_phrase('a_course_should_have_at_least_one_instructor'));
            redirect('admin/course_form/course_edit/' . $course_id);
        }
    }

    /** Coupons functionality starts */
    public function coupons($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('coupon');

        if ($param1 == "add") {
            // CHECK ACCESS PERMISSION
            check_permission('coupon');

            $response = $this->crud_model->add_coupon(); // PROVIDING TRUE FOR INSTRUCTOR
            $response ? $this->session->set_flashdata('flash_message', get_phrase('coupon_added_successfully')) : $this->session->set_flashdata('error_message', get_phrase('coupon_code_already_exists'));
            redirect(site_url('admin/coupons'), 'refresh');
        } elseif ($param1 == "edit") {
            // CHECK ACCESS PERMISSION
            check_permission('coupon');

            $response = $this->crud_model->edit_coupon($param2);
            $response ? $this->session->set_flashdata('flash_message', get_phrase('coupon_updated_successfully')) : $this->session->set_flashdata('error_message', get_phrase('coupon_code_already_exists'));
            redirect(site_url('admin/coupons'), 'refresh');
        } elseif ($param1 == "delete") {
            // CHECK ACCESS PERMISSION
            check_permission('coupon');

            $response = $this->crud_model->delete_coupon($param2);
            $response ? $this->session->set_flashdata('flash_message', get_phrase('coupon_deleted_successfully')) : $this->session->set_flashdata('error_message', get_phrase('coupon_code_already_exists'));
            redirect(site_url('admin/coupons'), 'refresh');
        }

        $page_data['page_name']  = 'coupons';
        $page_data['page_title'] = get_phrase('coupons');
        $page_data['coupons']    = $this->crud_model->get_coupons()->result_array();
        $this->load->view('backend/index', $page_data);
    }

    public function coupon_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        // CHECK ACCESS PERMISSION
        check_permission('coupon');

        if ($param1 == 'add_coupon_form') {

            $page_data['page_name']  = 'coupon_add';
            $page_data['page_title'] = get_phrase('add_coupons');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_coupon_form') {

            $page_data['page_name']  = 'coupon_edit';
            $page_data['coupon']     = $this->crud_model->get_coupons($param2)->row_array();
            $page_data['page_title'] = get_phrase('coupon_edit');
            $this->load->view('backend/index', $page_data);
        }
    }
    // ADMINS SECTION ENDS

    // AJAX PORTION
    // this function is responsible for managing multiple choice question
    public function quiz_fields_type_wize()
    {
        $page_data['question_type'] = $this->input->post('question_type');
        $this->load->view('backend/admin/quiz_fields_type_wize', $page_data);
    }

    public function ajax_get_sub_category($category_id)
    {
        $page_data['sub_categories'] = $this->crud_model->get_sub_categories($category_id);

        return $this->load->view('backend/admin/ajax_get_sub_category', $page_data);
    }

    public function ajax_get_section($course_id)
    {
        $page_data['sections'] = $this->crud_model->get_section('course', $course_id)->result_array();
        return $this->load->view('backend/admin/ajax_get_section', $page_data);
    }

    public function ajax_get_video_details()
    {
        $video_details = $this->video_model->getVideoDetails($_POST['video_url']);
        if (is_array($video_details)) {
            echo $video_details['duration'];
        }
    }
    public function ajax_sort_section()
    {
        $section_json = $this->input->post('itemJSON');
        $this->crud_model->sort_section($section_json);
    }
    public function ajax_sort_lesson()
    {
        $lesson_json = $this->input->post('itemJSON');
        $this->crud_model->sort_lesson($lesson_json);
    }
    public function ajax_sort_question()
    {
        $question_json = $this->input->post('itemJSON');
        $this->crud_model->sort_question($question_json);
    }

    //Start blog
    public function add_blog_category()
    {
        $this->load->view('backend/admin/blog_category_add');
    }

    public function edit_blog_category($blog_category_id = "")
    {
        $data['blog_category'] = $this->crud_model->get_blog_categories($blog_category_id)->row_array();
        $this->load->view('backend/admin/blog_category_edit', $data);
    }

    public function blog_category($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $response = $this->crud_model->add_blog_category();
            if ($response == true) {
                $this->session->set_flashdata('flash_message', get_phrase('blog_category_added_successfully'));
            } else {
                $this->session->set_flashdata('error_message', get_phrase('there_is_already_a_blog_with_this_name'));
            }
            redirect(site_url('admin/blog_category'), 'refresh');
        } elseif ($param1 == 'update') {
            $response = $this->crud_model->update_blog_category($param2);
            if ($response == true) {
                $this->session->set_flashdata('flash_message', get_phrase('blog_category_updated_successfully'));
            } else {
                $this->session->set_flashdata('error_message', get_phrase('there_is_already_a_blog_with_this_name'));
            }
            redirect(site_url('admin/blog_category'), 'refresh');
        } elseif ($param1 == 'delete') {
            $this->crud_model->delete_blog_category($param2);
            $this->session->set_flashdata('flash_message', get_phrase('blog_category_deleted_successfully'));
            redirect(site_url('admin/blog_category'), 'refresh');
        }
        $page_data['categories'] = $this->crud_model->get_blog_categories();
        $page_data['page_title'] = get_phrase('blog_category');
        $page_data['page_name']  = 'blog_category';
        $this->load->view('backend/index', $page_data);
    }

    public function add_blog()
    {
        $page_data['page_title'] = get_phrase('add_blog');
        $page_data['page_name']  = 'blog_add';
        $this->load->view('backend/index', $page_data);
    }

    public function edit_blog($blog_id = "")
    {
        $page_data['blog']       = $this->crud_model->get_blogs($blog_id)->row_array();
        $page_data['page_title'] = get_phrase('edit_blog');
        $page_data['page_name']  = 'blog_edit';
        $this->load->view('backend/index', $page_data);
    }

    public function blog($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $this->crud_model->add_blog();
            $this->session->set_flashdata('flash_message', get_phrase('blog_added_successfully'));
            redirect(site_url('admin/blog'), 'refresh');
        } elseif ($param1 == 'update') {
            $this->crud_model->update_blog($param2);
            $this->session->set_flashdata('flash_message', get_phrase('blog_updated_successfully'));
            redirect(site_url('admin/blog'), 'refresh');
        } elseif ($param1 == 'status') {
            $this->crud_model->update_blog_status($param2);
            $this->session->set_flashdata('flash_message', get_phrase('blog_status_has_been_updated'));
            redirect(site_url('admin/blog'), 'refresh');
        } elseif ($param1 == 'delete') {
            $this->crud_model->blog_delete($param2);
            $this->session->set_flashdata('flash_message', get_phrase('blog_deleted_successfully'));
            redirect(site_url('admin/blog'), 'refresh');
        }
        $page_data['blogs']      = $this->crud_model->get_blogs();
        $page_data['page_title'] = get_phrase('blog');
        $page_data['page_name']  = 'blog';
        $this->load->view('backend/index', $page_data);
    }

    public function instructors_pending_blog($param1 = "", $param2 = "")
    {
        if ($param1 == 'approval_request') {
            $this->crud_model->approve_blog($param2);
            $this->session->set_flashdata('flash_message', get_phrase('the_blog_has_been_approved'));
            redirect(site_url('admin/instructors_pending_blog'), 'refresh');
        } elseif ($param1 == 'delete') {
            $this->crud_model->blog_delete($param2);
            $this->session->set_flashdata('flash_message', get_phrase('blog_deleted_successfully'));
            redirect(site_url('admin/instructors_pending_blog'), 'refresh');
        }
        $page_data['pending_blogs'] = $this->crud_model->get_instructors_pending_blog();
        $page_data['page_title']    = get_phrase('instructors_pending_blog');
        $page_data['page_name']     = 'instructors_pending_blog';
        $this->load->view('backend/index', $page_data);
    }

    public function blog_settings($param1 = "")
    {
        if ($param1 == 'update') {
            $this->crud_model->update_blog_settings();
            $this->session->set_flashdata('flash_message', get_phrase('blog_settings_updated_successfully'));
            redirect(site_url('admin/blog_settings'), 'refresh');
        }
        $page_data['page_title'] = get_phrase('blog_settings');
        $page_data['page_name']  = 'blog_settings';
        $this->load->view('backend/index', $page_data);
    }
    //End blog

    public function drip_content_settings($param1 = "")
    {
        if ($param1 == 'update') {
            $this->crud_model->save_drip_content_settings();
            $this->session->set_flashdata('flash_message', get_phrase('drip_content_settings_updated_successfully'));
            redirect(site_url('admin/drip_content_settings'), 'refresh');
        }
        $page_data['drip_content_settings'] = json_decode(get_settings('drip_content_settings'), true);
        $page_data['page_title']            = get_phrase('drip_content_settings');
        $page_data['page_name']             = 'drip_content_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function custom_page($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            $this->crud_model->add_custom_page();
            $this->session->set_flashdata('flash_message', get_phrase('new_page_added_successfully'));
            redirect(site_url('admin/custom_page'), 'refresh');
        }

        if ($param1 == 'update') {
            $this->crud_model->update_custom_page($param2);
            $this->session->set_flashdata('flash_message', get_phrase('page_updated_successfully'));
            redirect(site_url('admin/custom_page'), 'refresh');
        }

        if ($param1 == 'delete') {
            $this->crud_model->delete_custom_page($param2);
            $this->session->set_flashdata('flash_message', get_phrase('page_deleted_successfully'));
            redirect(site_url('admin/custom_page'), 'refresh');
        }

        $page_data['custom_pages'] = $this->crud_model->get_custom_pages();
        $page_data['page_title']   = get_phrase('custom_pages');
        $page_data['page_name']    = 'custom_page';
        $this->load->view('backend/index', $page_data);
    }

    public function add_custom_page($custom_page_id = "")
    {
        $page_data['page_title'] = get_phrase('add_custom_page');
        $page_data['page_name']  = 'add_custom_page';
        $this->load->view('backend/index', $page_data);
    }

    public function edit_custom_page($custom_page_id = "")
    {
        $page_data['custom_page'] = $this->crud_model->get_custom_pages($custom_page_id)->row_array();
        $page_data['page_title']  = get_phrase('edit_custom_page');
        $page_data['page_name']   = 'edit_custom_page';
        $this->load->view('backend/index', $page_data);
    }

    //Start data center
    public function data_center()
    {

        $page_data['page_title'] = get_phrase('data_center');
        $page_data['page_name']  = 'data_center';
        $this->load->view('backend/index', $page_data);
    }
    //End of data center

    //Select 2 server-side user data
    public function get_select2_user_data($default = "")
    {
        $response = [];
        $result   = $this->db->where('role_id !=', 1)->group_start()->like('first_name', $_GET['searchVal'])->or_like('last_name', $_GET['searchVal'])->or_like('email', $_GET['searchVal'])->group_end()->limit(100)->get('users')->result_array();
        if ($default != '') {
            $response[] = [['id' => $default, 'text' => get_phrase($default)]];
        }
        foreach ($result as $key => $row) {
            $response[] = ['id' => $row['id'], 'text' => $row['first_name'] . ' ' . $row['last_name'] . '(' . $row['email'] . ')'];
        }
        echo json_encode($response);
    }
    //Select 2 server-side user data
    public function get_select2_instructor_data($default = "")
    {
        $response = [];
        $result   = $this->db->where('is_instructor', 1)->group_start()->like('first_name', $_GET['searchVal'])->or_like('last_name', $_GET['searchVal'])->or_like('email', $_GET['searchVal'])->group_end()->limit(100)->get('users')->result_array();
        if ($default != '') {
            $response[] = [['id' => $default, 'text' => get_phrase($default)]];
        }
        foreach ($result as $key => $row) {
            $response[] = ['id' => $row['id'], 'text' => $row['first_name'] . ' ' . $row['last_name'] . ' (' . $row['email'] . ')'];
        }
        echo json_encode($response);
    }

    //Select 2 server-side enrollable data
    public function get_select2_course_for_enroll($default = "")
    {
        $response = [];
        $result   = $this->db->group_start()->where('status', 'active')->or_where('status', 'private')->group_end()->group_start()->like('title', $_GET['searchVal'])->or_like('description', $_GET['searchVal'])->group_end()->limit(100)->get('course')->result_array();
        if ($default != '') {
            $response[] = [['id' => $default, 'text' => get_phrase($default)]];
        }
        foreach ($result as $key => $row) {
            $user       = $this->user_model->get_all_user($row['creator'])->row_array();
            $response[] = ['id' => $row['id'], 'text' => $row['title'] . ' (' . get_phrase('Creator') . ': ' . $user['first_name'] . ' ' . $user['last_name'] . ')'];
        }
        echo json_encode($response);
    }

    //Select 2 server-side general data
    public function get_select2_general_course($default = "")
    {
        $response = [];
        $result   = $this->db->where('course_type', 'general')->group_start()->like('title', $_GET['searchVal'])->or_like('description', $_GET['searchVal'])->group_end()->limit(100)->get('course')->result_array();
        if ($default != '') {
            $response[] = [['id' => $default, 'text' => get_phrase($default)]];
        }
        foreach ($result as $key => $row) {
            $user       = $this->user_model->get_all_user($row['creator'])->row_array();
            $response[] = ['id' => $row['id'], 'text' => $row['title'] . ' (' . get_phrase('Creator') . ': ' . $user['first_name'] . ' ' . $user['last_name'] . ')'];
        }
        echo json_encode($response);
    }

    public function instructor_payment($instructor_id = "")
    {
        $this->payment_model->configure_instructor_payment($instructor_id);
        redirect(site_url('payment'));
    }

    public function open_ai_settings($param1 = "")
    {
        if ($param1 == "update") {
            $this->load->model('addons/ai_model');
            $this->ai_model->update_open_ai_settings();
        }
        $page_data['page_title'] = get_phrase('openai_settings');
        $page_data['page_name']  = 'open_ai_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function ai_img_download()
    {
        $this->load->model('addons/ai_model');
        $this->ai_model->ai_img_download();
    }

    public function chat_gpt()
    {
        if (isset($_POST['service_type']) && ! empty($_POST['service_type'])) {
            $this->load->model('addons/ai_model');
            echo $this->ai_model->chat_gpt();
        } else {
            $this->load->view('backend/admin/chat_gpt');
        }
    }

    public function gpt_assistant()
    {
        $this->load->model('addons/ai_model');
        echo $this->ai_model->gpt_assistant();
    }

    public function upload_theme()
    {
        if (is_array($_FILES) && count($_FILES) > 0) {
            move_uploaded_file($_FILES['theme_zip']['tmp_name'], 'themes/' . $_FILES['theme_zip']['name']);
            redirect(site_url('admin/theme_settings'), 'refresh');
        }
        $this->load->view('backend/admin/upload_theme');
    }

    public function delete_course_review($rating_id = "")
    {
        $query          = $this->db->where('id', $rating_id);
        $course_details = $this->db->where('id', $query->get('rating')->row('ratable_id'))->get('course')->row_array();
        $this->db->where('id', $rating_id)->delete('rating');

        $this->session->set_flashdata('flash_message', get_phrase('user_review_deleted_successfully'));
        redirect(site_url('home/course/' . slugify($course_details['title']) . '/' . $course_details['id']), 'refresh');
    }

    //Start Notification
    public function get_my_notification($type = "")
    {
        $user_id = $this->session->userdata('user_id');

        if ($type == 'mark_all_as_read') {
            $this->db->where('to_user', $user_id);
            $this->db->update('notifications', ['status' => 1, 'updated_at' => time()]);
        }

        if ($type == 'remove_all') {
            $this->db->where('to_user', $user_id);
            $this->db->delete('notifications');
        }

        $this->db->where('to_user', $user_id);
        $this->db->limit(50);
        $this->db->order_by('status ASC, id desc');
        $page_data['notifications'] = $this->db->get('notifications');

        $unread_count = $this->db->where('to_user', $user_id)->where('status', 0)->get('notifications')->num_rows();

        if ($unread_count > 0):
            $response['notification_icon_class'] = 'noti-icon-badge';
        else:
            $response['notification_icon_class'] = '';
        endif;
        $response['unread_count'] = $unread_count;

        // Count unread messages for sidebar badge
        $this->db->where('receiver', $user_id);
        $this->db->where('read_status !=', 1);
        $response['unread_message_count'] = $this->db->get('message')->num_rows();

        // Newest unread message notification for the popup alert
        $response['message_alert'] = $this->crud_model->get_message_alert($user_id, intval($this->input->get('since')), 'admin');

        $response['rendered_view'] = $this->load->view('backend/header_notification', $page_data, true);

        echo json_encode($response);
    }
    //End notification

    public function language_import()
    {
        $this->load->dbforge();

        foreach ($_FILES['language_files']['name'] as $key => $language) {
            $language_name = strtolower(preg_replace('/\s+/', '_', explode('.', $_FILES['language_files']['name'][$key])[0]));
            //Create language column if not exist
            if (! $this->db->field_exists($language_name, 'language')) {
                $fields = [
                    $language_name => [
                        'type'      => 'LONGTEXT',
                        'default'   => null,
                        'null'      => true,
                        'collation' => 'utf8_unicode_ci',
                    ],
                ];
                $this->dbforge->add_column('language', $fields);
            }

            $language_content_arr = json_decode(file_get_contents($_FILES['language_files']['tmp_name'][$key]), true);
            if (is_array($language_content_arr)) {
                //Upload the json file
                move_uploaded_file($_FILES['language_files']['tmp_name'][$key], 'application/language/' . $language_name . '.json');
            } else {
                $this->session->set_flashdata('error_message', get_phrase('JSON_validation_failed') . '!');
                redirect(site_url('admin/manage_language'), 'refresh');
            }

            foreach ($language_content_arr as $phrase_key => $phrase) {
                $phrase_key = strtolower(preg_replace('/\s+/', '_', $phrase_key));
                $query      = $this->db->get_where('language', ['phrase' => $phrase_key]);

                if ($query->num_rows() > 0) {
                    $this->db->where('phrase', $phrase_key);
                    $this->db->update('language', [$language_name => $phrase]);
                } else {
                    $this->db->insert('language', ['phrase' => $phrase_key, $language_name => $phrase]);
                }
            }
        }

        $this->session->set_flashdata('flash_message', get_phrase('language_file_import_successfully'));
        redirect(site_url('admin/manage_language'), 'refresh');
    }

    public function export_language($language)
    {
        $this->load->helper('download');
        $language     = strtolower($language);
        $json_content = [];

        foreach ($this->db->get('language')->result_array() as $row) {
            $json_content[$row['phrase']] = $row[$language];
        }
        force_download($language . '.json', json_encode($json_content));
    }

    public function subscribed_user($type = "", $id = "")
    {
        if ($type == 'delete') {
            $this->db->where('id', $id)->delete('newsletter_subscriber');
            $this->session->set_flashdata('flash_message', get_phrase('Newsletter subscription deleted successfully'));
            redirect(site_url('admin/subscribed_user'), 'refresh');
        }

        if ($_POST) {
            $data = [];
            //mentioned all with colum of database table that related with html table
            $columns = ['id', 'email', 'id', 'id'];

            $limit = htmlspecialchars_($this->input->post('length'));
            $start = htmlspecialchars_($this->input->post('start'));

            $column_index = $columns[$this->input->post('order')[0]['column']];

            $dir                 = $this->input->post('order')[0]['dir'];
            $total_number_of_row = $this->db->get('newsletter_subscriber')->num_rows();

            $filtered_number_of_row = $total_number_of_row;
            $search                 = $this->input->post('search')['value'];

            if (empty($search)) {
                $this->db->select('*');
                $this->db->limit($limit, $start);
                $this->db->order_by($column_index, $dir);
                $newsletter_subscriber = $this->db->get('newsletter_subscriber')->result_array();
            } else {
                $this->db->select('*');
                $this->db->like('email', $search);
                $this->db->limit($limit, $start);
                $this->db->order_by($column_index, $dir);
                $newsletter_subscriber = $this->db->get('newsletter_subscriber')->result_array();

                $filtered_number_of_row = count($newsletter_subscriber);
            }

            foreach ($newsletter_subscriber as $key => $row):
                $user_row = $this->db->where('email', $row['email'])->get('users');
                //user email
                $email = $row['email'];

                if ($user_row->num_rows() > 0) {
                    if ($user_row->row('is_instructor') != 1) {
                        $user_status = '<p class="my-0">' . $user_row->row('first_name') . ' ' . $user_row->row('last_name') . '</p>';
                        $user_status .= '<span class="badge badge-primary">' . get_phrase('Pharmacist') . '</span>';
                    } else {
                        $user_status = '<p class="my-0">' . $user_row->row('first_name') . ' ' . $user_row->row('last_name') . '</p>';
                        $user_status .= '<span class="badge badge-success">' . get_phrase('Instructor') . '</span>';
                    }
                } else {
                    $user_status = '<span class="badge badge-warning">' . get_phrase('Not registered') . '</span>';
                }

                $action = '<div class="dropright dropright">
		                                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                    <i class="mdi mdi-dots-vertical"></i>
		                                </button>
		                                <ul class="dropdown-menu">
		                                    <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/subscribed_user/delete/' . $row['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                                </ul>
		                            </div>';

                $nestedData['key']         = ++$key;
                $nestedData['email']       = $email;
                $nestedData['user_status'] = $user_status;
                $nestedData['action']      = $action . '<script>$("a, i").tooltip();</script>';
                $data[]                    = $nestedData;
            endforeach;

            $json_data = [
                "draw"            => intval($this->input->post('draw')),
                "recordsTotal"    => intval($total_number_of_row),
                "recordsFiltered" => intval($filtered_number_of_row),
                "data"            => $data,
            ];
            echo json_encode($json_data);
        } else {
            $page_data['page_name']  = 'subscribed_user';
            $page_data['page_title'] = get_phrase('Subscribed user');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function newsletter_add_form()
    {
        $this->load->view('backend/admin/add_newsletter');
    }

    public function newsletter_edit_form($id)
    {
        $page_data['newsletter'] = $this->db->where('id', $id)->get('newsletters')->row_array();
        $this->load->view('backend/admin/edit_newsletter', $page_data);
    }

    public function newsletter_send_form($id)
    {
        $page_data['newsletter'] = $this->db->where('id', $id)->get('newsletters')->row_array();
        $this->load->view('backend/admin/send_newsletter', $page_data);
    }

    public function newsletters($type = "", $id = "")
    {
        if ($type == 'add') {
            $this->crud_model->add_newsletter();
            $this->session->set_flashdata('flash_message', get_phrase('Newsletter added successfully'));
            redirect(site_url('admin/newsletters'), 'refresh');
        }
        if ($type == 'edit') {
            $this->crud_model->update_newsletter($id);
            $this->session->set_flashdata('flash_message', get_phrase('Newsletter updated successfully'));
            redirect(site_url('admin/newsletters?tab=' . $id), 'refresh');
        }

        if ($type == 'send') {
            $to = [];

            $subject     = $this->input->post('subject');
            $description = $this->input->post('description', false);
            $send_to     = $this->input->post('send_to');

            if ($send_to == 'all') {
                $all_users = $this->db->where('status', 1)->where('role_id !=', 1)->get('users')->result_array();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } elseif ($send_to == 'student') {
                $all_users = $this->db->where('status', 1)->where('role_id !=', 1)->where('is_instructor !=', 1)->get('users')->result_array();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } elseif ($send_to == 'instructor') {
                $all_users = $this->db->where('status', 1)->where('role_id !=', 1)->where('is_instructor', 1)->get('users')->result_array();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } elseif ($send_to == 'all_subscriber') {
                $all_subscriber = $this->db->get('newsletter_subscriber')->result_array();
                foreach ($all_subscriber as $key => $subscriber):
                    $to[] = $subscriber['email'];
                endforeach;
            } elseif ($send_to == 'registered_subscriber') {
                $all_subscriber = $this->db->get('newsletter_subscriber')->result_array();
                foreach ($all_subscriber as $key => $subscriber):
                    $registration = $this->db->where('status', 1)->where('email', $subscriber['email'])->get('users');
                    if ($registration->num_rows() > 0) {
                        $to[] = $subscriber['email'];
                    }
                endforeach;
            } elseif ($send_to == 'non_registered_subscriber') {
                $all_subscriber = $this->db->get('newsletter_subscriber')->result_array();
                foreach ($all_subscriber as $key => $subscriber):
                    $registration = $this->db->where('status', 1)->where('email', $subscriber['email'])->get('users');
                    if ($registration->num_rows() == 0) {
                        $to[] = $subscriber['email'];
                    }
                endforeach;
            } elseif ($send_to == 'selected_user') {
                $user_ids  = $this->input->post('user_id');
                $all_users = $this->db->where_in('id', $user_ids)->get('users')->result_array();
                foreach ($all_users as $key => $all_user):
                    $to[] = $all_user['email'];
                endforeach;
            } else {
                $this->session->set_flashdata('error_message', get_phrase('You must select at least one single user'));
                redirect(site_url('admin/newsletters'), 'refresh');
            }

            $email_data['subject'] = $subject;
            $email_data['message'] = $description;
            $email_template        = $this->load->view('email/static_common_template', $email_data, true);

            $this->crud_model->assignEmailToSendList($to, $subject, $email_template);

            //$this->email_model->send_smtp_mail($email_template, $subject, $to);//
            $this->session->set_flashdata('flash_message', get_phrase('Users are assigned to newsletter mailing list') . ' ' . get_phrase('Please wait'));
            redirect(site_url('admin/newsletters'), 'refresh');
        }

        if ($type == 'delete') {
            $this->crud_model->delete_newsletter($id);
            $this->session->set_flashdata('flash_message', get_phrase('Newsletter deleted successfully'));
            redirect(site_url('admin/newsletters'), 'refresh');
        }
        $page_data['page_name']  = 'newsletters';
        $page_data['page_title'] = get_phrase('Newsletters');
        $this->load->view('backend/index', $page_data);
    }

    public function newsletter_history($type = "", $id = "")
    {

        if ($_POST) {
            $data = [];
            //mentioned all with colum of database table that related with html table
            $columns = ['id', 'subject', 'email', 'id', 'id'];

            $limit = htmlspecialchars_($this->input->post('length'));
            $start = htmlspecialchars_($this->input->post('start'));

            $column_index = $columns[$this->input->post('order')[0]['column']];

            $dir                 = $this->input->post('order')[0]['dir'];
            $total_number_of_row = $this->db->where('status', $type)->get('newsletter_histories')->num_rows();

            $filtered_number_of_row = $total_number_of_row;
            $search                 = $this->input->post('search')['value'];

            if (empty($search)) {
                $this->db->select('*');
                $this->db->where('status', $type);
                $this->db->limit($limit, $start);
                $this->db->order_by($column_index, $dir);
                $newsletter_histories = $this->db->get('newsletter_histories')->result_array();
            } else {
                $this->db->select('*');
                $this->db->like('email', $search);
                $this->db->or_like('subject', $search);
                $this->db->or_like('description', $search);

                $this->db->group_start();
                $this->db->where('status', $type);
                $this->db->group_end();

                $this->db->limit($limit, $start);
                $this->db->order_by($column_index, $dir);
                $newsletter_histories = $this->db->get('newsletter_histories')->result_array();

                $filtered_number_of_row = count($newsletter_histories);
            }

            foreach ($newsletter_histories as $key => $row):
                if ($row['status'] != 'sent') {
                    $action = '<a class="btn btn-primary" href="javascript:void(0)" onclick="actionTo(&#39;' . site_url('admin/newsletter_history/send/' . $row['id']) . '&#39;);">' . get_phrase('Send') . '</a>';
                    if ($row['status'] == 'faild') {
                        $status = '<span class="text-capitalize text-danger">' . $row['status'] . '</span>';
                    } elseif ($row['status'] == 'pending') {
                    $status = '<span class="text-capitalize text-warning">' . $row['status'] . '</span>';
                } else {
                    $status = '<span class="text-capitalize text-secondary">' . $row['status'] . '</span>';
                }
            } else {
                $action = '<a class="dropdown-item" href="javascript:void(0)" onclick="actionTo(&#39;' . site_url('admin/newsletter_history/send/' . $row['id']) . '&#39;);">' . get_phrase('Send Again') . '</a>';
                $status = '<span class="text-capitalize text-success">' . $row['status'] . '</span>';
            }

            $nestedData['key']     = ++$key;
            $nestedData['subject'] = $row['subject'];
            $nestedData['email']   = $row['email'];
            $nestedData['status']  = $status;
            $nestedData['action']  = $action;
            $data[]                = $nestedData;
            endforeach;

            $json_data = [
                "draw"            => intval($this->input->post('draw')),
                "recordsTotal"    => intval($total_number_of_row),
                "recordsFiltered" => intval($filtered_number_of_row),
                "data"            => $data,
            ];
            echo json_encode($json_data);
        } elseif ($type == "send") {
            $this->db->where('id', $id);
            $newsletter_history = $this->db->get('newsletter_histories')->row_array();
            $response           = $this->email_model->send_smtp_mail($newsletter_history['description'], $newsletter_history['subject'], $newsletter_history['email']);

            if ($response) {
                $this->db->where('id', $id);
                $newsletter_history = $this->db->update('newsletter_histories', ['status' => 'sent']);
                $sending_response   = [
                    'run_function' => 'refreshTable',
                    'success'      => get_phrase('Mail sent successfully'),
                ];
                echo json_encode($sending_response);
            } else {
                $sending_response = [
                    'error' => get_phrase('Failed to send mail'),
                ];
                echo json_encode($sending_response);
            }
        } else {
            $page_data['type']       = $type;
            $page_data['page_name']  = 'newsletter_history';
            $page_data['page_title'] = get_phrase('Newsletter history');
            $this->load->view('backend/index', $page_data);
        }
    }

    public function newsletter_statistics()
    {
        echo $this->load->view('backend/admin/newsletter_statistics', [], true);
    }

    public function student_academic_progress($course_id = "")
    {
        $course_details    = $this->crud_model->get_course_by_id($course_id)->row_array();
        $multi_instructors = explode(',', $course_details['user_id']);

        $page_data['course_details'] = $course_details;
        $this->load->view('backend/admin/student_academic_progress', $page_data);
    }

    public function student_academic_quiz_result($course_id = "", $student_id = "")
    {
        $course_details    = $this->crud_model->get_course_by_id($course_id)->row_array();
        $multi_instructors = explode(',', $course_details['user_id']);

        if ($this->session->userdata('admin_login') != 1 && ! in_array($this->session->userdata('user_id'), $multi_instructors)) {
            return false;
        }

        $page_data['course_details'] = $course_details;
        $page_data['student_id']     = $student_id;
        $this->load->view('backend/admin/student_academic_quiz_result', $page_data);
    }

    public function home_page_layout($home_page = "")
    {
        $this->db->where('key', 'home_page');
        $this->db->update('frontend_settings', ['value' => $home_page]);
        $this->session->set_flashdata('flash_message', get_phrase('New home page layout has been activated'));
        redirect(site_url('admin/home_page_builder'), 'refresh');
    }

    public function student_certificate($user_id = "", $course_id = "")
    {
        $this->load->model('addons/Certificate_model', 'certificate_model');
        $course_progress = $this->crud_model->get_watch_histories($user_id, $course_id)->row('course_progress');
        if ($course_progress >= 100) {
            $this->certificate_model->check_certificate_eligibility($course_id, $user_id);
            $certificate = $this->db->get_where('certificates', ['course_id' => $course_id, 'student_id' => $user_id]);
            redirect(site_url('certificate/' . $certificate->row('shareable_url')));
        } else {
            $this->session->set_flashdata('error_message', get_phrase('The course is not compleated yet'));
            redirect(site_url('admin/course_form/course_edit/' . $course_id . '?tab=academic_progress'));
        }
    }

    public function contact($type = "", $id = "")
    {
        if ($type == 'delete_selected_contact') {
            $selected_ids = $this->input->get('selected_ids'); // Assuming selected_ids are passed via GET
            $ids          = explode(',', $selected_ids);       // Convert the comma-separated string to an array

            if (! empty($ids)) {
                $this->db->where_in('id', $ids)->delete('contact');
                $this->session->set_flashdata('flash_message', get_phrase('Contacts deleted successfully'));
            } else {
                $this->session->set_flashdata('flash_message', get_phrase('No contacts selected for deletion'));
            }

            redirect(site_url('admin/contact'), 'refresh');
        }

        if ($type == 'delete') {
            $this->db->where('id', $id)->delete('contact');
            $this->session->set_flashdata('flash_message', get_phrase('Contact deleted successfully'));
            redirect(site_url('admin/contact'), 'refresh');
        }

        if ($type == '') {
            $page_data['page_name']  = 'contact';
            $page_data['page_title'] = get_phrase('Contact');
            $this->load->view('backend/index', $page_data);
        }

        if ($type == 'contact_reply_form' && $id != '') {
            $page_data['contact'] = $this->crud_model->get_contacts($id)->row_array();
            $this->load->view('backend/admin/contact_reply_form', $page_data);
        }

        if ($type == 'send_reply' && $id != '') {
            $message         = $this->input->post('reply_message');
            $contact_details = $this->crud_model->get_contacts($id)->row_array();
            $this->email_model->send_smtp_mail($message, get_phrase('Reply from - ') . get_settings('system_name'), $contact_details['email']);
            $this->db->where('id', $id)->update('contact', ['replied' => 1]);
            $this->session->set_flashdata('flash_message', get_phrase('Reply sent successfully'));
            redirect(site_url('admin/contact'), 'refresh');
        }

        if ($type == 'data-table' && $_GET) {
            $this->db->where('has_read', null)->update('contact', ['has_read' => 1]);

            $data = [];
            //mentioned all with colum of database table that related with html table
            $columns = ['id', 'first_name', 'email', 'message', 'id'];

            $limit = htmlspecialchars_($this->input->get('length'));
            $start = htmlspecialchars_($this->input->get('start'));

            $column_index = $columns[$this->input->get('order')[0]['column']];

            $dir                 = $this->input->get('order')[0]['dir'];
            $total_number_of_row = $this->db->get('contact')->num_rows();

            $filtered_number_of_row = $total_number_of_row;
            $search                 = $this->input->get('search')['value'];

            if (empty($search)) {
                $this->db->limit($limit, $start);
                $this->db->order_by($column_index, $dir);
                $contacts = $this->db->get('contact')->result_array();
            } else {
                $this->db->like('first_name', $search);
                $this->db->or_like('last_name', $search);
                $this->db->or_like('email', $search);
                $this->db->or_like('phone', $search);
                $this->db->or_like('address', $search);
                $this->db->or_like('message', $search);
                $this->db->limit($limit, $start);
                $this->db->order_by($column_index, $dir);
                $contacts               = $this->db->get('contact');
                $filtered_number_of_row = $contacts->num_rows();
                $contacts               = $contacts->result_array();
            }

            foreach ($contacts as $key => $row):
                if ($row['replied'] == 1):
                    $reply_sent = ' <i class="fas fa-check-circle text-success" title="' . get_phrase('Reply sent') . '" data-toggle="tooltip"></i>';
                else:
                    $reply_sent = '';
                endif;

                $user_row = $this->db->where('email', $row['email'])->get('users');
                if ($user_row->num_rows() > 0) {
                    if ($user_row->row('is_instructor') != 1) {
                        $user_status = '<span class="badge badge-primary">' . get_phrase('Pharmacist') . '</span>';
                    } else {
                        $user_status = '<span class="badge badge-success">' . get_phrase('Instructor') . '</span>';
                    }
                } else {
                    $user_status = '<span class="badge badge-warning">' . get_phrase('Not registered') . '</span>';
                }

                $contact_info = '<p class="my-0">' . get_phrase('Email') . ': <a href="mailto:' . $row['email'] . '">' . $row['email'] . '</a></p>';
                if ($row['phone'] != '') {
                    $contact_info .= '<p class="my-0">' . get_phrase('Phone') . ': <a href="tel:' . $row['phone'] . '">' . $row['phone'] . '</a></p>';
                }
                if ($row['address'] != '') {
                    $contact_info .= '<p class="my-0">' . $row['address'] . '</a></p>';
                }

                $action = '<div class="dropright dropright">
		                                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                                    <i class="mdi mdi-dots-vertical"></i>
		                                </button>
		                                <ul class="dropdown-menu">
		                                    <li><a class="dropdown-item" href="#" onclick="showAjaxModal(&#39;' . site_url('admin/contact/contact_reply_form/' . $row['id']) . '&#39;, &#39;' . get_phrase('Reply to ' . $row['first_name'] . ' ' . $row['last_name']) . '&#39;);">' . get_phrase('Reply') . '</a></li>
		                                    <li><a class="dropdown-item" href="#" onclick="confirm_modal(&#39;' . site_url('admin/contact/delete/' . $row['id']) . '&#39;);">' . get_phrase('delete') . '</a></li>
		                                </ul>
		                            </div>';

                $nestedData['checkbox'] = '<input type="checkbox" name="selected_contacts[]" value="' . $row['id'] . '" data-row-id="' . $row['id'] . '">';
                $nestedData['key']      = ++$key;
                $nestedData['name']     = '<p class="my-0">' . $row['first_name'] . ' ' . $row['last_name'] . $reply_sent . '</p>' . $user_status;
                $nestedData['contact']  = $contact_info;
                $nestedData['message']  = $row['message'];
                $nestedData['action']   = $action . '<script>$("a, i").tooltip();</script>';
                $data[]                 = $nestedData;
            endforeach;

            $json_data = [
                "draw"            => intval($this->input->post('draw')),
                "recordsTotal"    => intval($total_number_of_row),
                "recordsFiltered" => intval($filtered_number_of_row),
                "data"            => $data,
            ];
            echo json_encode($json_data);
        }
    }

    public function update_language_direction()
    {
        $language      = $this->input->post('language');
        $dir           = $this->input->post('dir');
        $language_dirs = get_settings('language_dirs') ? json_decode(get_settings('language_dirs'), true) : ['english' => 'ltr'];

        $language_dirs[$language] = $dir;

        $data['value'] = json_encode($language_dirs);

        if ($this->db->get_where('settings', ['key' => 'language_dirs'])->num_rows() > 0) {
            $this->db->where('key', 'language_dirs')->update('settings', $data);
        } else {
            $data['key'] = 'language_dirs';
            $this->db->insert('settings', $data);
        }
        echo get_phrase('Language direction updated successfully');
    }

    public function resource_files($param1 = "", $param2 = "")
    {
        if ($param1 == 'add') {
            if (isset($_FILES['resource_file']['name']) && $_FILES['resource_file']['name'] != "") {
                $data['file_name'] = random(20) . '.' . pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['resource_file']['tmp_name'], 'uploads/resource_files/' . $data['file_name']);
            }

            $data['title']      = $this->input->post('title');
            $data['lesson_id']  = $param2;
            $data['created_at'] = time();
            $this->db->insert('resource_files', $data);

            $response['replace'] = ['elem' => '.resource_file_content', 'content' => $this->load->view('backend/admin/resource_files', ['param2' => $param2], true)];
            echo json_encode($response);
        } elseif ($param1 == 'update') {
            $file_details = $this->db->get_where('resource_files', ['id' => $param2])->row_array();
            if (isset($_FILES['resource_file']['name']) && $_FILES['resource_file']['name'] != "") {
                if (file_exists('uploads/resource_files/' . $file_details['file_name']) && $file_details['file_name']) {
                    unlink('uploads/resource_files/' . $file_details['file_name']);
                }
                $data['file_name'] = random(20) . '.' . pathinfo($_FILES['resource_file']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['resource_file']['tmp_name'], 'uploads/resource_files/' . $data['file_name']);
            }

            $data['title']      = $this->input->post('title');
            $data['updated_at'] = time();
            $this->db->where('id', $param2);
            $this->db->update('resource_files', $data);

            $response['replace'] = ['elem' => '.resource_file_content', 'content' => $this->load->view('backend/admin/resource_files', ['param2' => $file_details['lesson_id']], true)];
            echo json_encode($response);
        } elseif ($param1 == 'delete') {
            $file_details = $this->db->get_where('resource_files', ['id' => $param2])->row_array();
            if (file_exists('uploads/resource_files/' . $file_details['file_name']) && $file_details['file_name']) {
                unlink('uploads/resource_files/' . $file_details['file_name']);
            }

            $this->db->where('id', $param2);
            $this->db->delete('resource_files');

            $response['replace'] = ['elem' => '.resource_file_content', 'content' => $this->load->view('backend/admin/resource_files', ['param2' => $file_details['lesson_id']], true)];
            $response['success'] = get_phrase('Resource deleted successfully');
            $response['fadeOut'] = '#resource_file_' . $file_details['id'];
            echo json_encode($response);
        }
    }

    public function cronjob($type = "")
    {
        // Write some content to the cron file for CURL call.
        $content = '<?php
        $url = "' . base_url("home/sendEmailToAssignedAddresses") . '";
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL session
        $response = curl_exec($ch);

        // Close cURL session
        curl_close($ch);';

        //White this file using curl ($content) content
        $newsletter_cron_file_path = "uploads/cronjob/newsletter_cron.php";
        if (file_exists($newsletter_cron_file_path)) {
            unlink($newsletter_cron_file_path);
        }

        // //CRON CONTENTS
        // // Get PHP Binary Path
        // $setInterval_1m = '* * * * *'; // for every 1 minute
        // //$phpIniFile = php_ini_loaded_file();
        // $phpBinaryPath = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
        // // Get the application path
        // $applicationPath = realpath(APPPATH . '..') . '/' . $newsletter_cron_file_path;

        // Get PHP Binary Path
        $phpSapi       = php_sapi_name();
        $phpBinaryPath = ($phpSapi === 'cli') ? PHP_BINARY : PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
                                        // Rest of your code remains unchanged
        $setInterval_1m  = '* * * * *'; // for every 1 minute
        $applicationPath = realpath(APPPATH . '..') . '/' . $newsletter_cron_file_path;

        if ($type == 'start') {
            if (! is_dir('uploads/cronjob')) {
                mkdir('uploads/cronjob', 0777, true);
            }

            // Open the file for writing (creates the file if it doesn't exist)
            $fileHandle = fopen($newsletter_cron_file_path, "w");
            // Check if the file was opened successfully
            if ($fileHandle) {
                fwrite($fileHandle, $content);
                // Close the file handle
                fclose($fileHandle);
            } else {
                $this->session->set_flashdata('error_message', get_phrase("Failed to create the cron file") . '. File path:' . $newsletter_cron_file_path);
                redirect(site_url('admin/newsletters'), 'refresh');
            }

            // Execute Shell Command
            // $cronCommand = 'crontab -l | { cat; echo "' . $setInterval_1m . ' ' . $phpBinaryPath . ' ' . $applicationPath . '"; } | crontab -';
            // exec($cronCommand, $output, $return_var);

            // if ($return_var !== 0) {
            //     unlink($newsletter_cron_file_path);
            //     $this->session->set_flashdata('error_message', get_phrase('Cron job setup failed') . ' Output:' . implode("\n", $output));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // } else {
            //     $this->session->set_flashdata('flash_message', get_phrase('Cron job successfully set up'));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // }

            redirect(site_url('admin/newsletters'), 'refresh');
        } elseif ($type == 'stop') {
            // Remove Cron Job
            // $cronCommandRemove = 'crontab -l | grep -v "' . $phpBinaryPath . ' ' . $applicationPath . '" | crontab -';
            // exec($cronCommandRemove, $outputRemove, $returnVarRemove);

            // if ($returnVarRemove !== 0) {
            //     $this->session->set_flashdata('error_message', get_phrase('Cron job removal failed') . ' Output:' . implode("\n", $outputRemove));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // } else {
            //     $this->session->set_flashdata('flash_message', get_phrase('Cron job successfully removed'));
            //     redirect(site_url('admin/newsletters'), 'refresh');
            // }

            $newsletter_cron_file_path = "uploads/cronjob/newsletter_cron.php";
            unlink($newsletter_cron_file_path);
            redirect(site_url('admin/newsletters'), 'refresh');
        }
    }

    public function wasabi_settings($type = '')
    {

        if ($type == 'update') {
            if ($this->db->where('key', 'wasabi_key')->get('settings')->num_rows() > 0) {
                $data['value'] = $this->input->post('access_key');
                $this->db->where('key', 'wasabi_key');
                $this->db->update('settings', $data);
            } else {
                $data['value'] = $this->input->post('access_key');
                $data['key']   = 'wasabi_key';
                $this->db->insert('settings', $data);
            }

            if ($this->db->where('key', 'wasabi_secret_key')->get('settings')->num_rows() > 0) {
                $data['value'] = $this->input->post('secret_key');
                $this->db->where('key', 'wasabi_secret_key');
                $this->db->update('settings', $data);
            } else {
                $data['value'] = $this->input->post('secret_key');
                $data['key']   = 'wasabi_secret_key';
                $this->db->insert('settings', $data);
            }

            if ($this->db->where('key', 'wasabi_bucketname')->get('settings')->num_rows() > 0) {
                $data['value'] = $this->input->post('bucket_name');
                $this->db->where('key', 'wasabi_bucketname');
                $this->db->update('settings', $data);
            } else {
                $data['value'] = $this->input->post('bucket_name');
                $data['key']   = 'wasabi_bucketname';
                $this->db->insert('settings', $data);
            }

            if ($this->db->where('key', 'wasabi_region')->get('settings')->num_rows() > 0) {
                $data['value'] = $this->input->post('region_name');
                $this->db->where('key', 'wasabi_region');
                $this->db->update('settings', $data);
            } else {
                $data['value'] = $this->input->post('region_name');
                $data['key']   = 'wasabi_region';
                $this->db->insert('settings', $data);
            }

            $this->session->set_flashdata('flash_message', get_phrase('Wasabi Settings Updated Successfully'));
            redirect(site_url('admin/wasabi_settings'), 'refresh');
        }

        $page_data['page_name']  = 'wasabi_settings';
        $page_data['page_title'] = get_phrase('Wasabi Storage Settings');
        $this->load->view('backend/index', $page_data);
    }

    public function bbb_live_class_settings($type = "")
    {
        if ($type == 'update') {
            $data['value'] = json_encode($_POST);
            if ($this->db->where('key', 'bbb_setting')->get('settings')->num_rows() > 0) {
                $this->db->where('key', 'bbb_setting')->update('settings', $data);
            } else {
                $data['key'] = 'bbb_setting';
                $this->db->insert('settings', $data);
            }
            $this->session->set_flashdata('flash_message', get_phrase('BigBlueButton configuration has been Updated'));
            redirect(site_url('admin/bbb_live_class_settings'), 'refresh');
        }

        $page_data['page_name']  = 'bbb_live_class_settings';
        $page_data['page_title'] = get_phrase('BBB live class settings');
        $this->load->view('backend/index', $page_data);
    }

    public function save_bbb_meeting($course_id = "")
    {
        $data['meeting_id']   = $this->input->post('bbb_meeting_id');
        $data['moderator_pw'] = $this->input->post('bbb_moderator_pw');
        $data['viewer_pw']    = $this->input->post('bbb_viewer_pw');
        $data['instructions'] = $this->input->post('instructions');

        if ($this->db->where('course_id', $course_id)->get('bbb_meetings')->num_rows() > 0) {
            $data['updated_at'] = time();
            $this->db->where('course_id', $course_id)->update('bbb_meetings', $data);
        } else {
            $data['course_id']  = $course_id;
            $data['created_at'] = time();
            $data['updated_at'] = $data['created_at'];
            $this->db->insert('bbb_meetings', $data);
        }

        echo get_phrase("BigBlueButton Meeting has been updated");
    }

    public function start_bbb_meeting($course_id = "")
    {
        $course_details = $this->crud_model->get_courses($course_id)->row_array();
        $bbb_meeting    = $this->db->where('course_id', $course_id)->get('bbb_meetings');
        $current_url    = site_url('admin/course_form/course_edit/' . $course_id . '?tab=bbb-live-class');

        if ($bbb_meeting->num_rows() > 0) {
            $bbb_meeting = $bbb_meeting->row_array();
            //Sanitize API URL START
            $api_url = get_settings('bbb_setting', true)['endpoint'] ?? '';
            // Parse the URL
            $parsed_url = parse_url($api_url);
            // Remove the 'api' part if it exists in the path
            $path = rtrim(str_replace('/api', '', $parsed_url['path']), '/');
            // Rebuild the URL
            $api_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $path;
            //Sanitize API URL END

            //Create BBB meeting START
            $query_data = http_build_query([
                'name'        => $course_details['title'],
                'meetingID'   => $bbb_meeting['meeting_id'],
                'attendeePW'  => $bbb_meeting['viewer_pw'],
                'moderatorPW' => $bbb_meeting['moderator_pw'],
                'redirectURL' => $current_url,
            ]);
            $response = $this->crud_model->callBbbApi('create', $query_data, $bbb_meeting['meeting_id']);
            //Create BBB meeting END

            // Handle response & redirect to meeting url
            if ($response) {
                $xml        = simplexml_load_string($response);
                $returncode = (string) $xml->returncode;

                if ($returncode == 'SUCCESS') {
                    // $moderator_details = $this->user_model->get_all_user($this->session->userdata('user_id'))->row_array();
                    // //JOIN AS A viewer
                    // $full_name = $moderator_details['first_name'].' '.$moderator_details['last_name']; // The full name of the participant
                    // $role = 'moderator'; // The role of the user (either "viewer" or "moderator")
                    // $join_url = $api_url."/api/join?meetingID=".$bbb_meeting['meeting_id']."&fullName=$full_name&password=".$bbb_meeting['moderator_pw']."&joinViaHtml5=true&redirect=true&joinParam[role]=$role";
                    // echo $join_url;
                    // return;
                    echo $this->crud_model->join_bbb_meeting_by_curl_calls($course_id, true);
                    return;
                } else {
                    $this->session->set_flashdata('error_message', get_phrase("Failed to create meeting. Error code: ____", [$returncode]));
                }
            } else {
                $this->session->set_flashdata('error_message', get_phrase("Failed to connect to BigBlueButton API"));
            }
        } else {
            $this->session->set_flashdata('error_message', get_phrase("Please save your meeting info first"));
        }
        echo $current_url;
    }

    public function change_course_author($course_id = "")
    {
        if (isset($_POST) && count($_POST) > 0) {
            if ($_POST['instructor_id'] > 0) {
                $this->db->where('id', $course_id)->update('course', ['creator' => $_POST['instructor_id']]);
                $this->session->set_flashdata('flash_message', get_phrase("Course author changed successfully"));
            } else {
                $this->session->set_flashdata('error_message', get_phrase("Something is wrong"));
            }
            redirect(site_url('admin/course_form/course_edit/' . $course_id . '?tab=basic'), 'refresh');
        } else {
            $page_data['instructors']    = $this->user_model->get_instructor()->result_array();
            $page_data['course_details'] = $this->crud_model->get_course_by_id($course_id)->row_array();
            $this->load->view('backend/admin/change_course_author', $page_data);
        }
    }

    public function seo_settings($param1 = "", $param2 = "")
    {
        if ($param1 == 'update') {
            $this->crud_model->save_seo_settings($param2);
            $this->session->set_flashdata('flash_message', get_phrase('seo_settings_updated_successfully'));
            redirect(site_url('admin/seo_settings/' . $param2), 'refresh');
        }
        $page_data['seo_meta_tags'] = $this->crud_model->get_seo_meta_tags()->result_array();
        $page_data['active_tab']    = ! empty($param1) ? $param1 : 'home';
        $page_data['page_title']    = get_phrase('seo_settings');
        $page_data['page_name']     = 'seo_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function sitemap_settings()
    {
        $blogs           = $this->crud_model->get_all_blogs()->result_array();
        $blog_categories = $this->crud_model->get_blog_categories()->result_array();
        $courses         = $this->crud_model->get_courses()->result_array();
        $categories      = $this->crud_model->get_categories()->result_array();

        // Construct URLs for each blog
        $blog_url_array = [];

        foreach ($blogs as $blog) {
            $slug             = slugify($blog['title']);
            $blog_id          = $blog['blog_id'];
            $url              = base_url("blog/details/$slug/$blog_id");
            $blog_url_array[] = $url;
        }

        // Construct URLs for each blog category
        $blog_category_url_array = [];

        foreach ($blog_categories as $blog_category) {
            $slug                      = $blog_category['slug'];
            $url                       = base_url("blogs?category=$slug");
            $blog_category_url_array[] = $url;
        }

        // Construct URLs for each category
        $category_url_array = [];

        foreach ($categories as $category) {
            $slug                 = $category['slug'];
            $url                  = base_url("home/courses?category=$slug");
            $category_url_array[] = $url;

            // Retrieve subcategories for the current category using its ID
            $sub_categories = $this->crud_model->get_sub_categories($category['id']);

            foreach ($sub_categories as $sub_category) {
                $sub_slug             = $sub_category['slug'];
                $sub_url              = base_url("home/courses?category=$sub_slug");
                $category_url_array[] = $sub_url;
            }
        }

        // Construct URLs for each course
        $course_url_array = [];

        foreach ($courses as $course) {
            $slug               = slugify($course['title']);
            $course_id          = $course['id'];
            $url                = base_url("home/course/$slug/$course_id");
            $course_url_array[] = $url;
        }

        $page_data['sitemap'] = [
            'key'   => 'sitemap_xml',
            'value' => get_settings('sitemap_xml'), // Fetch the sitemap XML content.
        ];
        $page_data['courses']         = $course_url_array;
        $page_data['categories']      = $category_url_array;
        $page_data['blogs']           = $blog_url_array;
        $page_data['blog_categories'] = $blog_category_url_array;
        $page_data['page_title']      = get_phrase('sitemap_settings');
        $page_data['page_name']       = 'sitemap_settings';
        $this->load->view('backend/index', $page_data);
    }

    public function export_enrol_history_csv()
    {
        // Check if the request method is POST
        if ($this->input->method() === 'post') {
            // Get enrol IDs from the request
            $enrol_ids = $this->input->post('enrol_ids');

            // Validate enrol IDs
            if (! is_array($enrol_ids) || empty($enrol_ids)) {
                show_error('No enrol IDs provided.', 400);
            }

            // Fetch enrol data for the provided IDs
            $this->db->where_in('id', $enrol_ids);
            $query = $this->db->get('enrol');

            // Check if data exists
            if ($query->num_rows() === 0) {
                show_error('No data found for the provided enrol IDs.', 404);
            }

            // Prepare the CSV header
            $csv_data = '"Id","Pharmacist Name","Course Title","Purchase Date","Expiry Date"' . "\n";

            // Populate the CSV rows
            foreach ($query->result_array() as $row) {
                // Fetch related user and course data
                $user_data   = $this->db->get_where('users', ['id' => $row['user_id']])->row_array();
                $course_data = $this->db->get_where('course', ['id' => $row['course_id']])->row_array();

                // Add a CSV row
                $csv_data .= '"' . $row['id'] . '",';
                $csv_data .= '"' . $user_data['first_name'] . ' ' . $user_data['last_name'] . '",';
                $csv_data .= '"' . $course_data['title'] . '",';
                $csv_data .= '"' . date('d-m-Y', $row['date_added']) . '",';
                $csv_data .= '"' . ($row['expiry_date'] ? date('d-m-Y', $row['expiry_date']) : 'Lifetime access') . '"' . "\n";
            }

            // Send the CSV data as a response
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="enrol_history.csv"');
            echo $csv_data;
            exit;
        } else {
            show_error('Invalid request method.', 405);
        }
    }

    public function enrol_list($course_id = "")
    {
        $course_details              = $this->crud_model->get_course_by_id($course_id)->row_array();
        $multi_instructors           = explode(',', $course_details['user_id']);
        $page_data['course_details'] = $course_details;

        $page_data['enrol_history'] = $this->crud_model->enrol_history($course_id);

        $this->load->view('backend/admin/course_enrol_list', $page_data);
    }

    public function export_student_progress_excel($course_id)
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $progress_data = $this->crud_model->get_course_pharmacist_academic_progress_data($course_id);
        if (!$progress_data) {
            $this->session->set_flashdata('error_message', get_phrase('course_not_found'));
            redirect(site_url('admin/courses'), 'refresh');
        }

        require_once APPPATH . 'libraries/SimpleXLSXGen.php';

        $excel_data = [];
        // Header with bold tags
        $header_row = [];
        foreach ($progress_data['headers'] as $header) {
            $header_row[] = '<b>' . $header . '</b>';
        }
        $excel_data[] = $header_row;

        // Data rows
        foreach ($progress_data['rows'] as $row) {
            $excel_data[] = [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['enrollment_date'],
                $row['last_seen'],
                $row['completed_date'],
                $row['course_progress'],
                $row['completed_lessons'],
                $row['watched_duration'],
                $row['quiz_result'],
                $row['score'],
                $row['percentage'],
                $row['pass_mark'],
                $row['attempts'],
                $row['quiz_details']
            ];
        }

        $course_title = slugify($progress_data['course']['title']);
        $filename = 'pharmacist_progress_' . $course_title . '_' . date('Y_m_d_His') . '.xlsx';

        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($excel_data, 'Pharmacist Progress');
        $xlsx->downloadAs($filename);
        exit;
    }

    public function export_student_progress_csv($course_id)
    {
        if ($this->session->userdata('admin_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        $progress_data = $this->crud_model->get_course_pharmacist_academic_progress_data($course_id);
        if (!$progress_data) {
            $this->session->set_flashdata('error_message', get_phrase('course_not_found'));
            redirect(site_url('admin/courses'), 'refresh');
        }

        $course_title = slugify($progress_data['course']['title']);
        $filename = 'pharmacist_progress_' . $course_title . '_' . date('Y_m_d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for proper Excel display of Unicode characters
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, $progress_data['headers']);

        foreach ($progress_data['rows'] as $row) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['enrollment_date'],
                $row['last_seen'],
                $row['completed_date'],
                $row['course_progress'],
                $row['completed_lessons'],
                $row['watched_duration'],
                $row['quiz_result'],
                $row['score'],
                $row['percentage'],
                $row['pass_mark'],
                $row['attempts'],
                $row['quiz_details']
            ]);
        }

        fclose($output);
        exit;
    }

    public function export_admins_csv()
    {
        // Check if the request method is POST
        if ($this->input->method() === 'post') {
            // Get admin IDs from the request
            $admin_ids = $this->input->post('admin_ids');

            // Validate admin IDs
            if (! is_array($admin_ids) || empty($admin_ids)) {
                show_error('No admin IDs provided.', 400);
            }

            // Fetch admin data for the provided IDs
            $this->db->where_in('id', $admin_ids);
            $query = $this->db->get('users');

            // Check if data exists
            if ($query->num_rows() === 0) {
                show_error('No data found for the provided admin IDs.', 404);
            }

            // Prepare the CSV header
            $csv_data = '"Id","Name","Email","Phone"' . "\n";

            // Populate the CSV rows
            foreach ($query->result_array() as $row) {
                // Add a CSV row
                $csv_data .= '"' . $row['id'] . '",';
                $csv_data .= '"' . $row['first_name'] . ' ' . $row['last_name'] . '",';
                $csv_data .= '"' . $row['email'] . '",';
                $csv_data .= '"' . $row['phone'] . '"' . "\n";
            }

            // Send the CSV data as a response
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="admins.csv"');
            echo $csv_data;
            exit;
        } else {
            show_error('Invalid request method.', 405);
        }
    }

    // Admin Create Review
    public function review_add()
    {
        $this->load->view('backend/admin/review_add');
    }
    public function review_edit($id = '')
    {
        $page_data['rating'] = $this->db->get_where('rating', ['id' => $id])->row_array();
        $this->load->view('backend/admin/review_edit', $page_data);
    }
    public function review($param1 = "", $param2 = "")
    {
        if ($param1 == 'delete' && ! empty($param2)) {
            $this->crud_model->review_delete($param2);
            $this->session->set_flashdata('flash_message', get_phrase('review_deleted_successfully'));
            redirect(site_url('admin/frontend_settings?tab=review'), 'refresh');
        }
    }

    // Badges
    public function badges($param1 = "", $param2 = "")
    {
        if ($param1 == 'course_count') {
            $this->crud_model->badges_store();
            $this->session->set_flashdata('flash_message', get_phrase('course count badges create successfully.'));
            redirect(site_url('admin/badges?tab=courseCount'), 'refresh');
        }
        if ($param1 == 'courses_rating') {
            $this->crud_model->badges_store();
            $this->session->set_flashdata('flash_message', get_phrase('course rating badges create successfully.'));
            redirect(site_url('admin/badges?tab=coursesRating'), 'refresh');
        }
        if ($param1 == 'courses_sale') {
            $this->crud_model->badges_store();
            $this->session->set_flashdata('flash_message', get_phrase('course sale badges create successfully.'));
            redirect(site_url('admin/badges?tab=courseSale'), 'refresh');
        }
        if ($param1 == 'articles') {
            $this->crud_model->badges_store();
            $this->session->set_flashdata('flash_message', get_phrase('course articles badges create successfully.'));
            redirect(site_url('admin/badges?tab=articles'), 'refresh');
        }
        if ($param1 == 'course_completed') {
            $this->crud_model->badges_store();
            $this->session->set_flashdata('flash_message', get_phrase('course completed badges create successfully.'));
            redirect(site_url('admin/badges?tab=courseCompleted'), 'refresh');
        }
        if ($param1 == 'certificate') {
            $this->crud_model->badges_store();
            $this->session->set_flashdata('flash_message', get_phrase('course certificate badges create successfully.'));
            redirect(site_url('admin/badges?tab=certificate'), 'refresh');
        }

        if ($param1 == 'update') {
            $this->crud_model->badges_update();
            $this->session->set_flashdata('flash_message', get_phrase('Badges  Update successfully'));
            redirect(site_url('admin/badges?tab=courseCount'), 'refresh');
        }

        if ($param1 == 'delete') {
            $this->crud_model->badges_delete($param2);
            $this->session->set_flashdata('flash_message', get_phrase('Badges delete successfully.'));
            redirect(site_url('admin/badges?tab=courseCount'), 'refresh');
        }

        $page_data['badgesList'] = $this->crud_model->badges_List()->result_array();
        $page_data['page_title'] = get_phrase('badges');
        $page_data['page_name']  = 'badges';
        $this->load->view('backend/index', $page_data);
    }

    public function badges_add()
    {
        $page_data['type'] = $this->input->get('type');
        $this->load->view('backend/admin/badges_add', $page_data);
    }

    public function badges_edit($id = '')
    {
        $page_data['badges'] = $this->db->get_where('badges', ['id' => $id])->row_array();
        $this->load->view('backend/admin/badges_edit', $page_data);
    }

    // Endbadges



// Custom Field Start

public function custom_field_add($param2)
{
    $custom_type = $this->input->post('custom_type');
    if (!$custom_type) return;

    $course_id    = $param2;
    $custom_title = $this->input->post($custom_type . '_custom_title');

    // ================= CHECK EXISTING ROW =================
    $existing = $this->db->get_where('custom_fields', [
        'course_id'    => $course_id,
        'custom_type'  => $custom_type,
        'custom_title' => $custom_title
    ])->row_array();

    $existing_items = [];
    $counter = 1;

    if ($existing) {
        $decoded = json_decode($existing['custom_field'], true);
        $existing_items = $decoded['data'] ?? [];

        if (!empty($existing_items)) {
            $ids = array_column($existing_items, 'id');
            $counter = max($ids) + 1; 
        }
    }

    $custom_items = [];

    // ================= IMAGE =================
    if ($custom_type == 'image') {

        if (!file_exists('uploads/custom_fields')) {
            mkdir('uploads/custom_fields', 0777, true);
        }

        foreach ($this->input->post('image_title') as $k => $title) {

            $file = '';
            if (!empty($_FILES['image_file']['name'][$k])) {
                $ext  = pathinfo($_FILES['image_file']['name'][$k], PATHINFO_EXTENSION);
                $file = time() . '_' . md5(uniqid()) . '.' . $ext;
                move_uploaded_file(
                    $_FILES['image_file']['tmp_name'][$k],
                    'uploads/custom_fields/' . $file
                );
            }

            $custom_items[] = [
                'id'          => $counter++,
                'title'       => $title,
                'description' => $_POST['image_description'][$k] ?? '',
                'file'        => $file
            ];
        }
    }

    // ================= TEXT =================
    if ($custom_type == 'text') {
        foreach ($this->input->post('text_content') as $content) {
            $custom_items[] = [
                'id'          => $counter++,
                'title'       => '',
                'description' => $content,
                'file'        => ''
            ];
        }
    }

    // ================= SLIDER =================
    if ($custom_type == 'slider') {

        if (!file_exists('uploads/custom_fields')) {
            mkdir('uploads/custom_fields', 0777, true);
        }

        foreach ($this->input->post('slider_title') as $k => $title) {

            $files = [];

            if (!empty($_FILES['slider_images']['name'][$k])) {
                foreach ($_FILES['slider_images']['name'][$k] as $i => $img) {
                    if (!empty($img)) {
                        $ext  = pathinfo($img, PATHINFO_EXTENSION);
                        $name = time() . '_' . md5(uniqid()) . '.' . $ext;

                        move_uploaded_file(
                            $_FILES['slider_images']['tmp_name'][$k][$i],
                            'uploads/custom_fields/' . $name
                        );

                        $files[] = $name;
                    }
                }
            }

            $custom_items[] = [
                'id'          => $counter++,
                'title'       => $title,
                'description' => $_POST['slider_description'][$k] ?? '',
                'file'        => $files
            ];
        }
    }

    // ================= VIDEO =================
    if ($custom_type == 'video') {
        foreach ($this->input->post('video_url') as $url) {
            $custom_items[] = [
                'id'    => $counter++,
                'title' => '',
                'file'  => $url
            ];
        }
    }

    // ================= FAQ =================
    if ($custom_type == 'faq') {
        foreach ($this->input->post('faq_question') as $k => $q) {
            $custom_items[] = [
                'id'          => $counter++,
                'title'       => $q,
                'description' => $_POST['faq_answer'][$k] ?? '',
                'file'        => ''
            ];
        }
    }

    // ================= GALLERY =================
    if ($custom_type == 'gallery') {

        if (!file_exists('uploads/custom_fields')) {
            mkdir('uploads/custom_fields', 0777, true);
        }

        foreach ($_FILES['gallery_images']['name'] as $k => $img) {

            $file = '';
            if (!empty($img)) {
                $ext  = pathinfo($img, PATHINFO_EXTENSION);
                $file = time() . '_' . md5(uniqid()) . '.' . $ext;
                move_uploaded_file(
                    $_FILES['gallery_images']['tmp_name'][$k],
                    'uploads/custom_fields/' . $file
                );
            }

            $custom_items[] = [
                'id'          => $counter++,
                'file'        => $file
            ];
        }
    }

    // ================= SAVE =================
    if ($existing) {

        $all_items = array_merge($existing_items, $custom_items);

        $this->db->where('id', $existing['id']);
        $this->db->update('custom_fields', [
            'custom_field' => json_encode(['data' => $all_items], JSON_UNESCAPED_UNICODE),
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

    } else {

        $this->db->insert('custom_fields', [
            'course_id'    => $course_id,
            'custom_type'  => $custom_type,
            'custom_title' => $custom_title,
            'custom_field' => json_encode(['data' => $custom_items], JSON_UNESCAPED_UNICODE),
            'sorting'      => 0,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        $insert_id = $this->db->insert_id();
        $this->db->where('id', $insert_id)->update('custom_fields', ['sorting' => $insert_id]);
    }

    $this->session->set_flashdata('flash_message', get_phrase('custom_field_added_successfully'));
    redirect(site_url('admin/course_form/course_edit/' . $course_id));
}





public function custom_field_section_update($field_id)
{
    // Fetch existing row
    $field = $this->db->get_where('custom_fields', ['id' => $field_id])->row_array();
    if (!$field) {
        $this->session->set_flashdata('error_message', get_phrase('custom_field_not_found'));
        redirect(site_url('admin/course_list'));
    }

    // Get new custom_title from form
    $custom_title = $this->input->post('custom_title');

    // Update only custom_title, keep custom_field JSON intact
    $this->db->where('id', $field_id);
    $this->db->update('custom_fields', [
        'custom_title' => $custom_title,
        'updated_at'   => date('Y-m-d H:i:s')
    ]);

    $this->session->set_flashdata('flash_message', get_phrase('custom_field_updated_successfully'));
    redirect(site_url('admin/course_form/course_edit/'.$field['course_id']));
}

public function custom_field_section_delete($id)
{
    $field = $this->db->get_where('custom_fields', ['id' => $id])->row_array();
    $this->db->where('id', $id);
    $this->db->delete('custom_fields');
    $this->session->set_flashdata('flash_message', get_phrase('custom_field_deleted_successfully'));
    redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
}

public function custom_field_item_update($field_id, $item_id)
{
    $field = $this->db->get_where('custom_fields', ['id' => $field_id])->row_array();
    if (!$field) {
        return;
    }

    $decoded = json_decode($field['custom_field'], true);
    $items   = $decoded['data'] ?? [];

    // loop 
    foreach ($items as &$item) {

        if ($item['id'] == $item_id) {

            // ===== TEXT DATA UPDATE =====
            if ($this->input->post('image_title')) {
                $item['title'] = $this->input->post('image_title')[0];
            }
            if ($this->input->post('image_description')) {
                $item['description'] = $this->input->post('image_description')[0];
            }
            if ($this->input->post('text_content')) {
                $item['description'] = $this->input->post('text_content')[0];
            }
            if ($this->input->post('video_url')) {
                $item['file'] = $this->input->post('video_url')[0];
            }
            if ($this->input->post('faq_question')) {
                $item['title'] = $this->input->post('faq_question')[0];
            }
            if ($this->input->post('faq_answer')) {
                $item['description'] = $this->input->post('faq_answer')[0];
            }

            // ===== IMAGE UPDATE (unlink old if new exists) =====
            if (!empty($_FILES['image_file']['name'][0])) {

                $old_file = $item['file'] ?? null;

                $file_name = time().'_'.$_FILES['image_file']['name'][0];
                $tmp_name  = $_FILES['image_file']['tmp_name'][0];

                move_uploaded_file(
                    $tmp_name,
                    'uploads/custom_fields/'.$file_name
                );

                // old image delete
                if ($old_file && file_exists('uploads/custom_fields/'.$old_file)) {
                    unlink('uploads/custom_fields/'.$old_file);
                }

                $item['file'] = $file_name;
            }

            break;
        }
    }

    // JSON update
    $decoded['data'] = $items;

    $this->db->where('id', $field_id)->update('custom_fields', [
        'custom_field' => json_encode($decoded),
        'updated_at'   => date('Y-m-d H:i:s')
    ]);

    $this->session->set_flashdata('flash_message', get_phrase('custom_field_item_updated_successfully'));
    redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
}



public function custom_field_item_delete($field_id = '', $item_id = '')
{
    $field = $this->db->get_where('custom_fields', ['id' => $field_id])->row_array();

    if (!$field) {
        $this->session->set_flashdata('error', 'Invalid field');
        redirect(site_url('admin/course_form'));
        return;
    }

    $custom_field = json_decode($field['custom_field'], true);

    if (!isset($custom_field['data']) || !is_array($custom_field['data'])) {
        $this->session->set_flashdata('success', 'Item deleted successfully');
        redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
        return;
    }

    $custom_field['data'] = array_values(array_filter(
        $custom_field['data'],
        function ($item) use ($item_id) {
            return isset($item['id']) && $item['id'] != $item_id;
        }
    ));

    $this->db->where('id', $field_id);
    $this->db->update('custom_fields', [
        'custom_field' => json_encode($custom_field)
    ]);

    $this->session->set_flashdata('success', 'Item deleted successfully');
    redirect(site_url('admin/course_form/course_edit/' . $field['course_id']));
}


public function custom_field_section_sort_update()
{
    $order = $this->input->post('order');

    if (is_array($order) && count($order) > 0) {
        foreach ($order as $position => $id) {
            $this->db->where('id', $id);
            $this->db->update('custom_fields', [
                'sorting' => $position + 1
            ]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}




// Custom Field End





    // Home page builder
    public function home_page_builder($type = "", $id = "")
    {
        if ($type == 'add') {

            $data['title']        = $this->input->post('title');
            $data['description']  = $this->input->post('description');
            $data['identifier']   = slugify($this->input->post('title'));
            $data['is_permanent'] = 0;
            $data['status']       = 0;

            if ($_FILES['thumbnail']['name'] != "") {
                if (! file_exists('uploads/home-pages')) {
                    mkdir('uploads/home-pages', 0777, true);
                }
                $data['thumbnail'] = 'uploads/home-pages/' . md5(rand(10000000, 20000000)) . '.jpg';
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $data['thumbnail']);
            }

            $this->db->insert('home_pages', $data);
            $this->session->set_flashdata('flash_message', get_phrase('home_page_added_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        } elseif ($type == 'update') {

            $data['title']       = $this->input->post('title');
            $data['description'] = $this->input->post('description');
            $data['identifier']  = slugify($this->input->post('title'));

            if ($_FILES['thumbnail']['name'] != "") {
                if (! file_exists('uploads/home-pages')) {
                    mkdir('uploads/home-pages', 0777, true);
                }
                $page_details = $this->db->get_where('home_pages', ['id' => $id])->row_array();
                if (file_exists($page_details['thumbnail']) && $page_details['thumbnail']) {
                    unlink($page_details['thumbnail']);
                }
                $data['thumbnail'] = 'uploads/home-pages/' . md5(rand(10000000, 20000000)) . '.jpg';
                move_uploaded_file($_FILES['thumbnail']['tmp_name'], $data['thumbnail']);
            }

            $this->db->where('id', $id);
            $this->db->update('home_pages', $data);
            $this->session->set_flashdata('flash_message', get_phrase('home_page_updated_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        } elseif ($type == 'delete') {

            $page_details = $this->db->get_where('home_pages', ['id' => $id])->row_array();
            if (file_exists($page_details['thumbnail']) && $page_details['thumbnail']) {
                unlink($page_details['thumbnail']);
            }

            $this->db->where('id', $id);
            $this->db->delete('home_pages');
            $this->session->set_flashdata('flash_message', get_phrase('home_page_deleted_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        } elseif ($type == 'status') {

            $this->db->where('status', 1);
            $this->db->update('home_pages', ['status' => 0]);

            $this->db->where('id', $id);
            $this->db->update('home_pages', ['status' => 1]);

            $this->session->set_flashdata('flash_message', get_phrase('home_page_updated_successfully'));
            redirect(site_url('admin/home_page_builder'), 'refresh');
        }

        $page_data['page_title'] = get_phrase('home_page_builder');
        $page_data['page_name']  = 'home_page_builder';
        $this->load->view('backend/index', $page_data);
    }

    public function home_page($type = '', $id = "")
    {
        $page_data['page'] = $this->db->get_where('home_pages', ['id' => $id])->row_array();
        $this->load->view('backend/admin/home_page_builder/index', $page_data);
    }

    public function preview_home_page($id = "")
    {
        $page_data['home'] = $this->db->get_where('home_pages', ['id' => $id])->row_array();
        $this->load->view('backend/admin/home_page_builder/preview', $page_data);
    }

    public function home_page_layout_update($id = "")
    {
        // $get_developer_elements = $this->developer_file_elements();
        $post_builder_elements = $_POST['builder_elements'];
        // print_r($post_builder_elements);
        // die;

        // dd($post_builder_elements);

        $built_file_names = [];

        foreach ($post_builder_elements as $file_name => $builder_elements) {
            // Skip invalid blocks
            // $arr_values = array_values($builder_elements);
            // if ($arr_values[0]['tag'] == 'null') continue;

            $built_file_names[] = $file_name;

            // Load original developer section
            $file_path      = APPPATH . 'views/components/main/' . $file_name . '.php';
            $developer_html = $this->encode_some_special_characters(file_get_contents($file_path));

            // Protect PHP opening tags (<?php, <?=)
            $developer_html_safe = preg_replace_callback(
                '/<\?(?:php|=)?[\s\S]*?\?>/i',
function ($matches) {
// encode the entire PHP snippet
return '__PHP_OPEN__' . base64_encode($matches[0]) . '__PHP_CLOSE__';
},
$developer_html
);

// ✅ Wrap in dummy container to prevent <html>

    $wrapped_html = '<div id="__temp_wrapper__">' . $developer_html_safe . '</div>';

    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    // $dom->loadHTML(mb_convert_encoding($wrapped_html, 'HTML-ENTITIES', 'UTF-8'));
    $dom->loadHTML('
    <?xml encoding="utf-8" ?>' . $wrapped_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new \DOMXPath($dom);
    $container = $dom->getElementById('__temp_wrapper__');

    foreach ($builder_elements as $builder_element) {
    $identity = $builder_element['identity'];

    $tag = $this->decodeContent($builder_element['tag']);
    $content = $this->decodeContent($builder_element['content']);
    $src = $this->decodeContent($builder_element['src']);
    $element = $this->decodeContent($builder_element['element']);
    $element_safe = preg_replace_callback(
    '/<\?(?:php|=)?[\s\S]*?\?>/i',
        fn($m) => '__PHP_OPEN__' . base64_encode($m[0]) . '__PHP_CLOSE__',
        $element
        );

        // ✅ Safe fallback support for new + old format
        $dropAreaIndex = $builder_element['dropAreaIndex'] ?? null;
        $droppedIndex = $builder_element['droppedIndex'] ?? null;

        // New nested (hierarchical) structure
        $dropAreaPath = $builder_element['dropAreaPath'] ?? [];
        if (! is_array($dropAreaPath)) {
        $decoded = json_decode($dropAreaPath, true);
        $dropAreaPath = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        // die;

        // Check if element already exists
        $existingNode = $xpath->query("//*[@builder-identity='$identity']")->item(0);
        // print_r($builder_element);
        // die;

        if ($existingNode) {

        // 🟢 Update existing element
        if ($tag === 'img') {
        // handle image src
        $builder_element_src = explode('/uploads/', $src);
        $builder_element_src2 = explode('/assets/', $src);
        if (array_key_exists(1, $builder_element_src)) {
        $updated_url_from_builder = '<?= base_url(); ?>' . 'uploads/' . $builder_element_src[1];
        } elseif (array_key_exists(1, $builder_element_src2)) {
        $updated_url_from_builder = '<?= base_url(); ?>' . 'assets/' . $builder_element_src2[1];
        } else {
        $updated_url_from_builder = '<?= base_url(); ?>' . $src;
        }
        $existingNode->setAttribute('src', $updated_url_from_builder);
        } else {

        // Replace content safely (keep Blade)
        while ($existingNode->firstChild) {
        $existingNode->removeChild($existingNode->firstChild);
        }

        $text = "<?= get_phrase('" . addslashes($content) . "') ?>";
        $textNode = $dom->createTextNode($text);
        $existingNode->appendChild($textNode);

        // while ($existingNode->firstChild) {
        // $existingNode->removeChild($existingNode->firstChild);
        // }

        // // Properly insert raw HTML or PHP snippets
        // $fragment = $dom->createDocumentFragment();
        // $fragment->appendXML($content);
        // $existingNode->appendChild($fragment);
        }
        } else {
        // Create new element
        if ($tag === 'img') {
        // handle image src
        $builder_element_src = explode('/public/', $src);
        if (array_key_exists(1, $builder_element_src)) {
        $updated_url_from_builder = '<?= base_url(); ?>' . $builder_element_src[1];
        } else {
        $updated_url_from_builder = '<?= base_url(); ?>' . $src;
        }
        $newElement = $dom->createElement($tag, $updated_url_from_builder);
        } else {
        // dd($tag);
        $newElement = $dom->createElement($tag, $content);
        }
        // Create new element ended

        // Extract all attributes from $element and add to $newElement
        $tempDom = new DOMDocument();
        libxml_use_internal_errors(true);
        // $tempDom->loadHTML(mb_convert_encoding($element, 'HTML-ENTITIES', 'UTF-8'));
        $tempDom->loadHTML('
        <?xml encoding="utf-8" ?>' . $element, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        $tempXpath = new DOMXPath($tempDom);
        $tempNode = $tempXpath->query('/*')->item(0) ?? $tempXpath->query('/html/body/*')->item(0);

        // print_r($tempNode);

        if ($tempNode !== null) {
        foreach ($tempNode->attributes ?? [] as $attr) {
        $newElement->setAttribute($attr->nodeName, $attr->nodeValue);
        }
        }
        // Extract all attributes from $element and add to $newElement ended

        // Insert the new element at the specific position
        if ($container) {
        $dropAreaNode = 0;
        $dropAreas = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' drop-area ')]", $container);

        if ($dropAreas->length > $dropAreaIndex) {
        $dropAreaNode = $dropAreas->item($dropAreaIndex);
        }
        if ($dropAreaNode) {
        // Insert at specific position
        $children = [];
        foreach ($dropAreaNode->childNodes as $child) {
        if ($child->nodeType === XML_ELEMENT_NODE) {
        $children[] = $child;
        }
        }
        $newElement = $dom->importNode($newElement, true);

        // if the index not exists the insert to the end
        if (! isset($children[$droppedIndex])) {
        $dropAreaNode->appendChild($newElement);
        } else {
        // print_r($newElement->attributes);
        $dropAreaNode->insertBefore($newElement, $children[$droppedIndex]);
        }
        }
        }

        // Insert the new element at the specific position ended
        }
        }

        // ✅ Extract only real HTML (skip <html>

            $container = $dom->getElementById('__temp_wrapper__');

            // ✅ Extract only real HTML (skip <html>

                $newHtml = '';
                foreach ($container->childNodes ?? [] as $child) {
                // Only nodes that belong to $dom
                $newHtml .= $dom->saveHTML($child);
                }

                // Restore PHP code
                $newHtml = preg_replace_callback('/__PHP_OPEN__(.*?)__PHP_CLOSE__/s', function ($matches) {
                return base64_decode($matches[1]);
                }, $newHtml);

                // ✅ Decode Blade-safe content (restore {{ }}, ->, etc.)
                $newHtml = html_entity_decode($newHtml, ENT_QUOTES | ENT_HTML5);
                $newHtml = $this->decode_some_special_characters(urldecode($newHtml));

                // ✅ Save cleaned section (no <html>

                    file_put_contents(
                    APPPATH . "views/components/builder/" . $id . '-' . $file_name . '.php',
                    $newHtml
                    );
                    }

                    // Update database with built section list
                    $this->db->where('id', $id);
                    $this->db->update('home_pages', ['html_file_names' => json_encode($built_file_names)]);

                    // $this->session->set_flashdata('flash_message', get_phrase('data_added_successfully'));
                    // redirect(site_url('admin/home_page/builder/' . $id), 'refresh');
                    echo 'Layout Updated';
                    }

                    public function encode_some_special_characters($content = "")
                    {
                    if ($content == "") {
                    return;
                    }

                    // $content = preg_replace('/&/', '&amp;', $content);
                    // $content = preg_replace('/"/', '&quot;', $content);
                    // $content = preg_replace('/</', '&lt;' , $content); // $content=preg_replace(' />/', '&gt;', $content);
                    // return $content;

                    $content = str_replace("&", "__apmsign_amp__", $content);
                    $content = str_replace('+', "__plussign_plus__", $content);

                    return $content;
                    }
                    public function decode_some_special_characters($content = "")
                    {
                    if ($content == "") {
                    return;
                    }

                    // $content = preg_replace('/&/', '&amp;', $content);
                    // $content = preg_replace('/"/', '&quot;', $content);
                    // $content = preg_replace('/</', '&lt;' , $content); // $content=preg_replace(' />/', '&gt;', $content);
                    // return $content;

                    $content = str_replace("__apmsign_amp__", "&", $content);
                    $content = str_replace('__plussign_plus__', "+", $content);

                    return $content;
                    }

                    public function decodeContent($content = "")
                    {
                    if (! $content || empty($content) || $content == 'null') {
                    return "null";
                    }

                    return urldecode(htmlspecialchars_decode(base64_decode($content)));
                    }

                    public function upload_page_builder_image()
                    {

                    if (! file_exists('uploads/home-pages')) {
                    mkdir('uploads/home-pages', 0777, true);
                    }

                    if ($_POST['remove_file']) {
                    $remove_file_arr = explode('/uploads/', $_POST['remove_file']);
                    if (isset($remove_file_arr[1]) && file_exists('uploads/' . $remove_file_arr[1])) {
                    unlink('uploads/' . $remove_file_arr[1]);
                    }
                    }

                    $image = 'uploads/home-pages/' . md5(rand(10000000, 20000000)) . '.png';
                    move_uploaded_file($_FILES['file']['tmp_name'], $image);
                    echo base_url($image);
                    }

                    public function developer_file_elements()
                    {
                    $developer_file_elements = [];

                    $componentPath = APPPATH . 'views/components/main';
                    $files = array_diff(scandir($componentPath), ['.', '..']);

                    foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                    continue;
                    }
                    // only .blade.php files

                    $fileName = str_replace('.blade', '', pathinfo($file, PATHINFO_FILENAME));
                    $filePath = $componentPath . '/' . $file;

                    $html = file_get_contents($filePath);

                    // Load HTML into DOM
                    $dom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    // $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                    $dom->loadHTML('
                    <?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                    libxml_clear_errors();

                    $xpath = new DOMXPath($dom);
                    $nodes = $xpath->query('//*[@builder-identity]');

                    $elements = [];

                    foreach ($nodes as $node) {
                    $identity = $node->getAttribute('builder-identity');
                    $tag = $node->nodeName;

                    // Get full element HTML
                    $elementHTML = $dom->saveHTML($node);

                    // Detect src (for images)
                    $src = $node->hasAttribute('src') ? $node->getAttribute('src') : null;

                    // Get inner content (without wrapping tag)
                    $content = '';
                    foreach ($node->childNodes as $child) {
                    $content .= $dom->saveHTML($child);
                    }

                    // 🔹 Get selector path
                    $selector = $this->getDomSelectorPath($node);

                    $elements[$identity] = [
                    'element' => $elementHTML,
                    'tag' => $tag,
                    'identity' => $identity,
                    'selector' => $selector,
                    'content' => $content ? $content : null,
                    'src' => $src ? $src : null,
                    ];
                    }

                    $developer_file_elements[$fileName] = $elements;
                    }

                    return $developer_file_elements;
                    }

                    public function getDomSelectorPath(DOMNode $node)
                    {
                    $path = [];

                    while ($node && $node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== 'html') {
                    $tag = strtolower($node->nodeName);

                    // Find position among siblings of same tag
                    $index = 1;
                    $sibling = $node->previousSibling;
                    while ($sibling) {
                    if ($sibling->nodeType === XML_ELEMENT_NODE && $sibling->nodeName === $node->nodeName) {
                    $index++;
                    }
                    $sibling = $sibling->previousSibling;
                    }

                    // Add nth-of-type if needed
                    $selector = $tag . ($index > 1 ? ":nth-of-type($index)" : '');
                    array_unshift($path, $selector);

                    $node = $node->parentNode;
                    }

                    return 'html > ' . implode(' > ', $path);
                    }
                    // Home page builder

    // ==========================================
    // STORE ROLES CRUD
    // ==========================================
    public function store_roles($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == "add") {
            $data['role_name']   = html_escape($this->input->post('role_name'));
            $data['description'] = html_escape($this->input->post('description'));
            $data['status']      = html_escape($this->input->post('status'));
            $data['created_at']  = time();
            $data['updated_at']  = time();
            $this->db->insert('store_roles', $data);
            $this->session->set_flashdata('flash_message', get_phrase('store_role_added_successfully'));
            redirect(site_url('admin/store_roles'), 'refresh');
        } elseif ($param1 == "edit") {
            $data['role_name']   = html_escape($this->input->post('role_name'));
            $data['description'] = html_escape($this->input->post('description'));
            $data['status']      = html_escape($this->input->post('status'));
            $data['updated_at']  = time();
            $this->db->where('id', $param2);
            $this->db->update('store_roles', $data);
            $this->session->set_flashdata('flash_message', get_phrase('store_role_updated_successfully'));
            redirect(site_url('admin/store_roles'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->db->where('id', $param2);
            $this->db->delete('store_roles');
            $this->session->set_flashdata('flash_message', get_phrase('store_role_deleted_successfully'));
            redirect(site_url('admin/store_roles'), 'refresh');
        }

        $page_data['roles']      = $this->db->order_by('id', 'desc')->get('store_roles')->result_array();
        $page_data['page_name']  = 'store_roles';
        $page_data['page_title'] = get_phrase('store_roles');
        $this->load->view('backend/index', $page_data);
    }

    public function store_role_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add_store_role_form') {
            $page_data['page_name']  = 'store_role_add';
            $page_data['page_title'] = get_phrase('add_new_role');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_store_role_form') {
            $page_data['page_name']  = 'store_role_edit';
            $page_data['role_data']  = $this->db->get_where('store_roles', array('id' => $param2))->row_array();
            $page_data['page_title'] = get_phrase('edit_role');
            $this->load->view('backend/index', $page_data);
        }
    }

    // ==========================================
    // STORE CATEGORIES CRUD
    // ==========================================
    public function store_categories($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == "add") {
            $data['category_name'] = html_escape($this->input->post('category_name'));
            $data['code']          = html_escape(strtoupper($this->input->post('code')));
            $data['description']   = html_escape($this->input->post('description'));
            $data['status']        = html_escape($this->input->post('status'));
            $data['created_at']    = time();
            $data['updated_at']    = time();
            $this->db->insert('store_categories', $data);
            $this->session->set_flashdata('flash_message', get_phrase('store_category_added_successfully'));
            redirect(site_url('admin/store_categories'), 'refresh');
        } elseif ($param1 == "edit") {
            $data['category_name'] = html_escape($this->input->post('category_name'));
            $data['code']          = html_escape(strtoupper($this->input->post('code')));
            $data['description']   = html_escape($this->input->post('description'));
            $data['status']        = html_escape($this->input->post('status'));
            $data['updated_at']    = time();
            $this->db->where('id', $param2);
            $this->db->update('store_categories', $data);
            $this->session->set_flashdata('flash_message', get_phrase('store_category_updated_successfully'));
            redirect(site_url('admin/store_categories'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->db->where('id', $param2);
            $this->db->delete('store_categories');
            $this->session->set_flashdata('flash_message', get_phrase('store_category_deleted_successfully'));
            redirect(site_url('admin/store_categories'), 'refresh');
        }

        $page_data['categories'] = $this->db->order_by('id', 'desc')->get('store_categories')->result_array();
        $page_data['page_name']  = 'store_categories';
        $page_data['page_title'] = get_phrase('store_categories');
        $this->load->view('backend/index', $page_data);
    }

    public function store_category_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add_category_form' || $param1 == 'add') {
            $page_data['page_name']  = 'store_category_add';
            $page_data['page_title'] = get_phrase('add_store_category');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_category_form' || $param1 == 'edit') {
            $page_data['page_name']     = 'store_category_edit';
            $page_data['category_data'] = $this->db->get_where('store_categories', array('id' => $param2))->row_array();
            $page_data['page_title']    = get_phrase('edit_store_category');
            $this->load->view('backend/index', $page_data);
        }
    }

    // ==========================================
    // STORES CRUD
    // ==========================================
    public function stores($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == "add") {
            $data['store_name']         = html_escape($this->input->post('store_name'));
            $data['store_code']         = html_escape($this->input->post('store_code'));
            $data['category_id']        = $this->input->post('category_id') ? (int)$this->input->post('category_id') : null;
            if (!empty($data['category_id'])) {
                $cat = $this->db->get_where('store_categories', ['id' => $data['category_id']])->row_array();
                $data['store_category'] = $cat ? $cat['category_name'] : '';
            } else {
                $data['store_category'] = html_escape($this->input->post('store_category'));
            }
            $data['zone']               = html_escape(strtoupper($this->input->post('zone')));
            $data['contact_person']     = html_escape($this->input->post('contact_person'));
            $data['live_date']          = html_escape($this->input->post('live_date'));
            $phone                      = html_escape($this->input->post('phone'));
            $mobile                     = html_escape($this->input->post('mobile'));
            $data['phone']              = !empty($phone) ? $phone : $mobile;
            $data['mobile']             = !empty($mobile) ? $mobile : $phone;
            $data['email']              = html_escape($this->input->post('email'));
            $data['portal_url']         = html_escape($this->input->post('portal_url'));
            $roles                      = $this->input->post('assigned_role_ids');
            $data['assigned_role_ids']  = is_array($roles) ? json_encode($roles) : json_encode([]);
            $data['address']            = html_escape($this->input->post('address'));
            $data['city']               = html_escape($this->input->post('city'));
            $data['state']              = html_escape($this->input->post('state'));
            $data['pin_code']           = html_escape($this->input->post('pin_code'));
            $data['status']             = html_escape($this->input->post('status'));
            $data['created_at']         = time();
            $data['updated_at']         = time();
            $this->db->insert('stores', $data);
            $this->session->set_flashdata('flash_message', get_phrase('store_added_successfully'));
            redirect(site_url('admin/stores'), 'refresh');
        } elseif ($param1 == "edit") {
            $data['store_name']         = html_escape($this->input->post('store_name'));
            $data['store_code']         = html_escape($this->input->post('store_code'));
            $data['category_id']        = $this->input->post('category_id') ? (int)$this->input->post('category_id') : null;
            if (!empty($data['category_id'])) {
                $cat = $this->db->get_where('store_categories', ['id' => $data['category_id']])->row_array();
                $data['store_category'] = $cat ? $cat['category_name'] : '';
            } else {
                $data['store_category'] = html_escape($this->input->post('store_category'));
            }
            $data['zone']               = html_escape(strtoupper($this->input->post('zone')));
            $data['contact_person']     = html_escape($this->input->post('contact_person'));
            $data['live_date']          = html_escape($this->input->post('live_date'));
            $phone                      = html_escape($this->input->post('phone'));
            $mobile                     = html_escape($this->input->post('mobile'));
            $data['phone']              = !empty($phone) ? $phone : $mobile;
            $data['mobile']             = !empty($mobile) ? $mobile : $phone;
            $data['email']              = html_escape($this->input->post('email'));
            $data['portal_url']         = html_escape($this->input->post('portal_url'));
            $roles                      = $this->input->post('assigned_role_ids');
            $data['assigned_role_ids']  = is_array($roles) ? json_encode($roles) : json_encode([]);
            $data['address']            = html_escape($this->input->post('address'));
            $data['city']               = html_escape($this->input->post('city'));
            $data['state']              = html_escape($this->input->post('state'));
            $data['pin_code']           = html_escape($this->input->post('pin_code'));
            $data['status']             = html_escape($this->input->post('status'));
            $data['updated_at']         = time();
            $this->db->where('id', $param2);
            $this->db->update('stores', $data);
            $this->session->set_flashdata('flash_message', get_phrase('store_updated_successfully'));
            redirect(site_url('admin/stores'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->db->where('id', $param2);
            $this->db->delete('stores');
            $this->session->set_flashdata('flash_message', get_phrase('store_deleted_successfully'));
            redirect(site_url('admin/stores'), 'refresh');
        }

        $page_data['stores']        = $this->db->order_by('id', 'desc')->get('stores')->result_array();
        $roles                      = $this->db->get('store_roles')->result_array();
        $roles_map                  = [];
        foreach ($roles as $r) {
            $roles_map[$r['id']] = $r['role_name'];
        }
        $page_data['roles_map']     = $roles_map;

        $categories                 = $this->db->get('store_categories')->result_array();
        $categories_map             = [];
        foreach ($categories as $c) {
            $categories_map[$c['id']] = $c['category_name'];
        }
        $page_data['categories_map'] = $categories_map;

        $page_data['page_name']      = 'stores';
        $page_data['page_title']     = get_phrase('stores');
        $this->load->view('backend/index', $page_data);
    }

    public function store_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add_store_form' || $param1 == 'add') {
            $page_data['page_name']        = 'store_add';
            $page_data['store_roles']      = $this->db->where('status', 1)->get('store_roles')->result_array();
            $page_data['store_categories'] = $this->db->where('status', 1)->get('store_categories')->result_array();
            $page_data['page_title']       = get_phrase('add_new_store');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_store_form' || $param1 == 'edit') {
            $page_data['page_name']        = 'store_edit';
            $page_data['store_data']       = $this->db->get_where('stores', array('id' => $param2))->row_array();
            $page_data['store_roles']      = $this->db->where('status', 1)->get('store_roles')->result_array();
            $page_data['store_categories'] = $this->db->where('status', 1)->get('store_categories')->result_array();
            $page_data['page_title']       = get_phrase('edit_store');
            $this->load->view('backend/index', $page_data);
        }
    }

    // ==========================================
    // STORE USERS CRUD
    // ==========================================
    public function store_users($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == "add") {
            $store_id = html_escape($this->input->post('store_id'));
            $role_id  = html_escape($this->input->post('role_id'));
            $username = trim($this->input->post('username'));
            $password = trim($this->input->post('password'));

            if (empty($store_id)) {
                $this->session->set_flashdata('error_message', get_phrase('store_is_mandatory'));
                redirect(site_url('admin/store_user_form/add_store_user_form'), 'refresh');
            }

            if (empty($role_id)) {
                $this->session->set_flashdata('error_message', get_phrase('role_is_mandatory_please_select_a_role'));
                redirect(site_url('admin/store_user_form/add_store_user_form'), 'refresh');
            }

            if (empty($username) || empty($password)) {
                $this->session->set_flashdata('error_message', get_phrase('username_and_password_are_required'));
                redirect(site_url('admin/store_user_form/add_store_user_form'), 'refresh');
            }

            // Ensure role_title is properly populated
            $role_row = $this->db->get_where('store_roles', array('id' => $role_id))->row_array();
            $role_title = $role_row ? $role_row['role_name'] : html_escape($this->input->post('role_title'));

            $data['store_id']      = $store_id;
            $data['pharmacist_id'] = $this->input->post('pharmacist_id') ? html_escape($this->input->post('pharmacist_id')) : null;
            $data['role_id']       = $role_id;
            $data['role_title']    = $role_title;
            $data['designation']   = html_escape($this->input->post('designation'));
            $data['username']      = html_escape($username);
            $data['password']      = html_escape($password);

            $portal_link           = trim($this->input->post('portal_link'));
            if (empty($portal_link) && !empty($data['store_id'])) {
                $store = $this->db->get_where('stores', array('id' => $data['store_id']))->row_array();
                if (!empty($store['portal_url'])) {
                    $portal_link = $store['portal_url'];
                }
            }
            $data['portal_link']   = html_escape($portal_link);

            $data['notes']         = html_escape($this->input->post('notes'));
            $data['status']        = html_escape($this->input->post('status'));
            $data['created_at']    = time();
            $data['updated_at']    = time();
            $this->db->insert('store_users', $data);
            if (!empty($data['pharmacist_id']) && !empty($data['password'])) {
                $this->db->where('id', $data['pharmacist_id'])->update('users', [
                    'password'       => sha1($data['password']),
                    'plain_password' => $data['password']
                ]);
            }
            $this->session->set_flashdata('flash_message', get_phrase('store_user_added_successfully'));
            redirect(site_url('admin/store_users'), 'refresh');
        } elseif ($param1 == "edit") {
            $store_id = html_escape($this->input->post('store_id'));
            $role_id  = html_escape($this->input->post('role_id'));
            $username = trim($this->input->post('username'));
            $password = trim($this->input->post('password'));

            if (empty($store_id)) {
                $this->session->set_flashdata('error_message', get_phrase('store_is_mandatory'));
                redirect(site_url('admin/store_user_form/edit_store_user_form/' . $param2), 'refresh');
            }

            if (empty($role_id)) {
                $this->session->set_flashdata('error_message', get_phrase('role_is_mandatory_please_select_a_role'));
                redirect(site_url('admin/store_user_form/edit_store_user_form/' . $param2), 'refresh');
            }

            if (empty($username) || empty($password)) {
                $this->session->set_flashdata('error_message', get_phrase('username_and_password_are_required'));
                redirect(site_url('admin/store_user_form/edit_store_user_form/' . $param2), 'refresh');
            }

            $role_row = $this->db->get_where('store_roles', array('id' => $role_id))->row_array();
            $role_title = $role_row ? $role_row['role_name'] : html_escape($this->input->post('role_title'));

            $data['store_id']      = $store_id;
            $data['pharmacist_id'] = $this->input->post('pharmacist_id') ? html_escape($this->input->post('pharmacist_id')) : null;
            $data['role_id']       = $role_id;
            $data['role_title']    = $role_title;
            $data['designation']   = html_escape($this->input->post('designation'));
            $data['username']      = html_escape($username);
            $data['password']      = html_escape($password);

            $portal_link           = trim($this->input->post('portal_link'));
            if (empty($portal_link) && !empty($data['store_id'])) {
                $store = $this->db->get_where('stores', array('id' => $data['store_id']))->row_array();
                if (!empty($store['portal_url'])) {
                    $portal_link = $store['portal_url'];
                }
            }
            $data['portal_link']   = html_escape($portal_link);

            $data['notes']         = html_escape($this->input->post('notes'));
            $data['status']        = html_escape($this->input->post('status'));
            $data['updated_at']    = time();
            $this->db->where('id', $param2);
            $this->db->update('store_users', $data);
            if (!empty($data['pharmacist_id']) && !empty($data['password'])) {
                $this->db->where('id', $data['pharmacist_id'])->update('users', [
                    'password'       => sha1($data['password']),
                    'plain_password' => $data['password']
                ]);
            }
            $this->session->set_flashdata('flash_message', get_phrase('store_user_updated_successfully'));
            redirect(site_url('admin/store_users'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->db->where('id', $param2);
            $this->db->delete('store_users');
            $this->session->set_flashdata('flash_message', get_phrase('store_user_deleted_successfully'));
            redirect(site_url('admin/store_users'), 'refresh');
        }

        $this->db->select('store_users.*, stores.store_name, stores.store_code, stores.portal_url as store_portal_url, users.first_name, users.last_name, users.email as pharmacist_email');
        $this->db->from('store_users');
        $this->db->join('stores', 'stores.id = store_users.store_id', 'left');
        $this->db->join('users', 'users.id = store_users.pharmacist_id', 'left');
        $this->db->order_by('store_users.id', 'desc');
        $store_users = $this->db->get()->result_array();
        foreach ($store_users as &$su) {
            if (empty($su['portal_link']) && !empty($su['store_portal_url'])) {
                $su['portal_link'] = $su['store_portal_url'];
            }
            if (!empty($su['first_name'])) {
                $su['pharmacist_name'] = $su['first_name'] . ' ' . $su['last_name'];
            }
        }
        $page_data['store_users'] = $store_users;
        $page_data['page_name']   = 'store_users';
        $page_data['page_title']  = get_phrase('store_users');
        $this->load->view('backend/index', $page_data);
    }

    public function store_user_form($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add_store_user_form') {
            $page_data['page_name']   = 'store_user_add';
            $page_data['stores']      = $this->db->where('status', 1)->get('stores')->result_array();
            $page_data['pharmacists'] = $this->db->where('role_id', 2)->where('status', 1)->get('users')->result_array();
            $page_data['page_title']  = get_phrase('add_new_store_user');
            $this->load->view('backend/index', $page_data);
        } elseif ($param1 == 'edit_store_user_form') {
            $page_data['page_name']   = 'store_user_edit';
            $store_user               = $this->db->get_where('store_users', array('id' => $param2))->row_array();
            $page_data['stores']      = $this->db->where('status', 1)->get('stores')->result_array();
            $page_data['pharmacists'] = $this->db->where('role_id', 2)->where('status', 1)->get('users')->result_array();

            // Load roles for this store
            $assigned_roles = [];
            if (!empty($store_user['store_id'])) {
                $store = $this->db->get_where('stores', array('id' => $store_user['store_id']))->row_array();
                if ($store) {
                    if (empty($store_user['portal_link']) && !empty($store['portal_url'])) {
                        $store_user['portal_link'] = $store['portal_url'];
                    }
                    if (!empty($store['assigned_role_ids'])) {
                        $role_ids = json_decode($store['assigned_role_ids'], true);
                        if (!empty($role_ids)) {
                            $this->db->where_in('id', $role_ids);
                            $this->db->where('status', 1);
                            $assigned_roles = $this->db->get('store_roles')->result_array();
                        }
                    }
                }
            }
            $page_data['store_user']  = $store_user;
            $page_data['store_roles'] = $assigned_roles;
            $page_data['page_title']  = get_phrase('edit_store_user');
            $this->load->view('backend/index', $page_data);
        }
    }

    // AJAX Endpoint for fetching store roles
    public function get_store_roles($store_id = 0)
    {
        if ($this->session->userdata('admin_login') != true) {
            echo json_encode([]);
            return;
        }
        $store = $this->db->get_where('stores', array('id' => $store_id))->row_array();
        if ($store && !empty($store['assigned_role_ids'])) {
            $role_ids = json_decode($store['assigned_role_ids'], true);
            if (!empty($role_ids)) {
                $this->db->where_in('id', $role_ids);
                $this->db->where('status', 1);
                $roles = $this->db->get('store_roles')->result_array();
                echo json_encode($roles);
                return;
            }
        }
        echo json_encode([]);
    }

    // ==========================================
    // BULK IMPORT & SAMPLE TEMPLATE DOWNLOADS
    // ==========================================
    public function download_sample_template($type = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $filename = "";
        $headers = [];
        $sample_rows = [];

        if ($type == 'pharmacists') {
            $filename = "pharmacists_sample_template.csv";
            $headers = ['First Name', 'Last Name', 'Email', 'Password', 'EMP ID', 'Gender', 'State', 'Pharmacy Name', 'Phone No.', 'Licence No.', 'Licence Start Date', 'Licence End Date', 'Address', 'Store Code', 'Designation', 'Region', 'Status'];
            $sample_rows = [
                ['shakuntala', 'pal', 'shakuntala.pal@davaindia.co.in', 'sIf2PmA3', 'DHL04112', 'Female', 'Chhattisgarh', 'GAR NIGAM COLONY', '9691564424', 'LIC-100234', '2024-01-15', '2027-01-15', 'Near Market, Raipur', 'CCGRAI10', 'M. Pharm', 'West', '1'],
                ['Janki', 'verma', 'janki.verma@davaindia.co.in', 'A7mWkNc8', 'DHL09281', 'Female', 'Chhattisgarh', 'Dava india tikrapara', '9131672749', '', '', '', '', 'CCGRAI11', 'B. Pharm', 'West', '1']
            ];
        } elseif ($type == 'stores') {
            $filename = "stores_sample_template.csv";
            $headers = ['Store Code', 'Email', 'State', 'Zone', 'Store Category', 'Mobile', 'Store Name', 'Store Contact Person', 'Store Live Date', 'Address', 'City', 'Pin Code', 'Portal URL', 'Status'];
            $sample_rows = [
                ['CASBAK1750', 'howlyroad1750@davaindia.co.in', 'ASSAM', 'EAST', 'DAVAINDIA COCO', '6358818453', 'PATHSALA', 'SHAHINUR ISLAM', '19-02-2025', 'LY ROAD, PO- PATHSALA, PS- PATHSALA, Dist. BAJALI', 'Barpeta', '781325', 'https://store1.domain.com/login', '1'],
                ['STR-002', 'healthplus.uptown@example.com', 'MA', 'NORTH', 'DAVAINDIA FOFO', '9876543211', 'HealthPlus Pharmacy', 'John Doe', '01-01-2025', '789 Uptown Blvd', 'Boston', '02108', 'https://store2.domain.com/login', '1']
            ];
        } elseif ($type == 'store_categories' || $type == 'categories') {
            $filename = "store_categories_sample_template.csv";
            $headers = ['category_name', 'code', 'description', 'status'];
            $sample_rows = [
                ['DAVAINDIA COCO', 'COCO', 'Company Owned Company Operated', '1'],
                ['DAVAINDIA FOFO', 'FOFO', 'Franchise Owned Franchise Operated', '1'],
                ['RETAIL PHARMACY', 'RETAIL', 'Retail pharmacy store', '1']
            ];
        } elseif ($type == 'roles') {
            $filename = "roles_sample_template.csv";
            $headers = ['role_name', 'description', 'status'];
            $sample_rows = [
                ['Senior Pharmacist', 'In charge of dispensing and inventory management', '1'],
                ['Cashier', 'Handles billing and customer checkout', '1'],
                ['Dispenser', 'Responsible for prescription fulfillment', '1']
            ];
        } elseif ($type == 'store_users') {
            $filename = "store_users_sample_template.csv";
            $headers = ['store_code', 'role_name', 'designation', 'pharmacist_email', 'username', 'password', 'portal_link', 'notes', 'status'];
            $sample_rows = [
                ['STR-001', 'Senior Pharmacist', 'M. Pharm', 'john.doe@example.com', 'john.apollo', 'Pass123!', '', 'Assigned to Apollo downtown', '1'],
                ['STR-002', 'Cashier', 'B. Pharm', 'jane.smith@example.com', 'jane.healthplus', '', '', 'Assigned to HealthPlus', '1']
            ];
        } elseif ($type == 'instructors' || $type == 'instructor') {
            $filename = "instructors_sample_template.csv";
            $headers = ['first_name', 'last_name', 'email', 'password', 'phone', 'address', 'biography', 'status'];
            $sample_rows = [
                ['Robert', 'Fox', 'robert.fox@example.com', 'Pass@1234', '9876543210', '123 Academic Way, Boston', 'Specialist in Pharmacology with 10+ years experience', '1'],
                ['Sarah', 'Connor', 'sarah.connor@example.com', '', '9876543211', '456 College Blvd, New York', 'Clinical Pharmacy educator and researcher', '1']
            ];
        } elseif ($type == 'enrollments' || $type == 'enrollment' || $type == 'course_enrollment') {
            $filename = "course_enrollment_sample_template.csv";
            $headers = ['user_email', 'course_title', 'expiry_days'];
            $sample_rows = [
                ['bhargav@example.com', 'First Course', '180'],
                ['student1@example.com', '1', '']
            ];
        } else {
            show_404();
            return;
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Write UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        foreach ($sample_rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    private function parse_imported_file($file_path, $file_name)
    {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $rows = [];

        if ($ext === 'xlsx') {
            $zip = new ZipArchive();
            if ($zip->open($file_path) === true) {
                $sharedStrings = [];
                $ssXml = $zip->getFromName("xl/sharedStrings.xml");
                if ($ssXml) {
                    $xml = simplexml_load_string($ssXml);
                    if ($xml && isset($xml->si)) {
                        foreach ($xml->si as $si) {
                            $t = "";
                            if (isset($si->t)) {
                                $t = (string)$si->t;
                            } elseif (isset($si->r)) {
                                foreach ($si->r as $r) {
                                    $t .= (string)$r->t;
                                }
                            }
                            $sharedStrings[] = $t;
                        }
                    }
                }

                $sheetXml = $zip->getFromName("xl/worksheets/sheet1.xml");
                if (!$sheetXml) {
                    $sheetXml = $zip->getFromName("xl/worksheets/Sheet1.xml");
                }
                if (!$sheetXml) {
                    for ($zi = 0; $zi < $zip->numFiles; $zi++) {
                        $stat = $zip->statIndex($zi);
                        if (preg_match('#^xl/worksheets/sheet[0-9]+\.xml$#i', $stat['name'])) {
                            $sheetXml = $zip->getFromIndex($zi);
                            break;
                        }
                    }
                }
                if ($sheetXml) {
                    $xml = simplexml_load_string($sheetXml);
                    if ($xml && isset($xml->sheetData->row)) {
                        foreach ($xml->sheetData->row as $row) {
                            $rowData = [];
                            $maxCol = 0;
                            $seqIdx = 0;
                            foreach ($row->c as $c) {
                                $cellRef = isset($c['r']) ? (string)$c['r'] : '';
                                if (!empty($cellRef)) {
                                    $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                                    $colIdx = 0;
                                    for ($ci = 0; $ci < strlen($colLetters); $ci++) {
                                        $colIdx = $colIdx * 26 + (ord($colLetters[$ci]) - 64);
                                    }
                                    $colIdx = $colIdx - 1;
                                } else {
                                    $colIdx = $seqIdx;
                                }
                                $seqIdx = $colIdx + 1;

                                $val = "";
                                $type = (string)$c["t"];
                                if ($type === "s") {
                                    $idx = (int)$c->v;
                                    $val = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : "";
                                } elseif ($type === "inlineStr") {
                                    $val = "";
                                    if (isset($c->is->t)) {
                                        $val = (string)$c->is->t;
                                    } elseif (isset($c->is->r)) {
                                        foreach ($c->is->r as $r) {
                                            $val .= (string)$r->t;
                                        }
                                    }
                                } else {
                                    $val = isset($c->v) ? (string)$c->v : "";
                                }
                                $rowData[$colIdx] = trim($val);
                                if ($colIdx > $maxCol) {
                                    $maxCol = $colIdx;
                                }
                            }
                            $completeRow = [];
                            for ($col = 0; $col <= $maxCol; $col++) {
                                $completeRow[$col] = isset($rowData[$col]) ? $rowData[$col] : "";
                            }
                            if (!empty(array_filter($completeRow, 'strlen'))) {
                                $rows[] = $completeRow;
                            }
                        }
                    }
                }
                $zip->close();
            }
        } else {
            // CSV / TXT
            if (($handle = fopen($file_path, "r")) !== false) {
                // Check and strip UTF-8 BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Detect delimiter
                $firstLine = fgets($handle);
                rewind($handle);
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                $delimiter = ",";
                if (substr_count($firstLine, ";") > substr_count($firstLine, ",")) {
                    $delimiter = ";";
                } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) {
                    $delimiter = "\t";
                }

                while (($data = fgetcsv($handle, 5000, $delimiter)) !== false) {
                    $rowData = array_map('trim', $data);
                    if (!empty(array_filter($rowData, 'strlen'))) {
                        $rows[] = $rowData;
                    }
                }
                fclose($handle);
            }
        }

        // Skip leading rows that have fewer than 2 non-empty values (e.g. single-cell title/banner rows)
        while (!empty($rows) && count(array_filter($rows[0], 'strlen')) < 2) {
            array_shift($rows);
        }

        if (empty($rows) || count($rows) < 2) {
            return [];
        }

        // Normalize headers
        $rawHeaders = array_shift($rows);
        $headers = [];
        foreach ($rawHeaders as $h) {
            $clean = strtolower(trim((string)$h));
            $clean = preg_replace('/[^a-z0-9_]/', '_', $clean);
            $clean = preg_replace('/_+/', '_', $clean);
            $clean = trim($clean, '_');
            $headers[] = $clean;
        }

        $assocRows = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($headers as $idx => $header) {
                $item[$header] = isset($row[$idx]) ? trim((string)$row[$idx]) : "";
            }
            $assocRows[] = $item;
        }

        return $assocRows;
    }

    private function import_pharmacists($rows)
    {
        $this->user_model->ensure_pharmacist_columns();
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            // First name & Last name (Last Name is optional)
            $first_name = !empty($row['first_name']) ? trim($row['first_name']) : (!empty($row['firstname']) ? trim($row['firstname']) : '');
            $last_name  = !empty($row['last_name']) ? trim($row['last_name']) : (!empty($row['lastname']) ? trim($row['lastname']) : '');
            $raw_email  = isset($row['email']) ? trim($row['email']) : '';

            // Clean & sanitize email (Email is optional)
            $email = '';
            if (!empty($raw_email) && $raw_email !== '-' && $raw_email !== '0') {
                $clean_email = preg_replace('/[^\x20-\x7E]/', '', $raw_email);
                if (strpos($clean_email, '/') !== false) {
                    $parts = explode('/', $clean_email);
                    $clean_email = trim($parts[0]);
                }
                if (strpos($clean_email, ',') !== false) {
                    $parts = explode(',', $clean_email);
                    $clean_email = trim($parts[0]);
                }
                $clean_email = rtrim($clean_email, ',');
                $clean_email = preg_replace('/\s+com$/i', '.com', $clean_email);
                $clean_email = preg_replace('/@gmal\./i', '@gmail.', $clean_email);
                $clean_email = trim($clean_email);
                if (filter_var($clean_email, FILTER_VALIDATE_EMAIL)) {
                    $email = $clean_email;
                } else {
                    $email = (strpos($clean_email, '@') !== false) ? $clean_email : '';
                }
            }

            // Fallback for first name if empty
            if (empty($first_name)) {
                $first_name = 'Pharmacist';
            }

            $raw_password = isset($row['password']) ? trim($row['password']) : '';
            $password     = !empty($raw_password) ? $raw_password : '12345678';

            // Employee ID (supports EMP ID, employee_id, empid)
            $employee_id = '';
            if (!empty($row['emp_id'])) {
                $employee_id = trim($row['emp_id']);
            } elseif (!empty($row['employee_id'])) {
                $employee_id = trim($row['employee_id']);
            } elseif (!empty($row['empid'])) {
                $employee_id = trim($row['empid']);
            }

            // Gender
            $gender = isset($row['gender']) ? trim($row['gender']) : '';

            // State
            $state = isset($row['state']) ? trim($row['state']) : '';

            // Pharmacy Name
            $pharmacy_name = '';
            if (!empty($row['pharmacy_name'])) {
                $pharmacy_name = trim($row['pharmacy_name']);
            } elseif (!empty($row['pharmacy'])) {
                $pharmacy_name = trim($row['pharmacy']);
            }

            // Phone No. (supports Phone No., phone, mobile, contact)
            $phone = '';
            if (!empty($row['phone_no'])) {
                $phone = trim($row['phone_no']);
            } elseif (!empty($row['phone'])) {
                $phone = trim($row['phone']);
            } elseif (!empty($row['mobile'])) {
                $phone = trim($row['mobile']);
            } elseif (!empty($row['contact'])) {
                $phone = trim($row['contact']);
            }

            // Licence No.
            $licence_no = '';
            if (!empty($row['licence_no'])) {
                $licence_no = trim($row['licence_no']);
            } elseif (!empty($row['license_no'])) {
                $licence_no = trim($row['license_no']);
            } elseif (!empty($row['licence'])) {
                $licence_no = trim($row['licence']);
            }

            // Date normalizer helper
            $normalize_date = function($d_val) {
                $d_val = trim((string)$d_val);
                if (empty($d_val)) return '';
                if (is_numeric($d_val) && (int)$d_val > 30000 && (int)$d_val < 60000) {
                    $unix_time = ($d_val - 25569) * 86400;
                    return date('Y-m-d', $unix_time);
                }
                foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'm/d/Y', 'd.m.Y'] as $fmt) {
                    $d = DateTime::createFromFormat($fmt, $d_val);
                    if ($d && $d->format($fmt) === $d_val) {
                        return $d->format('Y-m-d');
                    }
                }
                return $d_val;
            };

            // Licence Start Date & End Date
            $licence_start_date = '';
            if (!empty($row['licence_start_date'])) {
                $licence_start_date = $normalize_date($row['licence_start_date']);
            } elseif (!empty($row['license_start_date'])) {
                $licence_start_date = $normalize_date($row['license_start_date']);
            }

            $licence_end_date = '';
            if (!empty($row['licence_end_date'])) {
                $licence_end_date = $normalize_date($row['licence_end_date']);
            } elseif (!empty($row['license_end_date'])) {
                $licence_end_date = $normalize_date($row['license_end_date']);
            }

            // Address
            $address = isset($row['address']) ? trim($row['address']) : '';

            // Store Code
            $store_code = '';
            if (!empty($row['store_code'])) {
                $store_code = trim($row['store_code']);
            } elseif (!empty($row['store'])) {
                $store_code = trim($row['store']);
            }

            // Designation
            $designation = '';
            if (!empty($row['designation'])) {
                $designation = trim($row['designation']);
            } elseif (!empty($row['title'])) {
                $designation = trim($row['title']);
            }

            // Region
            $region = '';
            if (!empty($row['region'])) {
                $region = trim($row['region']);
            } elseif (!empty($row['zone'])) {
                $region = trim($row['zone']);
            }

            // Status
            $status = isset($row['status']) && $row['status'] !== '' ? (int)$row['status'] : 1;

            // Resolve or link store
            $store_id = null;
            if (!empty($store_code)) {
                $store = $this->db->group_start()
                    ->where('store_code', $store_code)
                    ->or_where('store_name', $store_code)
                    ->group_end()
                    ->get('stores')->row_array();
                if ($store) {
                    $store_id = $store['id'];
                    if (empty($pharmacy_name) && !empty($store['store_name'])) {
                        $pharmacy_name = $store['store_name'];
                    }
                    if (empty($state) && !empty($store['state'])) {
                        $state = $store['state'];
                    }
                    if (empty($region) && !empty($store['zone'])) {
                        $region = $store['zone'];
                    }
                }
            }

            if (!$store_id && !empty($pharmacy_name)) {
                $store = $this->db->get_where('stores', ['store_name' => $pharmacy_name])->row_array();
                if ($store) {
                    $store_id = $store['id'];
                    if (empty($store_code) && !empty($store['store_code'])) {
                        $store_code = $store['store_code'];
                    }
                }
            }

            // If store doesn't exist yet, auto-create it so pharmacist is linked to a valid store
            if (!$store_id && (!empty($store_code) || !empty($pharmacy_name))) {
                $new_store_name = !empty($pharmacy_name) ? $pharmacy_name : $store_code;
                $new_store = [
                    'store_name'        => html_escape($new_store_name),
                    'store_code'        => html_escape(!empty($store_code) ? $store_code : strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $new_store_name), 0, 10))),
                    'state'             => html_escape($state),
                    'zone'              => html_escape(strtoupper($region)),
                    'status'            => 1,
                    'created_at'        => time(),
                    'updated_at'        => time(),
                    'assigned_role_ids' => json_encode([])
                ];
                $this->db->insert('stores', $new_store);
                $store_id = $this->db->insert_id();
            }

            // Check if user already exists (only match by non-empty valid email, or by distinct employee_id)
            $existing_user = null;
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $existing_user = $this->db->get_where('users', ['email' => $email, 'role_id' => 2])->row_array();
            } elseif (!empty($employee_id) && strtoupper($employee_id) !== 'DHL' && strtoupper($employee_id) !== 'PENDING') {
                $existing_user = $this->db->get_where('users', ['employee_id' => $employee_id, 'role_id' => 2])->row_array();
            }
            $pharmacist_id = null;
            if ($existing_user) {
                // Update existing pharmacist with updated details and new columns
                $update_data = [
                    'first_name'         => html_escape($first_name),
                    'last_name'          => html_escape($last_name),
                    'employee_id'        => html_escape($employee_id),
                    'gender'             => html_escape($gender),
                    'state'              => html_escape($state),
                    'pharmacy_name'      => html_escape($pharmacy_name),
                    'phone'              => html_escape($phone),
                    'licence_no'         => html_escape($licence_no),
                    'licence_start_date' => html_escape($licence_start_date),
                    'licence_end_date'   => html_escape($licence_end_date),
                    'address'            => html_escape($address),
                    'designation'        => html_escape($designation),
                    'region'             => html_escape($region),
                    'status'             => $status,
                    'last_modified'      => time()
                ];
                if (!empty($designation)) {
                    $update_data['title'] = html_escape($designation);
                }
                if ($store_id) {
                    $update_data['store_id'] = $store_id;
                }
                if (!empty($raw_password)) {
                    $update_data['password'] = sha1($raw_password);
                    $update_data['plain_password'] = $raw_password;
                }
                $this->db->where('id', $existing_user['id'])->update('users', $update_data);
                $pharmacist_id = $existing_user['id'];
                $success++;
            } else {
                // Insert new pharmacist
                $user_data = [
                    'first_name'         => html_escape($first_name),
                    'last_name'          => html_escape($last_name),
                    'email'              => html_escape($email),
                    'password'           => sha1($password),
                    'plain_password'     => $password,
                    'employee_id'        => html_escape($employee_id),
                    'gender'             => html_escape($gender),
                    'state'              => html_escape($state),
                    'pharmacy_name'      => html_escape($pharmacy_name),
                    'phone'              => html_escape($phone),
                    'licence_no'         => html_escape($licence_no),
                    'licence_start_date' => html_escape($licence_start_date),
                    'licence_end_date'   => html_escape($licence_end_date),
                    'address'            => html_escape($address),
                    'designation'        => html_escape($designation),
                    'title'              => html_escape($designation),
                    'region'             => html_escape($region),
                    'store_id'           => $store_id,
                    'role_id'            => 2,
                    'status'             => $status,
                    'date_added'         => time(),
                    'social_links'       => json_encode(['facebook' => '', 'twitter' => '', 'linkedin' => '']),
                    'wishlist'           => json_encode([]),
                    'image'              => md5(rand(10000, 10000000))
                ];

                $this->db->insert('users', $user_data);
                $pharmacist_id = $this->db->insert_id();
                $success++;
            }

            // Assign roles to stores whose store_code matches pharmacist store_code,
            // and add / update designation in store_users
            $clean_designation = trim((string)$designation);
            $role_id = null;
            $role_title = !empty($clean_designation) ? $clean_designation : 'Pharmacist';

            if (!empty($clean_designation)) {
                // Check if role exists in store_roles (case-insensitive)
                $role = $this->db->where('LOWER(TRIM(role_name))', strtolower($clean_designation))->get('store_roles')->row_array();
                if (!$role) {
                    $new_role = [
                        'role_name'   => html_escape($clean_designation),
                        'description' => 'Auto-created role for designation ' . $clean_designation,
                        'status'      => 1,
                        'created_at'  => time(),
                        'updated_at'  => time()
                    ];
                    $this->db->insert('store_roles', $new_role);
                    $role_id = $this->db->insert_id();
                    $role_title = $clean_designation;
                } else {
                    $role_id = $role['id'];
                    $role_title = $role['role_name'];
                }
            }

            // Assign this role to all stores whose store_code matches pharmacist store_code
            if (!empty($role_id)) {
                $target_stores = [];
                if (!empty($store_code)) {
                    $found = $this->db->group_start()
                        ->where('LOWER(TRIM(store_code))', strtolower(trim($store_code)))
                        ->or_where('LOWER(TRIM(store_name))', strtolower(trim($store_code)))
                        ->group_end()
                        ->get('stores')->result_array();
                    foreach ($found as $f) {
                        $target_stores[$f['id']] = $f;
                    }
                }
                if ($store_id && !isset($target_stores[$store_id])) {
                    $f = $this->db->get_where('stores', ['id' => $store_id])->row_array();
                    if ($f) {
                        $target_stores[$f['id']] = $f;
                    }
                }

                foreach ($target_stores as $ts) {
                    $assigned_roles = json_decode($ts['assigned_role_ids'] ?: '[]', true);
                    if (!is_array($assigned_roles)) {
                        $assigned_roles = [];
                    }
                    if (!in_array((string)$role_id, $assigned_roles)) {
                        $assigned_roles[] = (string)$role_id;
                        $this->db->where('id', $ts['id'])->update('stores', [
                            'assigned_role_ids' => json_encode(array_values(array_unique($assigned_roles))),
                            'updated_at'        => time()
                        ]);
                    }
                }
            }

            // Add designation in store_users (create or update record for this pharmacist)
            if ($store_id && $pharmacist_id && $this->db->table_exists('store_users')) {
                $existing_su = $this->db->get_where('store_users', ['pharmacist_id' => $pharmacist_id])->row_array();
                if ($existing_su) {
                    $su_update = [
                        'store_id'    => $store_id,
                        'designation' => html_escape($clean_designation),
                        'updated_at'  => time()
                    ];
                    if (!empty($role_id)) {
                        $su_update['role_id']    = $role_id;
                        $su_update['role_title'] = html_escape($role_title);
                    }
                    if (!empty($raw_password)) {
                        $su_update['password'] = html_escape($raw_password);
                    }
                    $this->db->where('id', $existing_su['id'])->update('store_users', $su_update);
                } else {
                    // Generate unique username
                    $base_user = '';
                    if (!empty($email) && strpos($email, '@') !== false) {
                        $base_user = explode('@', $email)[0];
                    } elseif (!empty($employee_id)) {
                        $base_user = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $employee_id));
                    } else {
                        $base_user = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $first_name . '.' . $last_name));
                    }
                    if (empty($base_user)) {
                        $base_user = 'pharmacist_' . $pharmacist_id;
                    }

                    $candidate_user = $base_user;
                    $uidx = 1;
                    while ($this->db->get_where('store_users', ['store_id' => $store_id, 'username' => $candidate_user])->num_rows() > 0) {
                        $candidate_user = $base_user . '_' . $uidx;
                        $uidx++;
                    }

                    $store_rec = $this->db->get_where('stores', ['id' => $store_id])->row_array();
                    $portal_link = $store_rec['portal_url'] ?? '';

                    $su_insert = [
                        'store_id'      => $store_id,
                        'pharmacist_id' => $pharmacist_id,
                        'role_id'       => $role_id ?: null,
                        'role_title'    => html_escape($role_title),
                        'designation'   => html_escape($clean_designation),
                        'username'      => html_escape($candidate_user),
                        'password'      => html_escape(!empty($raw_password) ? $raw_password : 'password123'),
                        'portal_link'   => html_escape($portal_link),
                        'notes'         => 'Auto-created during pharmacist import',
                        'status'        => $status,
                        'created_at'    => time(),
                        'updated_at'    => time()
                    ];
                    $this->db->insert('store_users', $su_insert);
                }
            }
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function import_stores($rows)
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        // Pre-cache existing store roles
        $roles_db = $this->db->get('store_roles')->result_array();
        $roles_map = [];
        foreach ($roles_db as $r) {
            $roles_map[strtolower(trim($r['role_name']))] = (string)$r['id'];
        }

        // Pre-cache existing store categories
        $cats_db = $this->db->get('store_categories')->result_array();
        $cats_map = [];
        foreach ($cats_db as $c) {
            $c_clean_name = strtolower(trim(preg_replace('/\s+/', ' ', $c['category_name'])));
            $cats_map[$c_clean_name] = $c;
            if (!empty($c['code'])) {
                $c_clean_code = strtolower(trim($c['code']));
                $cats_map[$c_clean_code] = $c;
            }
        }

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            // Helper to find value from row using multiple key aliases and fuzzy keyword fallback
            $find_val = function($aliases, $contains = []) use ($row) {
                // 1. Direct match on normalized keys
                foreach ($aliases as $alias) {
                    if (isset($row[$alias]) && trim((string)$row[$alias]) !== '') {
                        return trim(preg_replace('/[\x00-\x1F\x7F\xA0\s]+/u', ' ', (string)$row[$alias]));
                    }
                }
                // 2. Alphanumeric normalized key match
                $alpha_row = [];
                foreach ($row as $k => $v) {
                    $ak = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$k));
                    $alpha_row[$ak] = $v;
                }
                foreach ($aliases as $alias) {
                    $ak = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$alias));
                    if (isset($alpha_row[$ak]) && trim((string)$alpha_row[$ak]) !== '') {
                        return trim(preg_replace('/[\x00-\x1F\x7F\xA0\s]+/u', ' ', (string)$alpha_row[$ak]));
                    }
                }
                // 3. Fallback: contains keyword
                if (!empty($contains)) {
                    foreach ($row as $k => $v) {
                        $lk = strtolower(trim((string)$k));
                        foreach ($contains as $kw) {
                            if (strpos($lk, $kw) !== false && trim((string)$v) !== '') {
                                return trim(preg_replace('/[\x00-\x1F\x7F\xA0\s]+/u', ' ', (string)$v));
                            }
                        }
                    }
                }
                return '';
            };

            // Store Name
            $store_name = $find_val(
                ['store_name', 'stores_name', 'name', 'pharmacy_name', 'pharmacy', 'store', 'shop_name', 'storename', 'storesname'],
                ['store_name', 'pharmacy_name', 'shop_name']
            );

            // Store Code
            $store_code = $find_val(
                ['store_code', 'stores_code', 'code', 'store_id', 'storeid', 'store_no', 'store_number', 'storecode', 'storescode'],
                ['store_code', 'stores_code']
            );

            // Fallback for store name if store_code exists
            if (empty($store_name) && !empty($store_code)) {
                $store_name = $store_code;
            }

            if (empty($store_name) && empty($store_code)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing store name and store code";
                continue;
            }

            $email = $find_val(['email', 'store_email', 'stores_email', 'email_id', 'mail']);
            $state = $find_val(['state', 'store_state', 'stores_state', 'province']);
            $zone  = strtoupper($find_val(['zone', 'region', 'store_zone', 'store_region', 'stores_zone', 'stores_region']));

            // Mobile & Phone
            $mobile = $find_val(['mobile', 'mobile_no', 'mobile_number', 'cell', 'store_mobile', 'stores_mobile']);
            $phone  = $find_val(['phone', 'phone_no', 'phone_number', 'telephone', 'store_phone', 'stores_phone', 'contact_no', 'contact_number']);
            if (empty($mobile) && !empty($phone)) $mobile = $phone;
            if (empty($phone) && !empty($mobile)) $phone = $mobile;
            if (empty($phone) && empty($mobile) && !empty($row['contact']) && preg_match('/^[0-9+\s\-()]{7,}$/', trim((string)$row['contact']))) {
                $phone = trim((string)$row['contact']);
                $mobile = $phone;
            }

            // Contact Person (handles 'store contact person', 'stores contact person', etc.)
            $contact_person = $find_val(
                ['store_contact_person', 'stores_contact_person', 'contact_person', 'store_contact_person_name', 'stores_contact_person_name', 'contact_person_name', 'contact_name', 'store_contact_name', 'stores_contact_name', 'store_contact', 'stores_contact', 'contactperson', 'storescontactperson', 'storecontactperson', 'person_name', 'store_person', 'stores_person', 'person', 'store_manager', 'stores_manager', 'manager_name', 'manager', 'owner_name', 'owner'],
                ['contact_person', 'contact_name', 'store_contact', 'stores_contact', 'manager', 'person']
            );
            if (empty($contact_person) && !empty($row['contact']) && !preg_match('/^[0-9+\s\-()]{7,}$/', trim((string)$row['contact']))) {
                $contact_person = trim((string)$row['contact']);
            }

            // Store Live Date
            $live_date = $find_val(
                ['store_live_date', 'stores_live_date', 'live_date', 'date_of_live', 'opening_date', 'start_date', 'launch_date', 'date', 'storelivedate', 'livedate'],
                ['live_date', 'livedate']
            );
            if (!empty($live_date)) {
                if (is_numeric($live_date) && (int)$live_date > 30000 && (int)$live_date < 60000) {
                    $unix_time = ($live_date - 25569) * 86400;
                    $live_date = date('Y-m-d', $unix_time);
                } else {
                    foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'm/d/Y', 'd.m.Y'] as $fmt) {
                        $d = DateTime::createFromFormat($fmt, $live_date);
                        if ($d && $d->format($fmt) === $live_date) {
                            $live_date = $d->format('Y-m-d');
                            break;
                        }
                    }
                }
            }

            // Store Category (handles 'store category', 'stores category', 'category', etc.)
            $category_id = null;
            $store_category_name = '';
            $cat_raw = $find_val(
                ['store_category', 'stores_category', 'category', 'category_name', 'categories', 'store_categories', 'stores_categories', 'store_cat', 'stores_cat', 'store_category_name', 'stores_category_name', 'storecategory', 'storescategory', 'store_type', 'stores_type', 'type', 'cat'],
                ['categor', 'store_cat', 'stores_cat', 'store_type', 'stores_type']
            );

            if (!empty($cat_raw)) {
                $clean_cat = trim(preg_replace('/\s+/', ' ', $cat_raw));
                $cat_lower = strtolower($clean_cat);

                // 1. Check if numeric category ID
                if (is_numeric($cat_raw)) {
                    $cat_db_row = $this->db->get_where('store_categories', ['id' => (int)$cat_raw])->row_array();
                    if ($cat_db_row) {
                        $category_id = $cat_db_row['id'];
                        $store_category_name = $cat_db_row['category_name'];
                    }
                }

                // 2. Check if cached or in DB by name or code
                if (!$category_id) {
                    if (isset($cats_map[$cat_lower])) {
                        $category_id = $cats_map[$cat_lower]['id'];
                        $store_category_name = $cats_map[$cat_lower]['category_name'];
                    } else {
                        // Check DB case-insensitively
                        $cat_db_row = $this->db->where('LOWER(TRIM(category_name))', $cat_lower)
                            ->or_where('LOWER(TRIM(code))', $cat_lower)
                            ->get('store_categories')->row_array();
                        if ($cat_db_row) {
                            $category_id = $cat_db_row['id'];
                            $store_category_name = $cat_db_row['category_name'];
                            $cats_map[$cat_lower] = $cat_db_row;
                        } else {
                            // Auto-create category in store_categories master
                            $new_cat = [
                                'category_name' => html_escape($clean_cat),
                                'code'          => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $clean_cat), 0, 10)),
                                'description'   => 'Auto-created during store import',
                                'status'        => 1,
                                'created_at'    => time(),
                                'updated_at'    => time()
                            ];
                            $this->db->insert('store_categories', $new_cat);
                            $new_cat_id = $this->db->insert_id();
                            $cats_map[$cat_lower] = ['id' => $new_cat_id, 'category_name' => $clean_cat];
                            $category_id = $new_cat_id;
                            $store_category_name = $clean_cat;
                        }
                    }
                }

                // Ensure store_category_name is always set if cat_raw exists
                if (empty($store_category_name)) {
                    $store_category_name = $clean_cat;
                }
            }

            // Assigned roles
            $assigned_role_ids = [];
            $roles_raw = $find_val(['assigned_roles', 'assigned_role', 'roles', 'role']);
            if (!empty($roles_raw)) {
                $role_names = explode(',', $roles_raw);
                foreach ($role_names as $rname) {
                    $rname_clean = strtolower(trim($rname));
                    if (empty($rname_clean)) continue;
                    if (isset($roles_map[$rname_clean])) {
                        $assigned_role_ids[] = $roles_map[$rname_clean];
                    } else {
                        $new_role = [
                            'role_name'  => html_escape(trim($rname)),
                            'status'     => 1,
                            'created_at' => time(),
                            'updated_at' => time()
                        ];
                        $this->db->insert('store_roles', $new_role);
                        $new_id = (string)$this->db->insert_id();
                        $roles_map[$rname_clean] = $new_id;
                        $assigned_role_ids[] = $new_id;
                    }
                }
            }

            $address    = $find_val(['address', 'store_address', 'stores_address', 'street', 'location']);
            $city       = $find_val(['city', 'store_city', 'stores_city', 'district', 'town']);
            $pin_code   = $find_val(['pin_code', 'pincode', 'pin', 'zip', 'zip_code', 'postal_code', 'store_pincode', 'stores_pincode']);
            $portal_url = $find_val(['portal_url', 'portal_link', 'portal', 'url', 'login_url', 'store_portal', 'stores_portal']);

            $status_raw = $find_val(['status', 'store_status', 'active', 'is_active']);
            $status = ($status_raw !== '') ? (int)$status_raw : 1;

            $store_data = [
                'store_name'        => html_escape($store_name),
                'store_code'        => html_escape($store_code),
                'category_id'       => $category_id,
                'store_category'    => html_escape($store_category_name),
                'zone'              => html_escape($zone),
                'contact_person'    => html_escape($contact_person),
                'live_date'         => html_escape($live_date),
                'phone'             => html_escape($phone),
                'mobile'            => html_escape($mobile),
                'email'             => html_escape($email),
                'portal_url'        => html_escape($portal_url),
                'address'           => html_escape($address),
                'city'              => html_escape($city),
                'state'             => html_escape($state),
                'pin_code'          => html_escape($pin_code),
                'status'            => $status,
                'updated_at'        => time()
            ];

            if (!empty($assigned_role_ids)) {
                $store_data['assigned_role_ids'] = json_encode(array_values(array_unique($assigned_role_ids)));
            }

            // Check if store exists by store_code or store_name
            $existing = null;
            if (!empty($store_code)) {
                $existing = $this->db->where('LOWER(TRIM(store_code))', strtolower(trim($store_code)))->get('stores')->row_array();
            }
            if (!$existing && !empty($store_name)) {
                $existing = $this->db->where('LOWER(TRIM(store_name))', strtolower(trim($store_name)))->get('stores')->row_array();
            }

            if ($existing) {
                // If assigned_roles in sheet was empty, preserve existing roles
                if (empty($assigned_role_ids) && !empty($existing['assigned_role_ids'])) {
                    $existing_roles = json_decode($existing['assigned_role_ids'], true);
                    if (!empty($existing_roles)) {
                        $store_data['assigned_role_ids'] = $existing['assigned_role_ids'];
                    }
                }
                $this->db->where('id', $existing['id'])->update('stores', $store_data);
                $success++;
                continue;
            }

            // If new store, set created_at and assigned_role_ids
            $store_data['created_at'] = time();
            if (!isset($store_data['assigned_role_ids'])) {
                $store_data['assigned_role_ids'] = json_encode([]);
            }
            $this->db->insert('stores', $store_data);
            $success++;
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function import_store_categories($rows)
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $cat_name = !empty($row['category_name']) ? trim($row['category_name']) : (!empty($row['name']) ? trim($row['name']) : '');

            if (empty($cat_name)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing category name";
                continue;
            }

            $exists = $this->db->get_where('store_categories', ['category_name' => $cat_name])->num_rows();
            if ($exists > 0) {
                $skipped++;
                $errors[] = "Row {$line}: Category '{$cat_name}' already exists";
                continue;
            }

            $code = !empty($row['code']) ? strtoupper(trim($row['code'])) : strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cat_name), 0, 10));

            $cat_data = [
                'category_name' => html_escape($cat_name),
                'code'          => html_escape($code),
                'description'   => isset($row['description']) ? html_escape(trim($row['description'])) : '',
                'status'        => isset($row['status']) && $row['status'] !== '' ? (int)$row['status'] : 1,
                'created_at'    => time(),
                'updated_at'    => time()
            ];

            $this->db->insert('store_categories', $cat_data);
            $success++;
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function import_roles($rows)
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $role_name = isset($row['role_name']) ? trim($row['role_name']) : '';

            if (empty($role_name)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing role name";
                continue;
            }

            $exists = $this->db->get_where('store_roles', ['role_name' => $role_name])->num_rows();
            if ($exists > 0) {
                $skipped++;
                $errors[] = "Row {$line}: Role '{$role_name}' already exists";
                continue;
            }

            $role_data = [
                'role_name'   => html_escape($role_name),
                'description' => isset($row['description']) ? html_escape(trim($row['description'])) : '',
                'status'      => isset($row['status']) && $row['status'] !== '' ? (int)$row['status'] : 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')
            ];

            $this->db->insert('store_roles', $role_data);
            $success++;
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function import_store_users($rows)
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $store_code = isset($row['store_code']) ? trim($row['store_code']) : '';
            $role_name = isset($row['role_name']) ? trim($row['role_name']) : '';
            $username = isset($row['username']) ? trim($row['username']) : '';

            if (empty($store_code)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing store code";
                continue;
            }

            $store = $this->db->group_start()
                ->where('store_code', $store_code)
                ->or_where('store_name', $store_code)
                ->group_end()
                ->get('stores')->row_array();

            if (!$store) {
                $skipped++;
                $errors[] = "Row {$line}: Store '{$store_code}' not found";
                continue;
            }

            if (empty($role_name)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing role name";
                continue;
            }

            $role = $this->db->get_where('store_roles', ['role_name' => $role_name])->row_array();
            if (!$role) {
                $new_role = [
                    'role_name'   => html_escape($role_name),
                    'status'      => 1,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s')
                ];
                $this->db->insert('store_roles', $new_role);
                $role_id = $this->db->insert_id();
                $role_title = $role_name;
            } else {
                $role_id = $role['id'];
                $role_title = $role['role_name'];
            }

            $assigned_role_ids = json_decode($store['assigned_role_ids'] ?: '[]', true);
            if (!in_array((string)$role_id, $assigned_role_ids)) {
                $assigned_role_ids[] = (string)$role_id;
                $this->db->where('id', $store['id'])->update('stores', ['assigned_role_ids' => json_encode($assigned_role_ids)]);
            }

            $pharmacist_id = null;
            if (!empty($row['pharmacist_email'])) {
                $ph = $this->db->get_where('users', ['email' => trim($row['pharmacist_email']), 'role_id' => 2])->row_array();
                if ($ph) {
                    $pharmacist_id = $ph['id'];
                    if (empty($username)) {
                        $username = explode('@', $ph['email'])[0];
                    }
                }
            }

            if (empty($username)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing username";
                continue;
            }

            $exists = $this->db->get_where('store_users', ['username' => $username, 'store_id' => $store['id']])->num_rows();
            if ($exists > 0) {
                $skipped++;
                $errors[] = "Row {$line}: Username '{$username}' already exists for store '{$store['store_name']}'";
                continue;
            }

            $designation = isset($row['designation']) ? trim($row['designation']) : '';
            $password = !empty($row['password']) ? trim($row['password']) : substr(str_shuffle("abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%"), 0, 10);
            $portal_link = !empty($row['portal_link']) ? trim($row['portal_link']) : ($store['portal_url'] ?? '');

            $store_user_data = [
                'store_id'      => $store['id'],
                'pharmacist_id' => $pharmacist_id,
                'role_id'       => $role_id,
                'role_title'    => html_escape($role_title),
                'designation'   => html_escape($designation),
                'username'      => html_escape($username),
                'password'      => html_escape($password),
                'portal_link'   => html_escape($portal_link),
                'notes'         => isset($row['notes']) ? html_escape(trim($row['notes'])) : '',
                'status'        => isset($row['status']) && $row['status'] !== '' ? (int)$row['status'] : 1,
                'created_at'    => time(),
                'updated_at'    => time()
            ];

            $this->db->insert('store_users', $store_user_data);
            $success++;
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function import_instructors($rows)
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $email = isset($row['email']) ? trim($row['email']) : '';
            $first_name = isset($row['first_name']) ? trim($row['first_name']) : '';
            $last_name = isset($row['last_name']) ? trim($row['last_name']) : '';

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                $errors[] = "Row {$line}: Invalid or missing email ('{$email}')";
                continue;
            }

            if (empty($first_name)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing first name";
                continue;
            }

            $existing_user = $this->db->get_where('users', ['email' => $email])->row_array();
            if ($existing_user) {
                if ($existing_user['is_instructor'] == 1) {
                    $skipped++;
                    $errors[] = "Row {$line}: Instructor with email '{$email}' already exists";
                    continue;
                } else {
                    $update_data = [
                        'is_instructor' => 1
                    ];
                    if (empty($existing_user['payment_keys'])) {
                        $update_data['payment_keys'] = json_encode([]);
                    }
                    if (!empty($row['phone'])) {
                        $update_data['phone'] = html_escape(trim($row['phone']));
                    }
                    if (!empty($row['address'])) {
                        $update_data['address'] = html_escape(trim($row['address']));
                    }
                    if (!empty($row['biography'])) {
                        $update_data['biography'] = html_escape(trim($row['biography']));
                    }
                    if (isset($row['status']) && $row['status'] !== '') {
                        $update_data['status'] = (int)$row['status'];
                    }
                    $this->db->where('id', $existing_user['id'])->update('users', $update_data);
                    $success++;
                    continue;
                }
            }

            $password = !empty($row['password']) ? trim($row['password']) : '12345678';

            $user_data = [
                'first_name'    => html_escape($first_name),
                'last_name'     => html_escape($last_name),
                'email'         => html_escape($email),
                'password'      => sha1($password),
                'phone'         => isset($row['phone']) ? html_escape(trim($row['phone'])) : '',
                'address'       => isset($row['address']) ? html_escape(trim($row['address'])) : '',
                'biography'     => isset($row['biography']) ? html_escape(trim($row['biography'])) : '',
                'role_id'       => 2,
                'is_instructor' => 1,
                'status'        => isset($row['status']) && $row['status'] !== '' ? (int)$row['status'] : 1,
                'date_added'    => time(),
                'social_links'  => json_encode(['facebook' => '', 'twitter' => '', 'linkedin' => '']),
                'payment_keys'  => json_encode([]),
                'wishlist'      => json_encode([]),
                'image'         => md5(rand(10000, 10000000))
            ];

            $this->db->insert('users', $user_data);
            $success++;
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    private function import_enrollments($rows)
    {
        $success = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            $user_identifier = '';
            if (!empty($row['user_email'])) {
                $user_identifier = trim($row['user_email']);
            } elseif (!empty($row['email'])) {
                $user_identifier = trim($row['email']);
            } elseif (!empty($row['student_email'])) {
                $user_identifier = trim($row['student_email']);
            } elseif (!empty($row['pharmacist_email'])) {
                $user_identifier = trim($row['pharmacist_email']);
            } elseif (!empty($row['employee_id'])) {
                $user_identifier = trim($row['employee_id']);
            } elseif (!empty($row['user_id'])) {
                $user_identifier = trim($row['user_id']);
            } elseif (!empty($row['user'])) {
                $user_identifier = trim($row['user']);
            } elseif (!empty($row['student'])) {
                $user_identifier = trim($row['student']);
            } elseif (!empty($row['pharmacist'])) {
                $user_identifier = trim($row['pharmacist']);
            } elseif (!empty($row['username'])) {
                $user_identifier = trim($row['username']);
            }

            if (empty($user_identifier)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing user email or identifier";
                continue;
            }

            $user = $this->db->get_where('users', ['email' => $user_identifier])->row_array();
            if (!$user) {
                $user = $this->db->where('LOWER(email)', strtolower($user_identifier))->get('users')->row_array();
            }
            if (!$user) {
                $user = $this->db->get_where('users', ['employee_id' => $user_identifier])->row_array();
            }
            if (!$user && is_numeric($user_identifier)) {
                $user = $this->db->get_where('users', ['id' => (int)$user_identifier])->row_array();
            }

            // If user does not exist but a valid email was provided, auto-create student account
            if (!$user && (filter_var($user_identifier, FILTER_VALIDATE_EMAIL) || strpos($user_identifier, '@') !== false)) {
                $first_name = !empty($row['first_name']) ? trim($row['first_name']) : ucfirst(explode('@', $user_identifier)[0]);
                $last_name  = !empty($row['last_name']) ? trim($row['last_name']) : '';
                $new_user_data = [
                    'first_name'   => html_escape($first_name),
                    'last_name'    => html_escape($last_name),
                    'email'        => html_escape($user_identifier),
                    'password'     => sha1('12345678'),
                    'role_id'      => 2,
                    'status'       => 1,
                    'date_added'   => time(),
                    'social_links' => json_encode(['facebook' => '', 'twitter' => '', 'linkedin' => '']),
                    'payment_keys' => json_encode([]),
                    'wishlist'     => json_encode([]),
                    'image'        => md5(rand(10000, 10000000))
                ];
                $this->db->insert('users', $new_user_data);
                $new_id = $this->db->insert_id();
                $user = $this->db->get_where('users', ['id' => $new_id])->row_array();
            }

            if (!$user) {
                $skipped++;
                $errors[] = "Row {$line}: User '{$user_identifier}' not found";
                continue;
            }

            $course_identifier = '';
            if (!empty($row['course_title'])) {
                $course_identifier = trim($row['course_title']);
            } elseif (!empty($row['course'])) {
                $course_identifier = trim($row['course']);
            } elseif (!empty($row['course_name'])) {
                $course_identifier = trim($row['course_name']);
            } elseif (!empty($row['course_id'])) {
                $course_identifier = trim($row['course_id']);
            } elseif (!empty($row['title'])) {
                $course_identifier = trim($row['title']);
            } elseif (!empty($row['courses'])) {
                $course_identifier = trim($row['courses']);
            }

            if (empty($course_identifier)) {
                $skipped++;
                $errors[] = "Row {$line}: Missing course title or ID";
                continue;
            }

            $matched_courses = [];
            $course = null;
            if (is_numeric($course_identifier)) {
                $course = $this->db->get_where('course', ['id' => (int)$course_identifier])->row_array();
            }
            if (!$course) {
                $course = $this->db->get_where('course', ['title' => $course_identifier])->row_array();
            }
            if (!$course) {
                $course = $this->db->where('LOWER(title)', strtolower($course_identifier))->get('course')->row_array();
            }
            if (!$course) {
                $course = $this->db->like('LOWER(title)', strtolower($course_identifier))->get('course')->row_array();
            }

            if ($course) {
                $matched_courses[] = $course;
            } elseif (strpos($course_identifier, ',') !== false) {
                $split_titles = explode(',', $course_identifier);
                foreach ($split_titles as $st) {
                    $st = trim($st);
                    if (empty($st)) continue;
                    $c = null;
                    if (is_numeric($st)) {
                        $c = $this->db->get_where('course', ['id' => (int)$st])->row_array();
                    }
                    if (!$c) {
                        $c = $this->db->get_where('course', ['title' => $st])->row_array();
                    }
                    if (!$c) {
                        $c = $this->db->where('LOWER(title)', strtolower($st))->get('course')->row_array();
                    }
                    if (!$c) {
                        $c = $this->db->like('LOWER(title)', strtolower($st))->get('course')->row_array();
                    }
                    if ($c) {
                        $matched_courses[] = $c;
                    }
                }
            }

            if (empty($matched_courses)) {
                $skipped++;
                $errors[] = "Row {$line}: Course '{$course_identifier}' not found";
                continue;
            }

            foreach ($matched_courses as $c_item) {
                $expiry_date = null;
                $expiry_days = '';
                if (isset($row['expiry_days'])) {
                    $expiry_days = trim($row['expiry_days']);
                } elseif (isset($row['expiry_day'])) {
                    $expiry_days = trim($row['expiry_day']);
                } elseif (isset($row['expiry'])) {
                    $expiry_days = trim($row['expiry']);
                } elseif (isset($row['days'])) {
                    $expiry_days = trim($row['days']);
                } elseif (isset($row['duration'])) {
                    $expiry_days = trim($row['duration']);
                }

                if (!empty($expiry_days)) {
                    if (is_numeric($expiry_days)) {
                        $expiry_date = strtotime("+" . (int)$expiry_days . " days");
                    } else {
                        $parsed = strtotime($expiry_days);
                        if ($parsed !== false) {
                            $expiry_date = $parsed;
                        }
                    }
                } elseif (!empty($row['expiry_date'])) {
                    $parsed = strtotime(trim($row['expiry_date']));
                    if ($parsed !== false) {
                        $expiry_date = $parsed;
                    }
                } elseif (!empty($c_item['expiry_period']) && $c_item['expiry_period'] > 0) {
                    $days = $c_item['expiry_period'] * 30;
                    $expiry_date = strtotime("+" . $days . " days");
                }

                $existing_enrol = $this->db->get_where('enrol', [
                    'user_id'   => $user['id'],
                    'course_id' => $c_item['id']
                ])->row_array();

                if ($existing_enrol) {
                    $update_data = [
                        'last_modified' => time(),
                        'date_added'    => time()
                    ];
                    if ($expiry_date !== null) {
                        $update_data['expiry_date'] = (string)$expiry_date;
                    }
                    $this->db->where('id', $existing_enrol['id'])->update('enrol', $update_data);
                    $success++;
                } else {
                    $enrol_data = [
                        'user_id'       => $user['id'],
                        'course_id'     => $c_item['id'],
                        'gifted_by'     => 0,
                        'expiry_date'   => $expiry_date !== null ? (string)$expiry_date : null,
                        'date_added'    => time(),
                        'last_modified' => time()
                    ];
                    $this->db->insert('enrol', $enrol_data);
                    $success++;
                }
            }
        }

        return ['success' => $success, 'skipped' => $skipped, 'errors' => $errors];
    }

    public function bulk_import($type = "")
    {
        if ($this->session->userdata('admin_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $redirect_map = [
            'pharmacists'       => 'admin/users',
            'stores'            => 'admin/stores',
            'store_categories'  => 'admin/store_categories',
            'categories'        => 'admin/store_categories',
            'roles'             => 'admin/store_roles',
            'store_users'       => 'admin/store_users',
            'instructors'       => 'admin/instructors',
            'instructor'        => 'admin/instructors',
            'enrollments'       => 'admin/enrol_history',
            'enrollment'        => 'admin/enrol_history',
            'course_enrollment' => 'admin/enrol_history'
        ];

        $redirect_url = isset($redirect_map[$type]) ? $redirect_map[$type] : 'admin/dashboard';

        if (!isset($_FILES['import_file']) || empty($_FILES['import_file']['tmp_name'])) {
            $this->session->set_flashdata('error_message', get_phrase('please_upload_a_valid_file'));
            redirect(site_url($redirect_url), 'refresh');
        }

        $rows = $this->parse_imported_file($_FILES['import_file']['tmp_name'], $_FILES['import_file']['name']);

        if (empty($rows)) {
            $this->session->set_flashdata('error_message', get_phrase('no_valid_data_found_in_file'));
            redirect(site_url($redirect_url), 'refresh');
        }

        $result = ['success' => 0, 'skipped' => 0, 'errors' => []];
        if ($type == 'pharmacists') {
            $result = $this->import_pharmacists($rows);
        } elseif ($type == 'stores') {
            $result = $this->import_stores($rows);
        } elseif ($type == 'store_categories' || $type == 'categories') {
            $result = $this->import_store_categories($rows);
        } elseif ($type == 'roles') {
            $result = $this->import_roles($rows);
        } elseif ($type == 'store_users') {
            $result = $this->import_store_users($rows);
        } elseif ($type == 'instructors' || $type == 'instructor') {
            $result = $this->import_instructors($rows);
        } elseif ($type == 'enrollments' || $type == 'enrollment' || $type == 'course_enrollment') {
            $result = $this->import_enrollments($rows);
        }

        $msg = "Imported: {$result['success']} records. Skipped: {$result['skipped']}.";
        if (!empty($result['errors'])) {
            $msg .= " Details: " . implode(" | ", array_slice($result['errors'], 0, 5));
            if (count($result['errors']) > 5) {
                $msg .= " ...and " . (count($result['errors']) - 5) . " more.";
            }
        }

        if ($result['success'] > 0 && $result['skipped'] == 0) {
            $this->session->set_flashdata('flash_message', $msg);
        } elseif ($result['success'] > 0 && $result['skipped'] > 0) {
            $this->session->set_flashdata('flash_message', $msg);
        } else {
            $this->session->set_flashdata('error_message', $msg);
        }

        redirect(site_url($redirect_url), 'refresh');
    }
}






