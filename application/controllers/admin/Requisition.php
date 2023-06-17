<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Requisition extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('requisition_model');
//        $this->load->model('estimates_model');
        $this->load->model('transactions_model');
        $this->load->library('gst');
        $this->load->helper('ckeditor');
    }


    public function index($action = NULL, $id = NULL, $item_id = NULL)
    {

        $data['page'] = lang('sales');
        $data['sub_active'] = lang('requisition');
        if (!empty($item_id)) {
            $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $id));
            if (!empty($can_edit)) {
                $data['item_info'] = $this->requisition_model->check_by(array('requisition_items_id' => $item_id), 'tbl_requisition_items');
            }
        }
        if ($action == 'edit_estimates') {
            $data['active'] = 2;
            $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $id));
            if (!empty($can_edit)) {
                $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
                if (!empty($data['estimates_info']->client_id)) {
                    $data['estimate_to_merge'] = $this->requisition_model->check_for_merge_invoice($data['estimates_info']->client_id, $id);
                }
            }
        } else if ($action == 'project') {
            $data['project_id'] = $id;
            $data['project_info'] = $this->requisition_model->check_by(array('project_id' => $id), 'tbl_project');
            $data['active'] = 2;
        } else {
            $data['active'] = 1;
        }
        // get all client
        $this->requisition_model->_table_name = 'tbl_client';
        $this->requisition_model->_order_by = 'client_id';
        $data['all_client'] = $this->requisition_model->get();
        // get permission user
        $data['permission_user'] = $this->requisition_model->all_permission_user('14');
        $data['all_estimates_info'] = $this->requisition_model->get_permission('tbl_requisitions');
        if ($action == 'requisition_details') {
            $data['title'] = "Requisitions Details"; //Page title
            $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
            if (empty($data['estimates_info'])) {
                $type = "error";
                $message = lang('no_record_found');
                set_message($type, $message);
                redirect('admin/requisition');
            }
            $subview = 'requisition_details';
        } elseif ($action == 'estimates_history') {
            $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
            $data['title'] = "Estimates History"; //Page title
            $subview = 'estimates_history';
        } elseif ($action == 'email_estimates') {
            $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
            $data['title'] = "Email Estimates"; //Page title
            $subview = 'email_estimates';
        } elseif ($action == 'pdf_estimates') {
            $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
            $data['title'] = "Estimates PDF"; //Page title
            $this->load->helper('dompdf');
            $viewfile = $this->load->view('admin/estimates/estimates_pdf', $data, TRUE);
            pdf_create($viewfile, slug_it('Estimates  # ' . $data['estimates_info']->reference_no));
        } else {
            $data['title'] = "Requisition"; //Page title
            $subview = 'requisition';
        }
        $data['subview'] = $this->load->view('admin/requisition/' . $subview, $data, true);
        $this->load->view('admin/_layout_main', $data); //page load
    }


    public function estimates_state_report()
    {
        $data = array();
        $pathonor_jonno['estimates_state_report_div'] = $this->load->view("admin/estimates/estimates_state_report", $data, true);
        echo json_encode($pathonor_jonno);
        exit;
    }


    public function requisitionList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_requisitions';
            $this->datatables->join_table = array('tbl_client');
            $this->datatables->join_where = array('tbl_requisitions.client_id=tbl_client.client_id');
            $this->datatables->select = 'tbl_requisitions.*,tbl_client.name';
            $this->datatables->column_order = array('reference_no', 'tbl_client.name', 'requisition_date', 'due_date', 'status', 'tags');
            $this->datatables->column_search = array('reference_no', 'tbl_client.name', 'requisition_date', 'due_date', 'status', 'tags');
            $this->datatables->order = array('requisition_id' => 'desc');

            if (empty($filterBy)) {
                $filterBy = '_' . date('Y');
            }
            if (!empty($filterBy) && !is_numeric($filterBy)) {
                $ex = explode('_', $filterBy);
                if ($ex[0] != 'c') {
                    $filterBy = $filterBy;
                }
            }
            $where = array();
            $where_in = null;
            if (!empty($search_by)) {
                if ($search_by == 'by_project') {
                    $where = array('project_id' => $filterBy);
                }
                if ($search_by == 'by_agent') {
                    $where = array('user_id' => $filterBy);
                }
                if ($search_by == 'by_client') {
                    $where = array('tbl_requisitions.client_id' => $filterBy);
                }
            } else {
                if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                    if ($filterBy == 'last_month') {
                        $month = date('Y-m', strtotime('-1 months'));
                    } else {
                        $month = date('Y-m');
                    }
                    $where = array('requisition_month' => $month);
                } else if ($filterBy == 'expired') {
                    $where = array('UNIX_TIMESTAMP(due_date) <' => strtotime(date('Y-m-d')));
                    $status = array('draft', 'pending');
                    $where_in = array('status', $status);
                } else if (strstr($filterBy, '_')) {
                    $year = str_replace('_', '', $filterBy);
                    $where = array('requisition_year' => $year);
                } else if (!empty($filterBy) && $filterBy != 'all') {
                    $where = array('status' => $filterBy);
                }
            }
            // get all estimate
            $fetch_data = $this->datatables->get_datatable_permission($where);

            $data = array();

            $edited = can_action('14', 'edited');
            $deleted = can_action('14', 'deleted');
            foreach ($fetch_data as $_key => $v_estimates) {
                if (!empty($v_estimates)) {
                    $action = null;
                    $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $v_estimates->requisition_id));
                    $can_delete = $this->requisition_model->can_action('tbl_requisitions', 'delete', array('requisition_id' => $v_estimates->requisition_id));

                    if ($v_estimates->status == 'pending') {
                        $label = "info";
                    } elseif ($v_estimates->status == 'accepted') {
                        $label = "success";
                    } else {
                        $label = "danger";
                    }

                    $sub_array = array();
                    $name = null;
                    $name .= '<a class="text-info" href="' . base_url() . 'admin/requisition/index/requisition_details/' . $v_estimates->requisition_id . '">' . $v_estimates->reference_no . '</a>';
                    if ($v_estimates->invoiced == 'Yes') {
                        $invoice_info = $this->db->where('invoices_id', $v_estimates->invoices_id)->get('tbl_invoices')->row();
                        if (!empty($invoice_info)) {
                            $name .= '<p class="text-sm m0 p0"><a class="text-success" href="' . base_url() . 'admin/invoice/manage_invoice/invoice_details/' . $invoice_info->invoices_id . '">' . lang('invoiced') . '</a></p>';
                        }
                    }
                    $sub_array[] = $name;
                    $sub_array[] = strftime(config_item('date_format'), strtotime($v_estimates->requisition_date));
                    $overdue = null;
                    if (strtotime($v_estimates->due_date) < strtotime(date('Y-m-d')) && $v_estimates->status == 'pending' || strtotime($v_estimates->due_date) < strtotime(date('Y-m-d')) && $v_estimates->status == ('draft')) {
                        $overdue .= '<span class="label label-danger ">' . lang("expired") . '</span>';
                    }
                    $sub_array[] = strftime(config_item('date_format'), strtotime($v_estimates->due_date)) . ' ' . $overdue;

                    $sub_array[] = '<span class="tags">' . client_name($v_estimates->client_id) . '</span>';

                    $sub_array[] = display_money($this->requisition_model->requisition_calculation('total', $v_estimates->requisition_id), client_currency($v_estimates->client_id));
                    $sub_array[] = "<span class='tags label label-" . $label . "'>" . lang($v_estimates->status) . "</span>";

                    $sub_array[] = get_tags($v_estimates->tags, true);

                    $custom_form_table = custom_form_table(10, $v_estimates->requisition_id);

                    if (!empty($custom_form_table)) {
                        foreach ($custom_form_table as $c_label => $v_fields) {
                            $sub_array[] = $v_fields;
                        }
                    }
                    if (!empty($can_edit) && !empty($edited)) {
                        $action .= '<a data-toggle="modal" data-target="#myModal"
                                                               title="' . lang('clone') . ' ' . lang('requisition') . '"
                                                               href="' . base_url() . 'admin/estimates/clone_estimate/' . $v_estimates->requisition_id . '"
                                                               class="btn btn-xs btn-purple">
                                                                <i class="fa fa-copy"></i></a>' . ' ';
                        $action .= btn_edit('admin/estimates/index/edit_estimates/' . $v_estimates->requisition_id) . ' ';
                    }
                    if (!empty($can_delete) && !empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/requisition/delete/delete_estimates/$v_estimates->requisition_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                    }
                    $change_status = null;
                    if (!empty($can_edit) && !empty($edited)) {
                        $ch_url = base_url() . 'admin/requisition/';
                        $change_status .= '<div class="btn-group">
        <button class="btn btn-xs btn-default dropdown-toggle"
                data-toggle="dropdown">
            <span class="caret"></span></button>
        <ul class="dropdown-menu animated zoomIn">';
                        $change_status .= '<li><a href="' . $ch_url . 'index/requisition_details/' . $v_estimates->requisition_id . '">' . lang('preview') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'index/email_estimates' . $v_estimates->requisition_id . '">' . lang('send_email') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'index/estimates_history' . $v_estimates->requisition_id . '">' . lang('history') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'change_status/declined/' . $v_estimates->requisition_id . '">' . lang('declined') . '</a></li>';
                        $change_status .= '<li><a href="' . $ch_url . 'change_status/accepted/' . $v_estimates->requisition_id . '">' . lang('accepted') . '</a></li>';
                        $change_status .= '</ul></div>';
                        $action .= $change_status;
                    }

                    $sub_array[] = $action;
                    $data[] = $sub_array;
                }
            }
            render_table($data, $where, $where_in);
        } else {
            redirect('admin/dashboard');
        }
    }

    public
    function client_change_data($customer_id, $current_invoice = 'undefined')
    {
        if ($this->input->is_ajax_request()) {
            $data = array();
            $data['client_currency'] = $this->requisition_model->client_currency_symbol($customer_id);
            $_data['estimate_to_merge'] = $this->requisition_model->check_for_merge_invoice($customer_id, $current_invoice);
            $data['merge_info'] = $this->load->view('admin/estimates/merge_estimate', $_data, true);
            echo json_encode($data);
            exit();
        }
    }

    public
    function get_merge_data($id)
    {
        $invoice_items = $this->requisition_model->ordered_items_by_id($id);
        $i = 0;
        foreach ($invoice_items as $item) {
            $invoice_items[$i]->taxname = $this->requisition_model->get_invoice_item_taxes($item->requisition_items_id);
            $invoice_items[$i]->qty = $item->quantity;
            $invoice_items[$i]->rate = $item->unit_cost;
            $i++;
        }
        echo json_encode($invoice_items);
        exit();
    }

    public
    function pdf_estimates($id)
    {
        $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
        if (empty($data['estimates_info'])) {
            $type = "error";
            $message = "No Record Found";
            set_message($type, $message);
            redirect('admin/estimates');
        }
        $data['title'] = lang('estimates'); //Page title
        $this->load->helper('dompdf');
        $viewfile = $this->load->view('admin/estimates/estimates_pdf', $data, TRUE);
        //        echo "<pre>";
        //        print_r($viewfile);
        //        exit();
        pdf_create($viewfile, slug_it(lang('estimates') . ' # ' . $data['estimates_info']->reference_no));
    }

    public
    function save_requisition($id = NULL)
    {
        try {
            $created = can_action('14', 'created');
            $edited = can_action('14', 'edited');
            if (!empty($created) || !empty($edited) && !empty($id)) {
                $data = $this->requisition_model->array_from_post(array('reference_no', 'client_id', 'project_id', 'discount_type', 'tags',
                    'discount_percent', 'user_id', 'adjustment', 'discount_total', 'show_quantity_as', 'task_id'));
                $data['client_visible'] = ($this->input->post('client_visible') == 'Yes') ? 'Yes' : 'No';
                $data['requisition_date'] = date('Y-m-d', strtotime($this->input->post('requisition_date', TRUE)));
                if (empty($data['requisition_date'])) {
                    $data['requisition_date'] = date('Y-m-d');
                }
                $data['requisition_year'] = date('Y', strtotime($this->input->post('requisition_date', TRUE)));
                $data['requisition_month'] = date('Y-m', strtotime($this->input->post('requisition_date', TRUE)));
                $data['due_date'] = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
                $data['notes'] = $this->input->post('notes', TRUE);
                $tax['tax_name'] = $this->input->post('total_tax_name', TRUE);
                $tax['total_tax'] = $this->input->post('total_tax', TRUE);
                $data['total_tax'] = json_encode($tax);
                $i_tax = 0;
                if (!empty($tax['total_tax'])) {
                    foreach ($tax['total_tax'] as $v_tax) {
                        $i_tax += $v_tax;
                    }
                }
                $data['tax'] = $i_tax;
                $save_as_draft = $this->input->post('status', TRUE);
                if (!empty($save_as_draft)) {
                    $data['status'] = $save_as_draft;
                } else {
                    $data['status'] = 'pending';
                }

                $currency = $this->requisition_model->client_currency_symbol($data['client_id']);
                if (!empty($currency->code)) {
                    $curren = $currency->code;
                } else {
                    $curren = config_item('default_currency');
                }
                $data['currency'] = $curren;

                $permission = $this->input->post('permission', true);
                if (!empty($permission)) {
                    if ($permission == 'everyone') {
                        $assigned = 'all';
                    } else {
                        $assigned_to = $this->requisition_model->array_from_post(array('assigned_to'));
                        if (!empty($assigned_to['assigned_to'])) {
                            foreach ($assigned_to['assigned_to'] as $assign_user) {
                                $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                            }
                        }
                    }
                    if (!empty($assigned)) {
                        if ($assigned != 'all') {
                            $assigned = json_encode($assigned);
                        }
                    } else {
                        $assigned = 'all';
                    }
                    $data['permission'] = $assigned;
                } else {
                    set_message('error', lang('assigned_to') . ' Field is required');
                    if (empty($_SERVER['HTTP_REFERER'])) {
                        redirect('admin/requisition');
                    } else {
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }

                // get all client
                $this->requisition_model->_table_name = 'tbl_requisitions';
                $this->requisition_model->_primary_key = 'requisition_id';
                if (!empty($id)) {
                    $requisition_id = $id;
                    $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $id));
                    if (!empty($can_edit)) {
                        $this->requisition_model->save($data, $id);
                    } else {
                        set_message('error', lang('there_in_no_value'));
                        redirect('admin/requisition');
                    }
                    $this->requisition_model->save($data, $id);
                    $action = ('activity_requisition_updated');
                    $msg = lang('requisition_updated');
                    $description = 'not_requisition_updated';
                } else {

                    $requisition_id = $this->requisition_model->save($data);
                    $action = ('activity_requisition_created');
                    $description = 'not_estimate_created';
                    $msg = lang('requisition_created');
                }
                save_custom_field(10, $requisition_id);

                // save items
                $invoices_to_merge = $this->input->post('invoices_to_merge', TRUE);
                $cancel_merged_invoices = $this->input->post('cancel_merged_estimate', TRUE);
                if (!empty($invoices_to_merge)) {
                    foreach ($invoices_to_merge as $inv_id) {
                        if (empty($cancel_merged_invoices)) {
                            $this->db->where('requisition_id', $inv_id);
                            $this->db->delete('tbl_requisitions');

                            $this->db->where('requisition_id', $inv_id);
                            $this->db->delete('tbl_requisition_items');
                        } else {
                            $mdata = array('status' => 'cancelled');
                            $this->requisition_model->_table_name = 'tbl_requisitions';
                            $this->requisition_model->_primary_key = 'requisition_id';
                            $this->requisition_model->save($mdata, $inv_id);
                        }
                    }
                }

                $removed_items = $this->input->post('removed_items', TRUE);
                if (!empty($removed_items)) {
                    foreach ($removed_items as $r_id) {
                        if ($r_id != 'undefined') {
                            $this->db->where('requisition_items_id', $r_id);
                            $this->db->delete('tbl_requisition_items');
                        }
                    }
                }

                $itemsid = $this->input->post('requisition_items_id', TRUE);
                $items_data = $this->input->post('items', true);

                if (!empty($items_data)) {
                    $index = 0;
                    foreach ($items_data as $items) {
                        $items['requisition_id'] = $requisition_id;
                        unset($items['invoice_items_id']);
                        unset($items['total_qty']);
                        $tax = 0;
                        if (!empty($items['taxname'])) {
                            foreach ($items['taxname'] as $tax_name) {
                                $tax_rate = explode("|", $tax_name);
                                $tax += $tax_rate[1];
                            }
                            $items['item_tax_name'] = $items['taxname'];
                            unset($items['taxname']);
                            $items['item_tax_name'] = json_encode($items['item_tax_name']);
                        }
                        $price = $items['quantity'] * $items['unit_cost'];
                        $items['item_tax_total'] = ($price / 100 * $tax);
                        $items['total_cost'] = $price;
                        // get all client
                        $this->requisition_model->_table_name = 'tbl_requisition_items';
                        $this->requisition_model->_primary_key = 'requisition_items_id';
                        if (!empty($itemsid[$index])) {
                            $items_id = $itemsid[$index];
                            $this->requisition_model->save($items, $items_id);
                        } else {
                            $items_id = $this->requisition_model->save($items);
                        }
                        $index++;
                    }
                }
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'requisition',
                    'module_field_id' => $requisition_id,
                    'activity' => $action,
                    'icon' => 'fa-shopping-cart',
                    'link' => 'admin/estimates/index/requisition_details/' . $requisition_id,
                    'value1' => $data['reference_no']
                );
                $this->requisition_model->_table_name = 'tbl_activities';
                $this->requisition_model->_primary_key = 'activities_id';
                $this->requisition_model->save($activity);

                // send notification to client
                if (!empty($data['client_id'])) {
                    $client_info = $this->requisition_model->check_by(array('client_id' => $data['client_id']), 'tbl_client');
                    if (!empty($client_info->primary_contact)) {
                        $notifyUser = array($client_info->primary_contact);
                    } else {
                        $user_info = $this->requisition_model->check_by(array('company' => $data['client_id']), 'tbl_account_details');
                        if (!empty($user_info)) {
                            $notifyUser = array($user_info->user_id);
                        }
                    }
                }
                if (!empty($notifyUser)) {
                    foreach ($notifyUser as $v_user) {
                        if ($v_user != $this->session->userdata('user_id')) {
                            add_notification(array(
                                'to_user_id' => $v_user,
                                'icon' => 'shopping-cart',
                                'description' => $description,
                                'link' => 'client/requisition/index/requisition_details/' . $requisition_id,
                                'value' => $data['reference_no'],
                            ));
                        }
                    }
                    show_notification($notifyUser);
                }

                // messages for user
                $type = "success";
                $message = $msg;
                set_message($type, $message);
            }
            if (!empty($data['project_id']) && is_numeric($data['project_id'])) {
                redirect('admin/projects/project_details/' . $data['project_id']);
            } else {
                redirect('admin/requisition/index/requisition_details/' . $requisition_id);
            }
            redirect('admin/requisition');
        }  catch (\Exception $e) {
            echo "Error: ".$e->getMessage();
            die();
        }
    }

    public
    function insert_items($estimates_id)
    {
        $edited = can_action('14', 'edited');
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $estimates_id));
        if (!empty($can_edit) && !empty($edited) && !empty($estimates_id)) {
            $data['requisition_id'] = $estimates_id;
            $data['modal_subview'] = $this->load->view('admin/estimates/_modal_insert_items', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/estimates');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function add_insert_items($estimates_id)
    {
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $estimates_id));
        $edited = can_action('14', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $saved_items_id = $this->input->post('saved_items_id', TRUE);
            if (!empty($saved_items_id)) {
                foreach ($saved_items_id as $v_items_id) {
                    $items_info = $this->requisition_model->check_by(array('saved_items_id' => $v_items_id), 'tbl_saved_items');
                    $tax_info = json_decode($items_info->tax_rates_id);
                    $tax_name = array();
                    if (!empty($tax_info)) {
                        foreach ($tax_info as $v_tax) {
                            $all_tax = $this->db->where('tax_rates_id', $v_tax)->get('tbl_tax_rates')->row();
                            $tax_name[] = $all_tax->tax_rate_name . '|' . $all_tax->tax_rate_percent;
                        }
                    }
                    if (!empty($tax_name)) {
                        $tax_name = $tax_name;
                    } else {
                        $tax_name = array();
                    }

                    $data['quantity'] = 1;
                    $data['requisition_id'] = $estimates_id;
                    $data['item_name'] = $items_info->item_name;
                    $data['item_desc'] = $items_info->item_desc;
                    $data['hsn_code'] = $items_info->hsn_code;
                    $data['unit_cost'] = $items_info->unit_cost;
                    $data['item_tax_rate'] = '0.00';
                    $data['item_tax_name'] = json_encode($tax_name);
                    $data['item_tax_total'] = $items_info->item_tax_total;
                    $data['total_cost'] = $items_info->unit_cost;

                    $this->requisition_model->_table_name = 'tbl_requisition_items';
                    $this->requisition_model->_primary_key = 'requisition_items_id';
                    $items_id = $this->requisition_model->save($data);
                    $action = 'activity_estimates_items_added';
                    $msg = lang('estimate_item_save');
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'estimates',
                        'module_field_id' => $items_id,
                        'activity' => $action,
                        'icon' => 'fa-shopping-cart',
                        'link' => 'admin/requisition/index/requisition_details/' . $estimates_id,
                        'value1' => $items_info->item_name
                    );
                    $this->requisition_model->_table_name = 'tbl_activities';
                    $this->requisition_model->_primary_key = 'activities_id';
                    $this->requisition_model->save($activity);
                }
                $type = "success";
                $this->update_invoice_tax($saved_items_id, $estimates_id);
            } else {
                $type = "error";
                $msg = 'Please Select a items';
            }
            $message = $msg;
            set_message($type, $message);
            redirect('admin/requisition/index/requisition_details/' . $estimates_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/estimates');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    function update_invoice_tax($saved_items_id, $estimates_id)
    {

        $invoice_info = $this->requisition_model->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
        $tax_info = json_decode($invoice_info->total_tax);

        $tax_name = $tax_info->tax_name;
        $total_tax = $tax_info->total_tax;
        $invoice_tax = array();
        if (!empty($tax_name)) {
            foreach ($tax_name as $t_key => $v_tax_info) {
                array_push($invoice_tax, array('tax_name' => $v_tax_info, 'total_tax' => $total_tax[$t_key]));
            }
        }
        $all_tax_info = array();
        if (!empty($saved_items_id)) {
            foreach ($saved_items_id as $v_items_id) {
                $items_info = $this->requisition_model->check_by(array('saved_items_id' => $v_items_id), 'tbl_saved_items');

                $tax_info = json_decode($items_info->tax_rates_id);
                if (!empty($tax_info)) {
                    foreach ($tax_info as $v_tax) {
                        $all_tax = $this->db->where('tax_rates_id', $v_tax)->get('tbl_tax_rates')->row();
                        array_push($all_tax_info, array('tax_name' => $all_tax->tax_rate_name . '|' . $all_tax->tax_rate_percent, 'total_tax' => $items_info->unit_cost / 100 * $all_tax->tax_rate_percent));
                    }
                }
            }
        }
        if (!empty($invoice_tax) && is_array($invoice_tax) && !empty($all_tax_info)) {
            $all_tax_info = array_merge($all_tax_info, $invoice_tax);
        }

        $results = array();
        foreach ($all_tax_info as $value) {
            if (!isset($results[$value['tax_name']])) {
                $results[$value['tax_name']] = 0;
            }
            $results[$value['tax_name']] += $value['total_tax'];
        }
        if (!empty($results)) {
            foreach ($results as $key => $value) {
                $structured_results['tax_name'][] = $key;
                $structured_results['total_tax'][] = $value;
            }
            $invoice_data['tax'] = array_sum($structured_results['total_tax']);
            $invoice_data['total_tax'] = json_encode($structured_results);

            $this->requisition_model->_table_name = 'tbl_requisitions';
            $this->requisition_model->_primary_key = 'requisition_id';
            $this->requisition_model->save($invoice_data, $estimates_id);
        }
        return true;
    }

    public
    function add_item($id = NULL)
    {
        $data = $this->requisition_model->array_from_post(array('requisition_id', 'item_order'));
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $data['requisition_id']));
        $edited = can_action('14', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $quantity = $this->input->post('quantity', TRUE);
            $array_data = $this->requisition_model->array_from_post(array('item_name', 'item_desc', 'item_tax_rate', 'unit_cost'));
            if (!empty($quantity)) {
                foreach ($quantity as $key => $value) {
                    $data['quantity'] = $value;
                    $data['item_name'] = $array_data['item_name'][$key];
                    $data['item_desc'] = $array_data['item_desc'][$key];
                    $data['unit_cost'] = $array_data['unit_cost'][$key];
                    $data['item_tax_rate'] = $array_data['item_tax_rate'][$key];
                    $sub_total = $data['unit_cost'] * $data['quantity'];

                    $data['item_tax_total'] = ($data['item_tax_rate'] / 100) * $sub_total;
                    $data['total_cost'] = $sub_total + $data['item_tax_total'];

                    // get all client
                    $this->requisition_model->_table_name = 'tbl_requisition_items';
                    $this->requisition_model->_primary_key = 'requisition_items_id';
                    if (!empty($id)) {
                        $requisition_items_id = $id;
                        $this->requisition_model->save($data, $id);
                        $action = ('activity_estimates_items_updated');
                    } else {
                        $requisition_items_id = $this->requisition_model->save($data);
                        $action = 'activity_estimates_items_added';
                    }
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'estimates',
                        'module_field_id' => $requisition_items_id,
                        'activity' => $action,
                        'icon' => 'fa-shopping-cart',
                        'link' => 'admin/estimates/index/requisition_details/' . $data['requisition_id'],
                        'value1' => $data['item_name']
                    );
                    $this->requisition_model->_table_name = 'tbl_activities';
                    $this->requisition_model->_primary_key = 'activities_id';
                    $this->requisition_model->save($activity);
                }
            }
            // messages for user
            $type = "success";
            $message = lang('estimate_item_save');
            set_message($type, $message);
            redirect('admin/estimates/index/requisition_details/' . $data['requisition_id']);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/estimates');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function clone_estimate($estimates_id)
    {
        $edited = can_action('14', 'edited');
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $estimates_id));
        if (!empty($can_edit) && !empty($edited) && !empty($estimates_id)) {
            $data['estimate_info'] = $this->requisition_model->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
            // get all client
            $this->requisition_model->_table_name = 'tbl_client';
            $this->requisition_model->_order_by = 'client_id';
            $data['all_client'] = $this->requisition_model->get();

            $data['modal_subview'] = $this->load->view('admin/estimates/_modal_clone_estimate', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/estimates');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function cloned_estimate($id)
    {
        $edited = can_action('14', 'edited');
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $id));
        if (!empty($can_edit) && !empty($edited)) {
            if (config_item('increment_estimate_number') == 'FALSE') {
                $this->load->helper('string');
                $reference_no = config_item('estimate_prefix') . ' ' . random_string('nozero', 6);
            } else {
                $reference_no = config_item('estimate_prefix') . ' ' . $this->requisition_model->generate_estimate_number();
            }

            $invoice_info = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
            $data['estimate_date'] = date('Y-m-d', strtotime($this->input->post('estimate_date', TRUE)));
            if (empty($data['estimate_date'])) {
                $data['estimate_date'] = date('Y-m-d');
            }
            // save into invoice table
            $new_invoice = array(
                'reference_no' => $reference_no,
                'client_id' => $this->input->post('client_id', true),
                'project_id' => $invoice_info->project_id,
                'estimate_date' => date('Y-m-d', strtotime($this->input->post('estimate_date', TRUE))),
                'estimate_month' => date('Y-m', strtotime($this->input->post('estimate_date', TRUE))),
                'estimate_year' => date('Y', strtotime($this->input->post('estimate_date', TRUE))),
                'due_date' => date('Y-m-d', strtotime($this->input->post('due_date', TRUE))),
                'notes' => $invoice_info->notes,
                'tags' => $invoice_info->tags,
                'total_tax' => $invoice_info->total_tax,
                'tax' => $invoice_info->tax,
                'discount_type' => $invoice_info->discount_type,
                'discount_percent' => $invoice_info->discount_percent,
                'user_id' => $invoice_info->user_id,
                'adjustment' => $invoice_info->adjustment,
                'discount_total' => $invoice_info->discount_total,
                'show_quantity_as' => $invoice_info->show_quantity_as,
                'currency' => $invoice_info->currency,
                'status' => $invoice_info->status,
                'date_sent' => $invoice_info->date_sent,
                'date_saved' => $invoice_info->date_saved,
                'emailed' => $invoice_info->emailed,
                'show_client' => $invoice_info->show_client,
                'invoiced' => $invoice_info->invoiced,
                'invoices_id' => $invoice_info->invoices_id,
                'permission' => $invoice_info->permission,
            );
            $this->requisition_model->_table_name = "tbl_requisitions"; //table name
            $this->requisition_model->_primary_key = "estimates_id";
            $new_invoice_id = $this->requisition_model->save($new_invoice);

            $invoice_items = $this->db->where('requisition_id', $id)->get('tbl_requisition_items')->result();

            if (!empty($invoice_items)) {
                foreach ($invoice_items as $new_item) {
                    $items = array(
                        'requisition_id' => $new_invoice_id,
                        'item_name' => $new_item->item_name,
                        'item_desc' => $new_item->item_desc,
                        'unit_cost' => $new_item->unit_cost,
                        'quantity' => $new_item->quantity,
                        'item_tax_rate' => $new_item->item_tax_rate,
                        'item_tax_name' => $new_item->item_tax_name,
                        'item_tax_total' => $new_item->item_tax_total,
                        'total_cost' => $new_item->total_cost,
                        'unit' => $new_item->unit,
                        'order' => $new_item->order,
                        'date_saved' => $new_item->date_saved,
                    );
                    $this->requisition_model->_table_name = "tbl_requisition_items"; //table name
                    $this->requisition_model->_primary_key = "requisition_items_id";
                    $this->requisition_model->save($items);
                }
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'estimates',
                'module_field_id' => $new_invoice_id,
                'activity' => ('activity_clone_estimate'),
                'icon' => 'fa-shopping-cart',
                'link' => 'admin/estimates/index/requisition_details/' . $new_invoice_id,
                'value1' => ' from ' . $invoice_info->reference_no . ' to ' . $reference_no,
            );
            // Update into tbl_project
            $this->requisition_model->_table_name = "tbl_activities"; //table name
            $this->requisition_model->_primary_key = "activities_id";
            $this->requisition_model->save($activities);

            // messages for user
            $type = "success";
            $message = lang('estimate_created');
            set_message($type, $message);
            redirect('admin/estimates/index/requisition_details/' . $new_invoice_id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/estimates');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function change_status($action, $id)
    {
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $id));
        $edited = can_action('14', 'edited');
        if (!empty($can_edit) && !empty($edited)) {
            $where = array('requisition_id' => $id);
            if ($action == 'hide') {
                $data = array('show_client' => 'No');
            } elseif ($action == 'show') {
                $data = array('show_client' => 'Yes');
            } elseif ($action == 'sent') {
                $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()), 'status' => 'sent');
            } elseif (!empty($action)) {
                $data = array('status' => $action);
            } else {
                $data = array('show_client' => 'Yes');
            }
            $this->requisition_model->set_action($where, $data, 'tbl_requisitions');
            // messages for user
            $type = "success";
            $message = lang('requisition_status_changed', $action);
            set_message($type, $message);
            redirect('admin/requisition/index/requisition_details/' . $id);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/requisition');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function delete($action, $estimates_id, $item_id = NULL)
    {
        $can_delete = $this->requisition_model->can_action('tbl_requisitions', 'delete', array('requisition_id' => $estimates_id));
        $deleted = can_action('14', 'deleted');
        if (!empty($can_delete) && !empty($deleted)) {
            if ($action == 'delete_item') {
                $this->requisition_model->_table_name = 'tbl_requisition_items';
                $this->requisition_model->_primary_key = 'requisition_items_id';
                $this->requisition_model->delete($item_id);
            } elseif ($action == 'delete_estimates') {

                $this->requisition_model->_table_name = 'tbl_requisition_items';
                $this->requisition_model->delete_multiple(array('requisition_id' => $estimates_id));

                $this->requisition_model->_table_name = 'tbl_reminders';
                $this->requisition_model->delete_multiple(array('module' => 'estimate', 'module_id' => $estimates_id));

                $this->requisition_model->_table_name = 'tbl_pinaction';
                $this->requisition_model->delete_multiple(array('module_name' => 'estimates', 'module_id' => $estimates_id));

                $this->requisition_model->_table_name = 'tbl_requisitions';
                $this->requisition_model->_primary_key = 'requisition_id';
                $this->requisition_model->delete($estimates_id);
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'estimates',
                'module_field_id' => $estimates_id,
                'activity' => ('activity_' . $action),
                'icon' => 'fa-shopping-cart',
                'value1' => $action
            );

            $this->requisition_model->_table_name = 'tbl_activities';
            $this->requisition_model->_primary_key = 'activities_id';
            $this->requisition_model->save($activity);
            $type = 'success';
            if ($action == 'delete_item') {
                $text = lang('estimate_item_deleted');
                //                set_message($type, $text);
                //                redirect('admin/estimates/index/requisition_details/' . $estimates_id);
            } else {
                $text = lang('estimate_deleted');

                //                set_message($type, $text);
                //                redirect('admin/estimates');
            }
            echo json_encode(array("status" => $type, 'message' => $text));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('there_in_no_value')));
            exit();
            //            set_message('error', lang('there_in_no_value'));
            //            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public
    function send_estimates_email($estimates_id, $row = null)
    {
        if (!empty($row)) {
            $estimates_info = $this->requisition_model->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
            $client_info = $this->requisition_model->check_by(array('client_id' => $estimates_info->client_id), 'tbl_client');
            if (!empty($client_info)) {
                $client = $client_info->name;
                $currency = $this->requisition_model->client_currency_symbol($client_info->client_id);;
            } else {
                $client = '-';
                $currency = $this->requisition_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
            }

            $amount = $this->requisition_model->requisition_calculation('total', $estimates_info->requisition_id);
            $currency = $currency->code;
            $email_template = email_templates(array('email_group' => 'estimate_email'), $estimates_info->client_id);
            $message = $email_template->template_body;
            $ref = $estimates_info->reference_no;
            $subject = $email_template->subject;
        } else {
            $message = $this->input->post('message', TRUE);
            $ref = $this->input->post('ref', TRUE);
            $subject = $this->input->post('subject', TRUE);
            $client = $this->input->post('client_name', TRUE);
            $amount = $this->input->post('amount', true);
            $currency = $this->input->post('currency', TRUE);
        }
        $client_name = str_replace("{CLIENT}", $client, $message);
        $Ref = str_replace("{ESTIMATE_REF}", $ref, $client_name);
        $Amount = str_replace("{AMOUNT}", $amount, $Ref);
        $Currency = str_replace("{CURRENCY}", $currency, $Amount);
        $link = str_replace("{ESTIMATE_LINK}", base_url() . 'client/estimates/index/requisition_details/' . $estimates_id, $Currency);
        $message = str_replace("{SITE_NAME}", config_item('company_name'), $link);


        $this->send_email_estimates($estimates_id, $message, $subject); // Email estimates

        $data = array('status' => 'sent', 'emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->requisition_model->_table_name = 'tbl_requisitions';
        $this->requisition_model->_primary_key = 'requisition_id';
        $this->requisition_model->save($data, $estimates_id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'estimates',
            'module_field_id' => $estimates_id,
            'activity' => 'activity_estimates_sent',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/estimates/index/requisition_details/' . $estimates_id,
            'value1' => $ref
        );
        $this->requisition_model->_table_name = 'tbl_activities';
        $this->requisition_model->_primary_key = 'activities_id';
        $this->requisition_model->save($activity);

        $type = 'success';
        $text = lang('estimate_email_sent');
        set_message($type, $text);
        redirect('admin/estimates/index/requisition_details/' . $estimates_id);
    }

    function send_email_estimates($estimates_id, $message, $subject)
    {
        $estimates_info = $this->requisition_model->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
        $client_info = $this->requisition_model->check_by(array('client_id' => $estimates_info->client_id), 'tbl_client');

        $recipient = $client_info->email;

        $data['message'] = $message;

        $message = $this->load->view('email_template', $data, TRUE);
        $params = array(
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message
        );
        $params['resourceed_file'] = 'uploads/' . slug_it(lang('estimate') . '_' . $estimates_info->reference_no) . '.pdf';
        $params['resourcement_url'] = base_url() . 'uploads/' . (lang('estimate') . '_' . $estimates_info->reference_no) . '.pdf';

        $this->attach_pdf($estimates_id);
        $this->requisition_model->send_email($params);
        //Delete estimate in tmp folder
        if (is_file('uploads/' . slug_it(lang('estimate') . '_' . $estimates_info->reference_no) . '.pdf')) {
            unlink('uploads/' . slug_it(lang('estimate') . '_' . $estimates_info->reference_no) . '.pdf');
        }
        // send notification to client
        if (!empty($client_info->primary_contact)) {
            $notifyUser = array($client_info->primary_contact);
        } else {
            $user_info = $this->requisition_model->check_by(array('company' => $estimates_info->client_id), 'tbl_account_details');
            if (!empty($user_info)) {
                $notifyUser = array($user_info->user_id);
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'not_email_send_alert',
                        'link' => 'client/estimates/index/requisition_details/' . $estimates_id,
                        'value' => lang('estimate') . ' ' . $estimates_info->reference_no,
                    ));
                }
            }
            show_notification($notifyUser);
        }
    }

    public
    function attach_pdf($id)
    {
        $data['page'] = lang('estimates');
        $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');
        $data['title'] = lang('estimates'); //Page title
        $this->load->helper('dompdf');
        $html = $this->load->view('admin/estimates/estimates_pdf', $data, TRUE);
        $result = pdf_create($html, slug_it(lang('estimate') . '_' . $data['estimates_info']->reference_no), 1, null, true);
        return $result;
    }

    function estimate_email($estimates_id)
    {
        $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
        $estimates_info = $data['estimates_info'];
        $client_info = $this->requisition_model->check_by(array('client_id' => $data['estimates_info']->client_id), 'tbl_client');

        $recipient = $client_info->email;

        $message = $this->load->view('admin/estimates/estimates_pdf', $data, TRUE);

        $data['message'] = $message;

        $message = $this->load->view('email_template', $data, TRUE);
        $params = array(
            'recipient' => $recipient,
            'subject' => '[ ' . config_item('company_name') . ' ]' . ' New Estimate' . ' ' . $data['estimates_info']->reference_no,
            'message' => $message
        );
        $params['resourceed_file'] = '';

        $this->requisition_model->send_email($params);

        $data = array('emailed' => 'Yes', 'date_sent' => date("Y-m-d H:i:s", time()));

        $this->requisition_model->_table_name = 'tbl_requisitions';
        $this->requisition_model->_primary_key = 'requisition_id';
        $this->requisition_model->save($data, $estimates_id);

        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'estimates',
            'module_field_id' => $estimates_id,
            'activity' => 'activity_estimates_sent',
            'icon' => 'fa-shopping-cart',
            'link' => 'admin/estimates/index/requisition_details/' . $estimates_id,
            'value1' => $estimates_info->reference_no
        );
        $this->requisition_model->_table_name = 'tbl_activities';
        $this->requisition_model->_primary_key = 'activities_id';
        $this->requisition_model->save($activity);

        // send notification to client
        if (!empty($client_info->primary_contact)) {
            $notifyUser = array($client_info->primary_contact);
        } else {
            $user_info = $this->requisition_model->check_by(array('company' => $estimates_info->client_id), 'tbl_account_details');
            if (!empty($user_info)) {
                $notifyUser = array($user_info->user_id);
            }
        }
        if (!empty($notifyUser)) {
            foreach ($notifyUser as $v_user) {
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'icon' => 'shopping-cart',
                        'description' => 'not_email_send_alert',
                        'link' => 'client/estimates/index/requisition_details/' . $estimates_id,
                        'value' => lang('estimate') . ' ' . $estimates_info->reference_no,
                    ));
                }
            }
            show_notification($notifyUser);
        }


        $type = 'success';
        $text = lang('estimate_email_sent');
        set_message($type, $text);
        redirect('admin/estimates/index/requisition_details/' . $estimates_id);
    }

    public
    function convert_to_expense($id)
    {
        $data['title'] = lang('convert_to_invoice');
        $edited = can_action('14', 'edited');
        $can_edit = $this->requisition_model->can_action('tbl_requisitions', 'edit', array('requisition_id' => $id));
        if (!empty($can_edit) && !empty($edited)) {
            // get all client
            $this->requisition_model->_table_name = 'tbl_client';
            $this->requisition_model->_order_by = 'client_id';
            $data['all_client'] = $this->requisition_model->get();
            // get permission user
            $data['permission_user'] = $this->requisition_model->all_permission_user('14');

            $data['estimates_info'] = $this->requisition_model->check_by(array('requisition_id' => $id), 'tbl_requisitions');

            $data['modal_subview'] = $this->load->view('admin/requisition/convert_to_expance', $data, FALSE);
            $this->load->view('admin/_layout_modal_large', $data);
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/requisition');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public
    function converted($requisition_id)
    {
        try {
            $data1 = $this->requisition_model->array_from_post(array('reference_no', 'client_id', 'project_id','task_id',
                'discount_type','amount','account_id','name','due_date','notes','category_id','paid_by','payment_methods_id',
                'discount_percent', 'user_id', 'adjustment', 'discount_total', 'show_quantity_as'));
            if (!empty($requisition_id)) {
                $data['transaction_prefix'] = $data1['reference_no'];
                $data['name'] = $data1['name'];
                if ($data['name'] === '') {
                    set_message('warning', 'Name is required');
                    redirect('admin/requisition/index/requisition_details/' . $requisition_id);
                }
                $data['notes'] = $data1['notes'];
                $data['date'] = $data1['due_date'];
                $data['category_id'] = $data1['category_id'] ?? null;
                $data['paid_by'] = $data1['paid_by'] ?? null;
                $data['tags'] = $data1['tags'] ?? 'Converted';
                $data['payment_methods_id'] = $data1['payment_methods_id'] ?? null;
                $data['project_id'] = $data1['project_id'] ?? null;
                $data['task_id'] = $data1['task_id'] ?? null;
                $data['billable'] = $data1['billable'] ?? 'no';
                $data['client_visible'] = $data1['client_visible'] ?? null;
                $data['repeat_every'] = $data1['repeat_every'] ?? null;
                $data['done_cycles'] = $data1['done_cycles'] ?? null;
                $data['account_id'] = $data1['account_id'];
                $data['status'] = 'paid';

                $repeat_every_custom = $this->input->post('repeat_every_custom', true);
                $repeat_type_custom = $this->input->post('repeat_type_custom', true);
                // Recurring expense set to NO, Cancelled
                if ($data['repeat_every'] == '') {
                    $data['total_cycles'] = 0;
                    $data['done_cycles'] = 0;
                    $data['last_recurring_date'] = null;
                }
                if (isset($data['repeat_every']) && $data['repeat_every'] != '') {
                    $data['recurring'] = 'Yes';
                    if ($data['repeat_every'] == 'custom') {
                        $data['repeat_every'] = $repeat_every_custom;
                        $data['recurring_type'] = $repeat_type_custom;
                        $data['custom_recurring'] = 1;
                    } else {
                        $_temp = explode('_', $data['repeat_every']);
                        $data['recurring_type'] = $_temp[1];
                        $data['repeat_every'] = $_temp[0];
                        $data['custom_recurring'] = 0;
                    }
                } else {
                    $data['recurring'] = 'No';
                }
                $data['total_cycles'] = !isset($data['total_cycles']) || $data['recurring'] == 'No' ? 0 : $data['total_cycles'];

                $data['type'] = 'Expense';
                if (empty($data['client_visible'])) {
                    $data['client_visible'] = 'No';
                }
                if (empty($data['billable'])) {
                    $data['billable'] = 'yes';
                }


                $account_info = $this->transactions_model->check_by(array('account_id' => $data['account_id']), 'tbl_accounts');
                if (!empty($account_info)) {
                    $account_info = $account_info;
                } else {
                    $account_info = $this->db->get('tbl_accounts')->row();
                }

                $data['amount'] = $this->input->post('amount', TRUE);

                if (!empty($data['amount'])) {
                    $check_head = $this->db->where('department_head_id', $this->session->userdata('user_id'))->get('tbl_departments')->row();
                    $role = $this->session->userdata('user_type');
                    if ($role == 1 || !empty($check_head)) {
                        if (!empty($requisition_id)) {
//                            $data['account_id'] = $this->input->post('old_account_id', TRUE);
                        } else {
                            $data['amount'] = $this->input->post('amount', TRUE);
                            $data['debit'] = $this->input->post('amount', TRUE);

                            $ac_data['balance'] = $account_info->balance - $data['amount'];
                            $this->transactions_model->_table_name = "tbl_accounts"; //table name
                            $this->transactions_model->_primary_key = "account_id";
                            $this->transactions_model->save($ac_data, $account_info->account_id);
                        }

                        $account_info = $this->transactions_model->check_by(array('account_id' => $data['account_id']), 'tbl_accounts');
                        if (!empty($account_info)) {
                            $account_info = $account_info;
                        } else {
                            $account_info = $this->db->get('tbl_accounts')->row();
                        }
                        $data['total_balance'] = $account_info->balance;
                        $data['status'] = 'paid';
                    }

                    $upload_file = array();
                    $files = $this->input->post("files", true);
                    $target_path = getcwd() . "/uploads/";
                    //process the fiiles which has been uploaded by dropzone
                    if (!empty($files) && is_array($files)) {
                        foreach ($files as $key => $file) {
                            if (!empty($file)) {
                                $file_name = $this->input->post('file_name_' . $file, true);
                                $new_file_name = move_temp_file($file_name, $target_path);
                                $file_ext = explode(".", $new_file_name);
                                $is_image = check_image_extension($new_file_name);
                                $size = $this->input->post('file_size_' . $file, true) / 1000;
                                if ($new_file_name) {
                                    $up_data = array(
                                        "fileName" => $new_file_name,
                                        "path" => "uploads/" . $new_file_name,
                                        "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                                        "ext" => '.' . end($file_ext),
                                        "size" => round($size, 2),
                                        "is_image" => $is_image,
                                    );
                                    array_push($upload_file, $up_data);
                                }
                            }
                        }
                    }

                    $fileName = $this->input->post('fileName', true);
                    $path = $this->input->post('path', true);
                    $fullPath = $this->input->post('fullPath', true);
                    $size = $this->input->post('size', true);
                    $is_image = $this->input->post('is_image', true);

                    if (!empty($fileName)) {
                        foreach ($fileName as $key => $name) {
                            $old['fileName'] = $name;
                            $old['path'] = $path[$key];
                            $old['fullPath'] = $fullPath[$key];
                            $old['size'] = $size[$key];
                            $old['is_image'] = $is_image[$key];

                            array_push($upload_file, $old);
                        }
                    }
                    if (!empty($upload_file)) {
                        $data['attachement'] = json_encode($upload_file);
                    } else {
                        $data['attachement'] = null;
                    }

                    $permission = $this->input->post('permission', true);
                    if (!empty($permission)) {
                        if ($permission == 'everyone') {
                            $assigned = 'all';
                        } else {
                            $assigned_to = $this->transactions_model->array_from_post(array('assigned_to'));
                            if (!empty($assigned_to['assigned_to'])) {
                                foreach ($assigned_to['assigned_to'] as $assign_user) {
                                    $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                                }
                            }
                        }
                        if (!empty($assigned)) {
                            if ($assigned != 'all') {
                                $assigned = json_encode($assigned);
                            }
                        } else {
                            $assigned = 'all';
                        }
                        $data['permission'] = $assigned;
                    } else {
                        set_message('error', lang('assigned_to') . ' Field is required');
                        if (empty($_SERVER['HTTP_REFERER'])) {
                            redirect('admin/transactions/expense');
                        } else {
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }


                    $this->transactions_model->_table_name = "tbl_transactions"; //table name
                    $this->transactions_model->_primary_key = "transactions_id";


                    if (!empty($requisition_id)) {
                        $expense_id = $this->transactions_model->save($data);
                        $activity = ('activity_update_expense');
                        $msg = lang('update_a_expense');
                        $description = 'not_expense_update';
                        $not_value = lang('title') . ' ' . $data['name'] . ' ' . lang('date') . ' ' . strftime(config_item('date_format'), strtotime($data['date']));
                    } else {
                        $data['added_by'] = $this->session->userdata('user_id');
                        $id = $this->transactions_model->save($data);
                        // send sms
                        $this->send_transactions_sms('expense', $id);
                        $activity = ('activity_new_expense');
                        $msg = lang('save_new_expense');
                        $description = 'not_expense_saved';
                        $not_value = lang('account') . ': ' . $account_info->account_name . ' ' . lang('amount') . ': ' . display_money($data['amount']);
                        $expense_id = $id;
                    }
                    $e_data = array('status' => 'accepted', 'invoiced' => 'Yes', 'invoices_id' => $expense_id);

                    $this->requisition_model->_table_name = 'tbl_requisitions';
                    $this->requisition_model->_primary_key = 'requisition_id';
                    $this->requisition_model->save($e_data, $requisition_id);

                    save_custom_field(2, $requisition_id);
                    // save into activities
                    $activities = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'transactions',
                        'module_field_id' => $requisition_id,
                        'activity' => $activity,
                        'icon' => 'fa-building-o',
                        'link' => 'admin/transactions/view_details/' . $requisition_id,
                        'value1' => $account_info->account_name,
                        'value2' => $data['amount'],
                    );
                    // Update into tbl_project
                    $this->transactions_model->_table_name = "tbl_activities"; //table name
                    $this->transactions_model->_primary_key = "activities_id";
                    $this->transactions_model->save($activities);
                    $type = 'success';
                    if ($role == 3 && empty($check_head)) {
                        $this->expense_request_email($data, $requisition_id);
                    }
                    $designation_id = $this->session->userdata('designations_id');
                    if (!empty($designation_id)) {
                        $designation_info = $this->transactions_model->check_by(array('designations_id' => $this->session->userdata('designations_id')), 'tbl_designations');
                    }
                    if (!empty($designation_info)) {
                        $dept_head = $this->transactions_model->check_by(array('departments_id' => $designation_info->departments_id), 'tbl_departments');
                    }
                    // get departments head by departments id
                    $all_admin = $this->db->where('role_id', 1)->get('tbl_users')->result();
                    if (!empty($dept_head)) {
                        $head = $this->db->where('user_id', $dept_head->department_head_id)->get('tbl_users')->row();
                        array_push($all_admin, $head);
                    }

                    $notifyUser = array();
                    if (!empty($all_admin)) {
                        foreach ($all_admin as $v_user) {
                            if (!empty($v_user)) {
                                if ($v_user->user_id != $this->session->userdata('user_id')) {
                                    array_push($notifyUser, $v_user->user_id);
                                    add_notification(array(
                                        'to_user_id' => $v_user->user_id,
                                        'icon' => 'building-o',
                                        'description' => $description,
                                        'link' => 'admin/transactions/view_details/' . $requisition_id,
                                        'value' => $not_value,
                                    ));
                                }
                            }
                        }
                    }
                    if (!empty($notifyUser)) {
                        show_notification($notifyUser);
                    }

                } else {
                    $type = 'error';
                    $msg = 'please enter the amount';
                }
                $message = $msg;
                set_message($type, $message);
            }
            if (!empty($data['project_id']) && is_numeric($data['project_id'])) {
                redirect('admin/projects/project_details/' . $data['project_id'] . '/' . '10');
            } else {
                redirect('admin/transactions/expense');
            }

        } catch (Exception $e) {
            $type = 'error';
            $text = $e->getMessage();
            redirect('admin/requisition');
        }
    }

    function return_items($items_id)
    {
        $items_info = $this->db->where('items_id', $items_id)->get('tbl_items')->row();
        if (!empty($items_info->saved_items_id)) {
            $this->requisition_model->return_items($items_info->saved_items_id, $items_info->quantity);
        }
        return true;
    }

    function check_existing_qty($items_id, $qty)
    {
        $items_info = $this->db->where('items_id', $items_id)->get('tbl_items')->row();
        if (!empty($items_info)) {
            if ($items_info->quantity != $qty) {
                if ($qty > $items_info->quantity) {
                    $reduce_qty = $qty - $items_info->quantity;
                    if (!empty($items_info->saved_items_id)) {
                        $this->requisition_model->reduce_items($items_info->saved_items_id, $reduce_qty);
                    }
                }
                if ($qty < $items_info->quantity) {
                    $return_qty = $items_info->quantity - $qty;
                    if (!empty($items_info->saved_items_id)) {
                        $this->requisition_model->return_items($items_info->saved_items_id, $return_qty);
                    }
                }
            }
        }
        return true;
    }

    function get_recuring_frequency($invoices_id, $recur_data)
    {
        $recur_days = $this->get_calculate_recurring_days($recur_data['recuring_frequency']);
        $due_date = $this->requisition_model->get_table_field('tbl_invoices', array('invoices_id' => $invoices_id), 'due_date');

        $next_date = date("Y-m-d", strtotime($due_date . "+ " . $recur_days . " days"));

        if ($recur_data['recur_end_date'] == '') {
            $recur_end_date = '0000-00-00';
        } else {
            $recur_end_date = date('Y-m-d', strtotime($recur_data['recur_end_date']));
        }
        $update_invoice = array(
            'recurring' => 'Yes',
            'recuring_frequency' => $recur_days,
            'recur_frequency' => $recur_data['recuring_frequency'],
            'recur_start_date' => date('Y-m-d', strtotime($recur_data['recur_start_date'])),
            'recur_end_date' => $recur_end_date,
            'recur_next_date' => $next_date
        );
        $this->requisition_model->_table_name = 'tbl_invoices';
        $this->requisition_model->_primary_key = 'invoices_id';
        $this->requisition_model->save($update_invoice, $invoices_id);
        return TRUE;
    }

    function get_calculate_recurring_days($recuring_frequency)
    {
        switch ($recuring_frequency) {
            case '7D':
                return 7;
                break;
            case '1M':
                return 31;
                break;
            case '3M':
                return 90;
                break;
            case '6M':
                return 182;
                break;
            case '1Y':
                return 365;
                break;
        }
    }
}
