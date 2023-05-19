<?php

class Requisition_model extends MY_Model
{

    public $_table_name;
    public $_order_by;
    public $_primary_key;

    function requisition_calculation($requisition_value, $requisition_id)
    {
        switch ($requisition_value) {
            case 'requisition_cost':
                return $this->get_estimate_cost($requisition_id);
                break;
            case 'tax':
                return $this->get_estimate_tax_amount($requisition_id);
                break;
            case 'discount':
                return $this->get_estimate_discount($requisition_id);
                break;
            case 'requisition_amount':
                return $this->get_estimate_amount($requisition_id);
                break;
            case 'total':
                return $this->get_total_estimate_amount($requisition_id);
                break;
        }
    }

    function get_estimate_cost($estimates_id)
    {
        $this->db->select_sum('total_cost');
        $this->db->where('requisition_id', $estimates_id);
        $this->db->from('tbl_requisition_items');
        $query_result = $this->db->get();
        $cost = $query_result->row();
        if (!empty($cost->total_cost)) {
            $result = $cost->total_cost;
        } else {
            $result = '0';
        }
        return $result;
    }

    function get_estimate_tax_amount($estimates_id)
    {

        $invoice_info = $this->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
        if (!empty($invoice_info->total_tax)) {
            $tax_info = json_decode($invoice_info->total_tax);
        }
        $tax = 0;
        if (!empty($tax_info)) {
            $total_tax = $tax_info->total_tax;
            if (!empty($total_tax)) {
                foreach ($total_tax as $t_key => $v_tax_info) {
                    $tax += $v_tax_info;
                }
            }
        }
        return $tax;

    }

    function get_estimate_discount($estimates_id)
    {
        $invoice_info = $this->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
        return $invoice_info->discount_total;
    }

    function get_estimate_amount($requisition_id)
    {

        $tax = $this->get_estimate_tax_amount($requisition_id);
        $discount = $this->get_estimate_discount($requisition_id);
        $estimate_cost = $this->get_estimate_cost($requisition_id);
        return (($estimate_cost - $discount) + $tax);
    }

    function get_total_estimate_amount($estimates_id)
    {
        $invoice_info = $this->check_by(array('requisition_id' => $estimates_id), 'tbl_requisitions');
        $tax = $this->get_estimate_tax_amount($estimates_id);
        $discount = $this->get_estimate_discount($estimates_id);
        $estimate_cost = $this->get_estimate_cost($estimates_id);
        return (($estimate_cost - $discount) + $tax + $invoice_info->adjustment);
    }

    function ordered_items_by_id($id)
    {
        $result = $this->db->where('requisition_id', $id)->order_by('order', 'asc')->get('tbl_requisition_items')->result();
        return $result;
    }


    public function check_for_merge_invoice($client_id, $current_estimate)
    {

        $estimate_info = $this->db->where('client_id', $client_id)->get('tbl_requisitions')->result();

        foreach ($estimate_info as $v_estimate) {
            if ($v_estimate->requisition_id != $current_estimate) {
                if (strtolower($v_estimate->status) == 'pending' || $v_estimate->status == 'draft') {
                    $estimate[] = $v_estimate;
                }
            }
        }
        if (!empty($estimate)) {
            return $estimate;
        } else {
            return array();
        }
    }

    public function get_invoice_filter()
    {
        $this->db->select('invoice_year');
        $this->db->group_by('invoice_year');
        $result = $this->db->get('tbl_invoices')->result();

        $statuses = array(
            array(
                'id' => 1,
                'value' => 'draft',
                'name' => lang('draft'),
                'order' => 1,
            ), array(
                'id' => 1,
                'value' => 'cancelled',
                'name' => lang('cancelled'),
                'order' => 1,
            ), array(
                'id' => 1,
                'value' => 'expired',
                'name' => lang('expired'),
                'order' => 1,
            ),
            array(
                'id' => 4,
                'value' => 'declined',
                'name' => lang('declined'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'accepted',
                'name' => lang('accepted'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'last_month',
                'name' => lang('last_month'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'this_months',
                'name' => lang('this_months'),
                'order' => 4,
            )
        );
        if (!empty($result)) {
            foreach ($result as $v_year) {
                $test = array(
                    'id' => 1,
                    'value' => '_' . $v_year->invoice_year,
                    'name' => $v_year->invoice_year,
                    'order' => 1);
                if (!empty($test)) {
                    array_push($statuses, $test);
                }
            }
        }
        return $statuses;
    }

    public function get_estimate_filter()
    {
        $this->db->select('estimate_year');
        $this->db->group_by('estimate_year');
        $result = $this->db->get('tbl_requisitions')->result();

        $statuses = array(
            array(
                'id' => 1,
                'value' => 'draft',
                'name' => lang('draft'),
                'order' => 1,
            ), array(
                'id' => 1,
                'value' => 'cancelled',
                'name' => lang('cancelled'),
                'order' => 1,
            ), array(
                'id' => 1,
                'value' => 'expired',
                'name' => lang('expired'),
                'order' => 1,
            ),
            array(
                'id' => 4,
                'value' => 'declined',
                'name' => lang('declined'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'accepted',
                'name' => lang('accepted'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'last_month',
                'name' => lang('last_month'),
                'order' => 4,
            ),
            array(
                'id' => 4,
                'value' => 'this_months',
                'name' => lang('this_months'),
                'order' => 4,
            )
        );
        if (!empty($result)) {
            foreach ($result as $v_year) {
                $test = array(
                    'id' => 1,
                    'value' => '_' . $v_year->estimate_year,
                    'name' => $v_year->estimate_year,
                    'order' => 1);
                if (!empty($test)) {
                    array_push($statuses, $test);
                }
            }
        }
        return $statuses;
    }

    public function get_estimates($filterBy = null, $client_id = null)
    {
        if (!empty($client_id)) {
            $all_invoice = get_result('tbl_requisitions', array('client_id' => $client_id));
        } else {
            $all_invoice = $this->get_permission('tbl_requisitions');
        }
        if (empty($filterBy) || !empty($filterBy) && $filterBy == 'all') {
            return $all_invoice;
        } else {
            if (!empty($all_invoice)) {
                $all_invoice = array_reverse($all_invoice);
                foreach ($all_invoice as $v_invoices) {

                    if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                        if ($filterBy == 'last_month') {
                            $month = date('Y-m', strtotime('-1 months'));
                        } else {
                            $month = date('Y-m');
                        }
                        if (strtotime($v_invoices->estimate_month) == strtotime($month)) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'expired') {
                        if (strtotime($v_invoices->due_date) < strtotime(date('Y-m-d')) && $v_invoices->status == ('pending') || strtotime($v_invoices->due_date) < strtotime(date('Y-m-d')) && $v_invoices->status == ('draft')) {
                            $invoice[] = $v_invoices;
                        }

                    } else if ($filterBy == $v_invoices->status) {
                        $invoice[] = $v_invoices;
                    } else if (strstr($filterBy, '_')) {
                        $year = str_replace('_', '', $filterBy);
                        if (strtotime($v_invoices->estimate_year) == strtotime($year)) {
                            $invoice[] = $v_invoices;
                        }
                    }

                }
            }
        }
        if (!empty($invoice)) {
            return $invoice;
        } else {
            return array();
        }

    }

    public function get_client_estimates($filterBy = null, $client_id = null)
    {
        if (!empty($client_id)) {
            $all_invoice = get_result('tbl_requisitions', array('client_id' => $client_id, 'status !=' => 'draft'));
        } else {
            $all_invoice = $this->get_permission('tbl_requisitions');
        }
        if (empty($filterBy) || !empty($filterBy) && $filterBy == 'all') {
            return $all_invoice;
        } else {
            if (!empty($all_invoice)) {
                $all_invoice = array_reverse($all_invoice);
                foreach ($all_invoice as $v_invoices) {

                    if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                        if ($filterBy == 'last_month') {
                            $month = date('Y-m', strtotime('-1 months'));
                        } else {
                            $month = date('Y-m');
                        }
                        if (strtotime($v_invoices->estimate_month) == strtotime($month)) {
                            $invoice[] = $v_invoices;
                        }
                    } else if ($filterBy == 'expired') {
                        if (strtotime($v_invoices->due_date) < strtotime(date('Y-m-d')) && $v_invoices->status == ('pending') || strtotime($v_invoices->due_date) < strtotime(date('Y-m-d')) && $v_invoices->status == ('draft')) {
                            $invoice[] = $v_invoices;
                        }

                    } else if ($filterBy == $v_invoices->status) {
                        $invoice[] = $v_invoices;
                    } else if (strstr($filterBy, '_')) {
                        $year = str_replace('_', '', $filterBy);
                        if (strtotime($v_invoices->estimate_year) == strtotime($year)) {
                            $invoice[] = $v_invoices;
                        }
                    }

                }
            }
        }
        if (!empty($invoice)) {
            return $invoice;
        } else {
            return array();
        }

    }

    public function get_estimate_report($filterBy = null, $range = null)
    {
        if (!empty($filterBy) && is_numeric($filterBy)) {
            $estimates = $this->db->where('client_id', $filterBy)->get('tbl_requisitions')->result();
        } else {
            $all_estimates = $this->get_permission('tbl_requisitions');
        }
        if (empty($filterBy) || !empty($filterBy) && $filterBy == 'all') {
            $estimates = $all_estimates;
        } else {
            if (!empty($all_estimates)) {
                $all_estimates = array_reverse($all_estimates);
                foreach ($all_estimates as $v_estimate) {
                    if ($filterBy == 'last_month' || $filterBy == 'this_months') {
                        if ($filterBy == 'last_month') {
                            $month = date('Y-m', strtotime('-1 months'));
                        } else {
                            $month = date('Y-m');
                        }
                        if (strtotime($v_estimate->estimate_month) == strtotime($month)) {
                            $estimates[] = $v_estimate;
                        }
                    } else if ($filterBy == 'expired') {
                        if (strtotime($v_estimate->due_date) < strtotime(date('Y-m-d')) && $v_estimate->status == ('pending') || strtotime($v_estimate->due_date) < strtotime(date('Y-m-d')) && $v_estimate->status == ('draft')) {
                            $estimates[] = $v_estimate;
                        }
                    } else if ($filterBy == $v_estimate->status) {
                        $estimates[] = $v_estimate;
                    } else if (strstr($filterBy, '_')) {
                        $year = str_replace('_', '', $filterBy);
                        if (strtotime($v_estimate->estimate_year) == strtotime($year)) {
                            $estimates[] = $v_estimate;
                        }
                    }

                }
            }
        }
        if (!empty($estimates)) {
            $estimate_info = array();
            if (!empty($range[0])) {
                foreach ($estimates as $v_estimate) {
                    if ($v_estimate->estimate_date >= $range[0] && $v_estimate->estimate_date <= $range[1]) {
                        array_push($estimate_info, $v_estimate);
                    }
                }
                return $estimate_info;
            } else {
                return $estimates;
            }
        } else {
            return array();
        }

    }

}
