<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Branches extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('transactions_model');

    }

    public function index($id = null)
    {
        $data['title'] = lang('all_contractors');
        // get permission user by menu id
        $data['permission_user'] = $this->transactions_model->all_permission_user('167');

        $data['dropzone'] = true;

        $data['all_expense_info'] = array();
        $data['active'] = $id ? 2 : 1;
        $data['branch_info'] = $this->db->where('id', $id)->get('tbl_branches')->row();

        $data['subview'] = $this->load->view('admin/Branches/branches', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function branchesList()
    {
        if ($this->input->is_ajax_request()) {
            $branches = $this->db
                ->get('tbl_branches')
                ->result();
            $data = [];

            foreach ($branches as $_key => $branch) {
                $sub_array = array();
                $action = null;
                $sub_array[] =$_key+1;
                $sub_array[] = $branch->name;
                $sub_array[] = $branch->address;
                $sub_array[] = '';
//                $action .= '<a class="btn btn-info btn-xs"  href="' . base_url() . 'admin/branches/delete_branch/' . $branch->id . '"><span class="fa fa-trash-o"></span></a>' . ' ';
                $action .= ajax_anchor(base_url("admin/branches/delete_branch/$branch->id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                $action .= '<a class="btn btn-info btn-xs"  href="' . base_url() . 'admin/branches/index/' . $branch->id . '"><span class="fa fa-edit"></span></a>' . ' ';
                $sub_array[] = $action;
                $data[] = $sub_array;

            }
            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function getProjectIdByTaskId($task_id)
    {
        $task = $this->db->where('task_id', $task_id)
            ->get('tbl_task')
            ->row();
        $task_project_id = $task->project_id ?? $this->getProjectIdByTaskId($task->sub_task_id);
        return $task_project_id;

    }


    public function save_branche($id = NULL)
    {
        $data['name'] = $this->input->post('name', TRUE);
        $data['address'] = $this->input->post('address', TRUE);

        if ($id) {
            $this->db->where('id', $id)->update('tbl_branches', $data);
        } else {
            $this->db->insert('tbl_branches', $data);
        }

        return redirect('admin/branches');
    }

    public function delete_branch($id)
    {
        $this->db->where('id', $id)->delete('tbl_branches');
        return redirect('admin/branches');
    }
}
