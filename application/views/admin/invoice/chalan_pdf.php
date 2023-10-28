<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= lang('invoice') ?></title>
    <?php
    $direction = $this->session->userdata('direction');
    if (!empty($direction) && $direction == 'rtl') {
        $RTL = 'on';
    } else {
        $RTL = config_item('RTL');
    }
    ?>
    <style type="text/css">
        /*@page {*/
        /*    margin: 1in 0in 0in 0in;*/
        /*}*/
        @font-face {
            font-family: "Source Sans Pro", sans-serif;
        }

        .h4 {
            font-size: 14px;
        }

        .h3 {
            font-size: 15px;
        }

        h2 {
            font-size: 19px;
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        body {
            color: #555555;
            background: #ffffff;
            font-size: 12px;
            font-family: "Source Sans Pro", sans-serif;
            width: 100%;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        header {
            padding: 10px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #aaaaaa;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #logo {
        <?php if(!empty($RTL)){?> text-align: right !important;
            padding-right: 55px;
        <?php }?>
        }

        #company {
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        #details {
            margin-bottom: 10px;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #client {
            padding-left: 6px;
            /*border-left: 6px solid #0087C3;*/
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1em;
            font-weight: normal;
            margin: 0;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #invoice {
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        #invoice h1 {
            color: #0087C3;
            font-size: 1.5em;
            line-height: 1em;
            font-weight: normal;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table {
            width: 100%;
            border-spacing: 0;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            /*margin-bottom: 10px;*/
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items th,
        table.items td {
            padding: 5px;
            /*background: #EEEEEE;*/
            border-bottom: 1px solid #FFFFFF;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }else{?> text-align: left;
        <?php }?>

        }

        table.items th {
            white-space: nowrap;
            font-weight: normal;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items td {
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }else{?> text-align: left;
        <?php }?>
        }

        table.items td h3 {
            color: #57B223;
            font-size: 1em;
            font-weight: normal;
            margin-top: 2px;
            margin-bottom: 2px;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items .no {
            background: #dddddd;
        }

        table.items .desc {
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }else{?> text-align: left;
        <?php }?>
        }

        table.items .unit {
            background: #F3F3F3;
            padding: 5px 10px 5px 5px;
            word-wrap: break-word;
        }

        table.items .qty {
        }

        table.items td.unit,
        table.items td.qty,
        table.items td.total {
            font-size: 1em;
        }

        table.items tbody tr:last-child td {
            border: none;

        }

        table.items tfoot td {
            padding: 5px 10px;
            background: #ffffff;
            border-bottom: none;
            font-size: 14px;
            white-space: nowrap;
            border-top: 1px solid #aaaaaa;
        <?php if(!empty($RTL)){?> text-align: right;
        <?php }?>
        }

        table.items tfoot tr:first-child td {
            border-top: none;
        }

        table.items tfoot tr:last-child td {
            color: #57B223;
            font-size: 1.4em;
            border-top: 1px solid #57B223;

        }

        table.items tfoot tr td:first-child {
            border: none;
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        #thanks {
            font-size: 16px;
            margin-bottom: 10px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;

        }

        #notices .notice {
            font-size: 1em;
            color: #777;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #aaaaaa;
            padding: 8px 0;
            text-align: center;
        }

        tr.total td, tr th.total, tr td.total {
        <?php if(!empty($RTL)){?> text-align: left;
        <?php }else{?> text-align: right;
        <?php }?>
        }

        .bg-items {
            background: #303252 !important;
            color: #ffffff
        }

        .p-md {
            padding: 9px !important;
        }

        .left {
        <?php if(!empty($RTL)){?> float: right;
        <?php }else{?> float: left;
        <?php }?>
        }

        .right {
        <?php if(!empty($RTL)){?> float: left;
            padding-right: 10px;
        <?php }else{?> float: right;
            padding-left: 10px;
        <?php }?>
        }

        .num_word {
            margin-top: 5px;
            margin-bottom: 5px;
        }
    
    </style>
</head>
<body>

<?php
$paid_amount = $this->invoice_model->calculate_to('paid_amount', $invoice_info->invoices_id);
$client_info = $this->invoice_model->check_by(array('client_id' => $invoice_info->client_id), 'tbl_client');
if (!empty($client_info)) {
    $currency = $this->invoice_model->client_currency_symbol($invoice_info->client_id);
    $client_lang = $client_info->language;
} else {
    $client_lang = 'english';
    $currency = $this->invoice_model->check_by(array('code' => config_item('default_currency')), 'tbl_currencies');
}

unset($this->lang->is_loaded[5]);
$language_info = $this->lang->load('sales_lang', $client_lang, TRUE, FALSE, '', TRUE);
$payment_status = $this->invoice_model->get_payment_status($invoice_info->invoices_id);

$uri = $this->uri->segment(3);
if ($uri == 'invoice_email') {
    $img = base_url() . config_item('invoice_logo');
} else {
    $img = ROOTPATH . '/' . config_item('invoice_logo');
    $a = file_exists($img);
    if (empty($a)) {
        $img = base_url() . config_item('invoice_logo');
    }
    if (!file_exists($img)) {
        $img = ROOTPATH . '/' . 'uploads/default_logo.png';
    }
}

?>
<table class="clearfix">
    <tr>
        <td style="width: 50%;">
            <div id="logo" class="left">
                <img style="width: 170px;height: 80px;float: left !important;" src="<?= $img ?>">
            </div>
        </td>
        <td style="width: 50%;">
            <div class="right" style="">
                <h3 style="margin-bottom: 0;margin-top: 0">Chalan: <span
                            style="text-align: right"><?= $invoice_info->reference_no ?></span></h3>
                <div class="date">Chalan Date
                    : <span
                            style="text-align: right"><?= strftime(config_item('date_format'), strtotime($invoice_info->invoice_date)); ?></span>
                </div>

                <?php if (!empty($invoice_info->user_id)) { ?>
                    <div class="date">
                        <?= lang('sales') . ' ' . lang('agent') ?>: <span style="text-align: right"><?php
                            $profile_info = $this->db->where('user_id', $invoice_info->user_id)->get('tbl_account_details')->row();
                            if (!empty($profile_info)) {
                                echo $profile_info->fullname;
                            }
                            ?></span>
                    </div>
                <?php } ?>
                <div class="date"><?= $language_info['payment_status'] ?>: <span
                            style="text-align: right"> <?= $payment_status ?></span></div>
                
                <?php $show_custom_fields = custom_form_label(9, $invoice_info->invoices_id);
                if (!empty($show_custom_fields)) {
                    foreach ($show_custom_fields as $c_label => $v_fields) {
                        if (!empty($v_fields)) {
                            ?>
                            <br>
                            <div class="date"><?= $c_label ?>: <span
                                        style="text-align: right"> <?= $v_fields ?></span></div>
                        <?php }
                    }
                }
                ?>
            </div>
        
        </td>
    </tr>
</table>

<table id="details" class="clearfix">
    <tr>
        <td style="width: 50%;overflow: hidden">
            <h4 class="p-md bg-items ">
                <?= lang('our_info') ?>
            </h4>
        </td>
        <td style="width: 50%">
            <h4 class="p-md bg-items ">
                <?= lang('customer') ?>
            </h4>
        </td>
    </tr>
    <tr style="margin-top: 0px">
        <td style="width: 50%;overflow: hidden">
            <div style="padding-left: 5px">
                <h3 style="margin: 0px"><?= (config_item('company_legal_name_' . $client_lang) ? config_item('company_legal_name_' . $client_lang) : config_item('company_legal_name')) ?></h3>
                <div><?= (config_item('company_address_' . $client_lang) ? config_item('company_address_' . $client_lang) : config_item('company_address')) ?></div>
                <div><?= (config_item('company_city_' . $client_lang) ? config_item('company_city_' . $client_lang) : config_item('company_city')) ?>
                    , <?= config_item('company_zip_code') ?></div>
                <div><?= (config_item('company_country_' . $client_lang) ? config_item('company_country_' . $client_lang) : config_item('company_country')) ?></div>
                <div> <?= config_item('company_phone') ?></div>
                <div><a href="mailto:<?= config_item('company_email') ?>"><?= config_item('company_email') ?></a></div>
                <div><?= config_item('company_vat') ?></div>
            </div>
        </td>
        <td style="width: 50%">
            <div style="padding-left: 5px">
                <?php
                if (!empty($client_info)) {
                    $client_name = $client_info->name;
                    $address = $client_info->address;
                    $city = $client_info->city;
                    $zipcode = $client_info->zipcode;
                    $country = $client_info->country;
                    $phone = $client_info->phone;
                    $email = $client_info->email;
                } else {
                    $client_name = '-';
                    $address = '-';
                    $city = '-';
                    $zipcode = '-';
                    $country = '-';
                    $phone = '-';
                    $email = '-';
                }
                ?>
                <h3 style="margin: 0px"><?= $client_name ?></h3>
                <div class="address"><?= $address ?></div>
                <div class="address"><?= $city ?>, <?= $zipcode ?>
                    ,<?= $country ?></div>
                <div class="address"><?= $phone ?></div>
                <div class="email"><a href="mailto:<?= $email ?>"><?= $email ?></a></div>
                <?php if (!empty($client_info->vat)) { ?>
                    <div class="email"><?= lang('vat_number') ?>: <?= $client_info->vat ?></div>
                <?php } ?>
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead class="p-md bg-items">
    <tr>
        <th><?= $language_info['description'] ?></th>
        <?php
        $colspan = 3;
        $invoice_view = config_item('invoice_view');
        if (!empty($invoice_view) && $invoice_view == '2') {
            $colspan = 4;
            ?>
            <th><?= lang('hsn_code') ?></th>
        <?php } ?>
        <th ><?= $language_info['qty'] ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $invoice_items = $this->invoice_model->ordered_items_by_id($invoice_info->invoices_id);

    if (!empty($invoice_items)) :
        $sum = 0;
        foreach ($invoice_items as $key => $v_item) :
            $item_name = $v_item->item_name ? $v_item->item_name : $v_item->item_desc;
            $item_tax_name = json_decode($v_item->item_tax_name);
            $sum += $v_item->quantity;
            ?>
            <tr>
                <td class="unit"><h3><?= $item_name ?></h3>
                    <small><?= nl2br($v_item->item_desc) ?></small>
                </td>
                <?php
                $invoice_view = config_item('invoice_view');
                if (!empty($invoice_view) && $invoice_view == '2') {
                    ?>
                    <td class="unit"><?= $v_item->hsn_code ?></td>
                <?php } ?>
                <td class="unit" ><?= $v_item->quantity . '   ' . $v_item->unit ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th class="unit" style="text-align: right"> <strong>Total : </strong>
            </th>
            <th class="unit" ><?= $v_item->quantity . '   ' . $v_item->unit ?></th>
        </tr>
    <?php endif ?>

    </tbody>

</table>
<div id="notices">
    <div class="notice"><?= ($invoice_info->notes) ?></div>
</div>

<footer>
    <?= config_item('invoice_footer') ?>
</footer>
</body>
</html>
