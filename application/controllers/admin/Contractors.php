<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contractors extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('transactions_model');

    }

    public function index()
    {
        $data['title'] = lang('all_contractors');
        // get permission user by menu id
        $data['permission_user'] = $this->transactions_model->all_permission_user('166');


        if (!empty($id)) {
            $data['active'] = 2;
            if (is_numeric($id)) {
                $expense_info = $this->transactions_model->check_by(array('transactions_id' => $id), 'tbl_transactions');
                $can_edit = $this->transactions_model->can_action('tbl_transactions', 'edit', array('transactions_id' => $id));
                if (!empty($expense_info) && !empty($can_edit)) {
                    $data['expense_info'] = $expense_info;
                }
            } else {
                $data['active'] = 1;
                if ($id == 'project_expense') {
                    $data['active'] = 2;
                    $project_id = $this->uri->segment(5);
                    $project_info = get_row('tbl_project', array('project_id' => $project_id));
                    if ($project_info->permission == 'all') {
                        $data['permission_user'] = $this->transactions_model->allowed_user('57');
                    } else {
                        $data['permission_user'] = $this->transactions_model->permitted_allowed_user($project_info->permission);
                    }
                }
            }
        } else {
            $data['active'] = 1;
        }
        $data['dropzone'] = true;
        $all_expense_info = $this->transactions_model->get_permission('tbl_transactions');
        $data['all_expense_info'] = array();
        $id = $this->uri->segment(5);
        if (!empty($id)) {
            $data['search_by'] = $this->uri->segment(4);
            if ($data['search_by'] == 'category') {
                if (!empty($all_expense_info)) {
                    foreach ($all_expense_info as $v_expense) {
                        if ($v_expense->category_id == $id) {
                            array_push($data['all_expense_info'], $v_expense);
                        }

                    }
                }
            }
        } else {
            $data['all_expense_info'] = $this->transactions_model->get_permission('tbl_transactions');
        }
        $data['subview'] = $this->load->view('admin/contractors/contractors', $data, TRUE);
        $this->load->view('admin/_layout_main', $data); //page load
    }

    public function contractorList()
    {
        if ($this->input->is_ajax_request()) {
            $contactors = $this->db->select('tbl_task.*,customer_group')
                ->join('tbl_customer_group', 'tbl_customer_group.customer_group_id = tbl_task.contactor_id', 'left')
                ->group_by('tbl_task.task_id')
                ->where("contactor_id >", 0)
                ->get('tbl_task')
                ->result();

            $data = [];
            foreach ($contactors as $_key => $task) {
                $sub_array = array();
                $action = null;
                $expense = $this->db->select('tbl_transactions.name,tbl_transactions.amount,tbl_transactions.task_id')
                    ->where('task_id', $task->task_id)
                    ->get('tbl_transactions')
                    ->row();
                $task_project_id = $task->project_id ?? $this->getProjectIdByTaskId($task->sub_task_id);
                $project = $this->db->where('project_id', $task_project_id)
                    ->get('tbl_project')
                    ->row();
                $project_name = $project->project_name ?? '-';
                $sub_array[] = $_key + 1;

                $sub_array[]  = '<a class="text-info" href="' . base_url() . 'admin/projects/project_details/' . $task_project_id . '">' . $project_name . '</a>';

                $transaction_prefix =  $task->customer_group;
                $sub_array[] = $transaction_prefix;

                $name = '<a class="text-info" href="' . base_url() . 'admin/tasks/view_task_details/' . $task->task_id . '">' . $task->task_name . '</a>';
                $sub_array[] = $name;

                $sub_array[] = display_money($task->budget, default_currency());
                $sub_array[] = '<span class="tags">' . display_money($expense->amount, default_currency()) . '</span>';
                $sub_array[] = display_money($task->budget - $expense->amount, default_currency());

                $action .= '<a class="btn btn-info btn-xs"  href="#"><span class="fa fa-list-alt"></span></a>' . ' ';

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
        return $task->project_id;
//        return $task->project_id ?? $this->getProjectIdByTaskId($task->sub_task_id);

    }

}