<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Items extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('items_model');
    }

    public function items_list($id = NULL, $opt = null)
    {
        $data['title'] = lang('all_items');
        if (!empty($id)) {
            if (is_numeric($id)) {
                $data['active'] = 2;
                $data['items_info'] = $this->items_model->check_by(array('saved_items_id' => $id), 'tbl_saved_items');
            } else {
                $data['active'] = 3;
                $data['group_info'] = $this->items_model->check_by(array('customer_group_id' => $opt), 'tbl_customer_group');
            }
        } else {
            $data['active'] = 1;
        }
        $data['dropzone'] = 1;
        $data['subview'] = $this->load->view('admin/items/items_list', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function itemsList($group_id = null, $type = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_saved_items';
            $this->datatables->join_table = array('tbl_customer_group', 'tbl_manufacturer','tbl_project');
            $this->datatables->join_where = array('tbl_customer_group.customer_group_id=tbl_saved_items.customer_group_id', 'tbl_manufacturer.manufacturer_id=tbl_saved_items.manufacturer_id','tbl_project.project_id=tbl_saved_items.project_id');

            $custom_field = custom_form_table_search(18);
            $action_array = array('saved_items_id');
            $main_column = array('project_id','item_name', 'code', 'hsn_code', 'quantity', 'unit_cost', 'unit_type', 'customer_group', 'manufacturer');
            $result = array_merge($main_column, $custom_field, $action_array);
            $this->datatables->column_order = $result;
            $this->datatables->column_search = $result;

            $this->datatables->order = array('saved_items_id' => 'desc');

            // get all invoice
            if (!empty($type) && $type == 'by_group') {
                $where = array('tbl_saved_items.customer_group_id' => $group_id);
            } else if (!empty($type) && $type == 'by_manufacturer') {
                $where = array('tbl_saved_items.manufacturer_id' => $group_id);
            } else {
                $where = null;
            }
            $fetch_data = make_datatables($where);

            $data = array();

            $edited = can_action('39', 'edited');
            $deleted = can_action('39', 'deleted');
            foreach ($fetch_data as $_key => $v_items) {
                $action = null;
                $item_name = !empty($v_items->item_name) ? $v_items->item_name : $v_items->item_name;

                $sub_array = array();
                if (!empty($deleted)) {
                    $sub_array[] = '<div class="checkbox c-checkbox" ><label class="needsclick"> <input value="' . $v_items->saved_items_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                }
                $sub_array[] = '<a data-toggle="modal" data-target="#myModal_extra_lg" href="' . base_url('admin/items/items_details/' . $v_items->saved_items_id) . '"><strong class="block">' . $item_name . '</strong></a>' . ' ' . lang('code') . ': <span class="tags">' . (!empty($v_items->code) ? $v_items->code : '-') . '</span><br/>' . lang('manufacturer') . ': <span class="tags">' . (!empty($v_items->manufacturer) ? $v_items->manufacturer : '-') . '</span><br/>' . lang('availability') . ': ' . ($v_items->quantity);

                $invoice_view = config_item('invoice_view');
                if (!empty($invoice_view) && $invoice_view == '2') {
                    $sub_array[] = $v_items->hsn_code;
                }
                if (!empty(admin())) {
                    $sub_array[] = display_money($v_items->cost_price, default_currency());
                }
                $sub_array[] = display_money($v_items->unit_cost, default_currency());
                $sub_array[] = $v_items->unit_type;
                $sub_array[] = $v_items->project_name;
                if (!is_numeric($v_items->tax_rates_id)) {
                    $tax_rates = json_decode($v_items->tax_rates_id);
                } else {
                    $tax_rates = null;
                }
                $rates = null;
                if (!empty($tax_rates)) {
                    if (is_array($tax_rates)) {
                        foreach ($tax_rates as $key => $tax_id) {
                            $taxes_info = $this->db->where('tax_rates_id', $tax_id)->get('tbl_tax_rates')->row();
                            if (!empty($taxes_info)) {
                                $rates .= $key + 1 . '. ' . $taxes_info->tax_rate_name . '&nbsp;&nbsp; (' . $taxes_info->tax_rate_percent . '% ) <br>';
                            }
                        }
                    } else {
                        $rates = $this->db->where('tax_rates_id', $tax_rates)->get('tbl_tax_rates')->row()->tax_rate_name;
                    }
                }
                $sub_array[] = $rates;

                $sub_array[] = (!empty($v_items->customer_group) ? '<span class="tags">' . $v_items->customer_group . '</span>' : ' ');
                $custom_form_table = custom_form_table(18, $v_items->saved_items_id);

                if (!empty($custom_form_table)) {
                    foreach ($custom_form_table as $c_label => $v_fields) {
                        $sub_array[] = $v_fields;
                    }
                }

                if (!empty($edited)) {
                    $action .= btn_edit('admin/items/items_list/' . $v_items->saved_items_id) . ' ';
                }
                if (!empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/items/delete_items/$v_items->saved_items_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $action .= '<a class="btn btn-inverse btn-xs" data-toggle="tooltip" data-placement="top" title="' . lang('print_barcode') . '"  target="_blank" href="' . base_url('admin/items/single_barcode/' . $v_items->saved_items_id) . '"><i class="fa fa-barcode" ></i></a>' . ' ';

                $sub_array[] = $action;
                $data[] = $sub_array;
            }

            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function saved_items($id = NULL)
    {
        $this->items_model->_table_name = 'tbl_saved_items';
        $this->items_model->_primary_key = 'saved_items_id';

        $data = $this->items_model->array_from_post(array('project_id','item_name', 'manufacturer_id', 'code', 'barcode_symbology', 'item_desc', 'hsn_code', 'cost_price', 'unit_cost', 'unit_type', 'customer_group_id', 'quantity'));
        $tax_rates = $this->input->post('tax_rates_id', true);
        $total_tax = 0;
        if (!empty($tax_rates)) {
            foreach ($tax_rates as $tax_id) {
                $tax_info = $this->db->where('tax_rates_id', $tax_id)->get('tbl_tax_rates')->row();
                $total_tax += $tax_info->tax_rate_percent;
            }
        }
        if (!empty($tax_rates)) {
            $data['tax_rates_id'] = json_encode($tax_rates);
        } else {
            $data['tax_rates_id'] = '-';
        }


        // update root category
        $where = array('item_name' => $data['item_name']);
        // duplicate value check in DB
        if (!empty($id)) { // if id exist in db update data
            $saved_items_id = array('saved_items_id !=' => $id);
        } else { // if id is not exist then set id as null
            $saved_items_id = null;
        }
        // check whether this input data already exist or not
        $check_items = $this->items_model->check_update('tbl_saved_items', $where, $saved_items_id);
        if (!empty($check_items)) { // if input data already exist show error alert
            // massage for user
            $type = 'error';
            $msg = "<strong style='color:#000'>" . $data['item_name'] . '</strong>  ' . lang('already_exist');
        } else { // save and update query          

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
                        if (!empty($new_file_name)) {
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
                $data['upload_file'] = json_encode($upload_file);
            } else {
                $data['upload_file'] = null;
            }

            $sub_total = $data['unit_cost'] * $data['quantity'];
            $data['item_tax_total'] = ($total_tax / 100) * $sub_total;
            $data['total_cost'] = $sub_total + $data['item_tax_total'];
            $return_id = $this->items_model->save($data, $id);

            save_custom_field(18, $id);

            if (!empty($id)) {
                $id = $id;
                $action = 'activity_update_items';
                $msg = lang('update_items');
            } else {
                $id = $return_id;
                $action = 'activity_save_items';
                $msg = lang('save_items');
            }
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'items',
                'module_field_id' => $id,
                'activity' => $action,
                'icon' => 'fa-circle-o',
                'value1' => $data['item_name']
            );
            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);
            // messages for user
            $type = "success";
        }
        $message = $msg;
        set_message($type, $message);
        redirect('admin/items/items_list');
    }

    public function bulk_delete()
    {
        $selected_id = $this->input->post('ids', true);
        if (!empty($selected_id)) {
            foreach ($selected_id as $id) {
                $result[] = $this->delete_items($id, true);
            }
            echo json_encode($result);
            exit();
        } else {
            $type = "error";
            $message = lang('you_need_select_to_delete');
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        }
    }

    public function delete_items($id, $bulk = null)
    {
        $deleted = can_action('39', 'deleted');
        if (!empty($deleted)) {
            $items_info = $this->items_model->check_by(array('saved_items_id' => $id), 'tbl_saved_items');
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'items',
                'module_field_id' => $id,
                'activity' => 'activity_items_deleted',
                'icon' => 'fa-circle-o',
                'value1' => $items_info->item_name
            );
            $this->items_model->_table_name = 'tbl_activities';
            $this->items_model->_primary_key = 'activities_id';
            $this->items_model->save($activity);

            $this->items_model->_table_name = 'tbl_saved_items';
            $this->items_model->_primary_key = 'saved_items_id';
            $this->items_model->delete($id);
            $type = 'success';
            $message = lang('items_deleted');
        } else {
            $type = "error";
            $message = lang('no_permission');
        }
        if (!empty($bulk)) {
            return (array("status" => $type, 'message' => $message));
        }
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function items_group()
    {
        $data['title'] = lang('lead_source');
        $data['subview'] = $this->load->view('admin/items/items_group', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public function update_group($id = null)
    {
        $this->items_model->_table_name = 'tbl_customer_group';
        $this->items_model->_primary_key = 'customer_group_id';

        $cate_data['customer_group'] = $this->input->post('customer_group', TRUE);
        $cate_data['description'] = $this->input->post('description', TRUE);
        $cate_data['type'] = 'items';
        $id = $this->items_model->save($cate_data, $id);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'settings',
            'module_field_id' => $id,
            'activity' => ('customer_group_added'),
            'value1' => $cate_data['customer_group']
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);

        // messages for user
        $type = "success";
        $msg = lang('customer_group_added');
        if (!empty($id)) {
            $result = array(
                'id' => $id,
                'group' => $cate_data['customer_group'],
                'status' => $type,
                'message' => $msg,
            );
        } else {
            $result = array(
                'status' => $type,
                'message' => $msg,
            );
        }
        echo json_encode($result);
        exit();
    }

    public function saved_group($id = null)
    {
        $this->items_model->_table_name = 'tbl_customer_group';
        $this->items_model->_primary_key = 'customer_group_id';

        $cate_data['customer_group'] = $this->input->post('customer_group', TRUE);
        $cate_data['description'] = $this->input->post('description', TRUE);
        $cate_data['type'] = 'items';

        $id = $this->items_model->save($cate_data, $id);

        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'settings',
            'module_field_id' => $id,
            'activity' => ('customer_group_added'),
            'value1' => $cate_data['customer_group']
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);

        // messages for user
        $type = "success";
        $msg = lang('customer_group_added');
        $message = $msg;
        set_message($type, $message);
        redirect('admin/items/items_list/group');
    }

    public function delete_group($id)
    {
        $customer_group = $this->items_model->check_by(array('customer_group_id' => $id), 'tbl_customer_group');
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'settings',
            'module_field_id' => $id,
            'activity' => ('activity_delete_a_customer_group'),
            'value1' => $customer_group->customer_group,
        );
        $this->items_model->_table_name = 'tbl_activities';
        $this->items_model->_primary_key = 'activities_id';
        $this->items_model->save($activity);

        $this->items_model->_table_name = 'tbl_customer_group';
        $this->items_model->_primary_key = 'customer_group_id';
        $this->items_model->delete($id);
        // messages for user
        $type = "success";
        $message = lang('category_deleted');
        echo json_encode(array("status" => $type, 'message' => $message));
        exit();
    }

    public function import()
    {
        $header = lang('items');
        $data['title'] = lang('import') . ' ' . $header;
        $data['permission_user'] = $this->items_model->all_permission_user('30');
        $data['type'] = 'items';
        $data['subview'] = $this->load->view('admin/items/import', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }


    public function save_imported()
    {
        $this->load->library('excel');
        ob_start();
        $file = $_FILES["upload_file"]["tmp_name"];
        if (!empty($file)) {
            $valid = false;
            $types = array('Excel2007', 'Excel5', 'CSV');
            foreach ($types as $type) {
                $reader = PHPExcel_IOFactory::createReader($type);
                if ($reader->canRead($file)) {
                    $valid = true;
                }
            }
            if (!empty($valid)) {
                try {
                    $objPHPExcel = PHPExcel_IOFactory::load($file);
                } catch (Exception $e) {
                    die("Error loading file :" . $e->getMessage());
                }
                //All data from excel
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

                $all_data = array();
                for ($x = 2; $x <= count($sheetData); $x++) {
                    $data['item_name'] = trim($sheetData[$x]["A"]);
                    $data['item_desc'] = trim($sheetData[$x]["B"]);
                    $data['quantity'] = trim($sheetData[$x]["C"]);
                    $unit_cost = str_replace(',', '.', trim($sheetData[$x]["D"]));
                    $data['unit_cost'] = preg_replace("/[^0-9,.]/", "", $unit_cost);
                    $data['unit_type'] = trim($sheetData[$x]["E"]);
                    $taxtname = $this->remove_numbers(trim($sheetData[$x]["F"]));
                    $taxtname = explode("_", $taxtname);
                    $taxtname = array_map('trim', $taxtname);
                    $taxtname = array_filter($taxtname);
                    $tax_id = array();
                    if (is_array($taxtname)) {
                        if (!empty($taxtname)) {
                            foreach ($taxtname as $val) {
                                array_push($tax_id, $this->db->where('tax_rate_name', $val)->get('tbl_tax_rates')->row('tax_rates_id'));
                            }
                        }
                    } else {
                        $tax_id = $this->db->where('tax_rate_name', $taxtname)->get('tbl_tax_rates')->row('tax_rates_id');
                    }
                    if (!empty($tax_id)) {
                        $data['tax_rates_id'] = json_encode($tax_id);
                    } else {
                        $data['tax_rates_id'] = '-';
                    }
                    $category_code = trim($sheetData[$x]["G"]);
                    $category_code_info = $this->items_model->check_by(array('type' => 'items', 'customer_group' => $category_code), 'tbl_customer_group');
                    if (!empty($category_code_info)) {
                        $data['customer_group_id'] = $category_code_info->customer_group_id;
                    } else {
                        $category['type'] = 'items';
                        $category['customer_group'] = $category_code;
                        $this->items_model->_table_name = "tbl_customer_group"; //table name
                        $this->items_model->_primary_key = "customer_group_id";
                        $data['customer_group_id'] = $this->items_model->save($category);
                    }
                    $all_data[] = $data;
                }
                if (!empty($all_data)) {
                    $this->db->insert_batch('tbl_saved_items', $all_data);
                }
                $type = 'success';
                $message = lang('save_new_items');
                $redirect = 'items';
            } else {
                $type = 'error';
                $message = "Sorry your uploaded file type not allowed ! please upload XLS/CSV File ";
            }
        } else {
            $type = 'error';
            $message = "You did not Select File! please upload XLS/CSV File ";
        }
        set_message($type, $message);
        redirect('admin/items/items_list');
    }

    function remove_numbers($string)
    {
        $string = preg_replace("/\([^)]+\)/", "", $string);
        $num = array('0.', '1.', '2.', '3.', '4.', '5.', '6.', '7.', '8.', '9.');
        return str_replace($num, '_', $string);
    }

    public function items_details($id = NULL)
    {
        $data['title'] = lang('items_details');
        if (!empty($id)) {
            $data['items_info'] = $this->items_model->check_by(array('saved_items_id' => $id), 'tbl_saved_items');
            $data['barcode'] = $this->product_barcode($data['items_info']->code, $data['items_info']->barcode_symbology, 60);
        }
        $data['subview'] = $this->load->view('admin/items/items_details', $data, false);
        // $this->load->view('admin/_layout_main', $data); //page load
        $this->load->view('admin/_layout_modal_lg', $data); //page load
    }

    public function single_barcode($id)
    {
        $currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
        $product = get_row('tbl_saved_items', array('saved_items_id' => $id));
        $total = $product->quantity - 1;
        $html = "";
        $html .= '<table class="table table-bordered table-centered mb0">
        <tbody><tr>';
        if ($product->quantity > 0) {
            for ($r = 0; $r <= $total; $r++) {
                if ($r % 4 == 0) {
                    $html .= '</tr><tr>';
                }
                //                $rw = (bool)($r & 1);
                //                    $html .= $rw ? '</tr><tr>' : '';
                $html .= '<td class="text-center"><h4 class="m-sm">' . config_item('website_name') . '</h4><strong>' . $product->item_name . '</strong><br>' . $this->product_barcode($product->code, $product->barcode_symbology, 60) . ' <br><span class="price">' . lang('price') . ': ' . display_money($product->unit_cost, $currency->symbol) . '</span></td>';
            }
        } else {
            for ($r = 0; $r <= 9; $r++) {
                if ($r != 1) {
                    $rw = (bool)($r & 1);
                    $html .= $rw ? '</tr><tr>' : '';
                }
                $html .= '<td><h4>' . config_item('website_name') . '</h4><strong class="text-center">' . $product->item_name . '</strong><br>' . $this->product_barcode($product->code, $product->barcode_symbology, 60) . ' <br><span class="price">' . lang('price') . ': ' . display_money($product->unit_cost, $currency->symbol) . '</span></td>';
            }
        }
        $html .= '</tr></tbody>
        </table>';
        $data['html'] = $html;
        $data['title'] = lang("print_barcodes") . ' (' . $product->item_name . ')';
        $data['subview'] = $this->load->view('admin/items/single_barcode', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    function product_barcode($product_code = NULL, $bcs = 'code128', $height = 60)
    {
        return "<img src='" . site_url('admin/items/gen_barcode/' . $product_code . '/' . $bcs . '/' . $height) . "' alt='{$product_code}' class='bcimg' />";
    }

    function gen_barcode($product_code = NULL, $bcs = 'code128', $height = 60, $text = 1)
    {
        $drawText = ($text != 1) ? FALSE : TRUE;
        $this->load->library('zend');
        $this->zend->load('Zend/Barcode');
        $barcodeOptions = array('text' => $product_code, 'barHeight' => $height, 'drawText' => $drawText, 'factor' => 1);
        $rendererOptions = array('imageType' => 'png', 'horizontalPosition' => 'center', 'verticalPosition' => 'middle');
        echo Zend_Barcode::render($bcs, 'image', $barcodeOptions, $rendererOptions);
        exit();
    }
}
