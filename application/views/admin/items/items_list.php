<?= message_box('success'); ?>
<?= message_box('error');
$created = can_action('39', 'created');
$edited = can_action('39', 'edited');
$deleted = can_action('39', 'deleted');
$all_customer_group = $this->db->where('type', 'items')->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();
$all_manufacturer = get_result('tbl_manufacturer');
if (!empty($created) || !empty($edited)) {
?>
<div class="nav-tabs-custom">
    <?php $is_department_head = is_department_head();
    if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>
        <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
             data-title="<?php echo lang('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu group animated zoomIn" style="width:300px;">
                <li class="filter_by all_filter"><a href="#"><?php echo lang('all'); ?></a></li>
                <li class="divider"></li>

                <li class="dropdown-submenu pull-left  " id="from_account">
                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('group'); ?></a>
                    <ul class="dropdown-menu dropdown-menu-left from_account" style="">
                        <?php if (count($all_customer_group) > 0) { ?>
                            <?php foreach ($all_customer_group as $group) {
                                ?>
                                <li class="filter_by" id="<?= $group->customer_group_id ?>" search-type="by_group">
                                    <a href="#"><?php echo $group->customer_group; ?></a>
                                </li>
                            <?php }
                            ?>
                            <div class="clearfix"></div>
                        <?php } ?>
                    </ul>
                </li>
                <div class="clearfix"></div>
                <li class="dropdown-submenu pull-left " id="to_account">
                    <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('manufacturer'); ?></a>
                    <ul class="dropdown-menu dropdown-menu-left to_account" style="">
                        <?php
                        if (count($all_manufacturer) > 0) { ?>
                            <?php foreach ($all_manufacturer as $v_manufacturer) {
                                ?>
                                <li class="filter_by" id="<?= $v_manufacturer->manufacturer_id ?>"
                                    search-type="by_manufacturer">
                                    <a href="#"><?php echo $v_manufacturer->manufacturer; ?></a>
                                </li>
                            <?php }
                            ?>
                            <div class="clearfix"></div>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    <?php } ?>
    <!-- Tabs within a box -->
    <ul class="nav nav-tabs">
        <li class="<?= $active == 1 ? 'active' : ''; ?>"><a href="#manage"
                                                            data-toggle="tab"><?= lang('all_items') ?></a></li>
        <li class="<?= $active == 2 ? 'active' : ''; ?>"><a href="#create"
                                                            data-toggle="tab"><?= lang('new_items') ?></a></li>

        <li class="<?= $active == 3 ? 'active' : ''; ?>"><a href="#group"
                                                            data-toggle="tab"><?= lang('group') . ' ' . lang('list') ?></a>
        </li>
        <li><a class="import" href="<?= base_url() ?>admin/items/import"><?= lang('import') . ' ' . lang('items') ?></a>
        </li>

        <li class=""><a href="#stock_use" data-toggle="tab"><?= lang('Use Stock') ?></a>
        </li>
        <li class=""><a href="#stock_transfer"
                        data-toggle="tab"><?= lang('Stock Transfer') ?></a>
        </li>
        <li class=""><a href="#stock-expense-and-transfer-history1"
                        data-toggle="tab"><?= lang('Stock Uses & Transfer History') ?></a>
        </li>

    </ul>
    <style type="text/css">
        .custom-bulk-button {
            display: initial;
        }
    </style>
    <div class="tab-content bg-white">
        <!-- ************** general *************-->
        <div class="tab-pane <?= $active == 1 ? 'active' : ''; ?>" id="manage">
            <?php } else { ?>
            <div class="panel panel-custom">
                <header class="panel-heading ">
                    <div class="panel-title"><strong><?= lang('all_items') ?></strong></div>
                </header>
                <?php } ?>
                <div class="table-responsive">
                    <table class="table table-striped DataTables bulk_table" id="DataTables" cellspacing="0"
                           width="100%">
                        <thead>
                        <tr>
                            <?php if (!empty($deleted)) { ?>
                                <th data-orderable="false">
                                    <div class="checkbox c-checkbox">
                                        <label class="needsclick">
                                            <input id="select_all" type="checkbox">
                                            <span class="fa fa-check"></span></label>
                                    </div>
                                </th>
                            <?php } ?>
                            <th><?= lang('item') ?></th>
                            <?php
                            $invoice_view = config_item('invoice_view');
                            if (!empty($invoice_view) && $invoice_view == '2') {
                                ?>
                                <th><?= lang('hsn_code') ?></th>
                            <?php } ?>
<!--                            --><?php //if (admin()) { ?>
<!--                                <th class="col-sm-1">--><?php //= lang('cost_price') ?><!--</th>-->
<!--                            --><?php //} ?>
                            <th class="col-sm-1"><?= lang('cost_price') ?></th>
                            <th class="col-sm-1"><?= lang('Sell Price') ?></th>
                            <th class="col-sm-1"><?= lang('unit') . ' ' . lang('type') ?></th>
                            <th class="col-sm-2"><?= lang('project') ?></th>
                            <th class="col-sm-2"><?= lang('task') ?></th>
                            <th class="col-sm-2"><?= lang('tax') ?></th>
                            <th class="col-sm-1"><?= lang('group') ?></th>
                            <?php $show_custom_fields = custom_form_table(18, null);
                            if (!empty($show_custom_fields)) {
                                foreach ($show_custom_fields as $c_label => $v_fields) {
                                    if (!empty($c_label)) {
                                        ?>
                                        <th><?= $c_label ?> </th>
                                    <?php }
                                }
                            }
                            ?>
                            <?php if (!empty($edited) || !empty($deleted)) { ?>
                                <th class="col-sm-1"><?= lang('action') ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <script type="text/javascript">
                            $(document).ready(function () {
                                list = base_url + "admin/items/itemsList" + "<?php echo(($type === 'project') ? '/' . $project_id . '/' . $type : ''); ?>";
                                bulk_url = base_url + "admin/items/bulk_delete";
                                $('.filtered > .dropdown-toggle').on('click', function () {
                                    if ($('.group').css('display') == 'block') {
                                        $('.group').css('display', 'none');
                                    } else {
                                        $('.group').css('display', 'block')
                                    }
                                });
                                $('.filter_by').on('click', function () {
                                    $('.filter_by').removeClass('active');
                                    $('.group').css('display', 'block');
                                    $(this).addClass('active');
                                    var filter_by = $(this).attr('id');
                                    if (filter_by) {
                                        filter_by = filter_by;
                                    } else {
                                        filter_by = '';
                                    }
                                    var search_type = $(this).attr('search-type');
                                    if (search_type) {
                                        search_type = '/' + search_type;
                                    } else {
                                        search_type = '';
                                    }
                                    table_url(base_url + "admin/items/itemsList/" + filter_by + search_type);
                                });
                            });
                        </script>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($created) || !empty($edited)) { ?>
            <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="create">
                <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
                      action="<?php echo base_url(); ?>admin/items/saved_items/<?php
                      if (!empty($items_info)) {
                          echo $items_info->saved_items_id;
                      }
                      ?>" method="post" class="form-horizontal row ">
                    <div class="col-sm-7">
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('item_name') ?> <span
                                        class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" value="<?php
                                if (!empty($items_info)) {
                                    echo $items_info->item_name;
                                }
                                ?>" name="item_name" required="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 col-md-3 col-sm-3 control-label"><?= lang('code') ?> <span
                                        class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" value="<?php
                                if (!empty($items_info)) {
                                    echo $items_info->code;
                                }
                                ?>" name="code" required="" placeholder="<?= lang('item_code_help') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-3 col-md-3 col-sm-3 control-label"><?= lang('barcode_symbology') ?>
                                <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <?php
                                if (!empty($items_info->barcode_symbology)) {
                                    $barcode_symbology = $items_info->barcode_symbology;
                                } else {
                                    $barcode_symbology = null;
                                }
                                $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca ' => 'UPC-A', 'upce' => 'UPC-E');
                                echo form_dropdown('barcode_symbology', $bs, set_value('barcode_symbology', $barcode_symbology), 'class="form-control select2" id="barcode_symbology" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                        <?php
                        $invoice_view = config_item('invoice_view');
                        if (!empty($invoice_view) && $invoice_view == '2') {
                            ?>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('hsn_code') ?></label>
                                <div class="col-lg-9">
                                    <input type="text" data-parsley-type="number" class="form-control" value="<?php
                                    if (!empty($items_info)) {
                                        echo $items_info->hsn_code;
                                    }
                                    ?>" name="hsn_code" required="">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="field-1"
                                   class="col-sm-3 col-md-3 col-sm-3 control-label"><?= lang('manufacturer') ?></label>
                            <div class="col-sm-5">
                                <div class="input-group">
                                    <select class="form-control select_box" style="width: 100%" name="manufacturer_id">
                                        <option value=""><?= lang('select') . ' ' . lang('manufacturer') ?></option>
                                        <?php
                                        $manufacturer_info = get_result('tbl_manufacturer');
                                        if (!empty($manufacturer_info)) {
                                            foreach ($manufacturer_info as $manufacturer) {
                                                ?>
                                                <option value="<?= $manufacturer->manufacturer_id ?>" <?php
                                                if (!empty($items_info->manufacturer_id)) {
                                                    echo $items_info->manufacturer_id == $manufacturer->manufacturer_id ? 'selected' : null;
                                                }
                                                ?>><?= $manufacturer->manufacturer ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="input-group-addon"
                                         title="<?= lang('new') . ' ' . lang('manufacturer') ?>" data-toggle="tooltip"
                                         data-placement="top">
                                        <a data-toggle="modal" data-target="#myModal"
                                           href="<?= base_url() ?>admin/items/manufacturer"><i
                                                    class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 col-md-3 col-sm-3 control-label"><?= lang('cost_price') ?> <span
                                        class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" data-parsley-type="number" class="form-control" value="<?php
                                if (!empty($items_info->cost_price)) {
                                    echo $items_info->cost_price;
                                }
                                ?>" name="cost_price" required="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('unit_price') ?> <span
                                        class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" data-parsley-type="number" class="form-control" value="<?php
                                if (!empty($items_info)) {
                                    echo $items_info->sell_price;
                                }
                                ?>" name="sell_price" required="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('unit') . ' ' . lang('type') ?></label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" value="<?php
                                if (!empty($items_info)) {
                                    echo $items_info->unit_type;
                                }
                                ?>" placeholder="<?= lang('unit_type_example') ?>" name="unit_type">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('quantity') ?> <span
                                        class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input type="text" data-parsley-type="number" class="form-control" value="<?php
                                if (!empty($items_info)) {
                                    echo $items_info->quantity;
                                }
                                ?>" name="quantity" required="">
                            </div>

                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('Projects') ?></label>
                            <div class="col-lg-9">
                                <?php

                                $projects = $this->db->order_by('project_name', 'ASC')
                                    ->select('project_name,project_id')
                                    ->get('tbl_project')->result();
                                $select = '<select class="selectpicker" data-width="100%" name="project_id" data-none-selected-text="' . lang('Projects') . '">';
                                $select .= '<option value="">' . lang('select') . ' ' . lang('Projects') . '</option>';
                                if (!empty($projects)) {
                                    foreach ($projects as $project) {
                                        $select .= '<option value="' . $project->project_id . '"';
                                        if (!empty($items_info) && $items_info->project_id == $project->project_id) {
                                            $select .= ' selected';
                                        }
                                        if (!empty($project_id) && $project_id == $project->project_id) {
                                            $select .= ' selected';
                                        }
                                        $select .= '>' . $project->project_name . '</option>';
                                    }
                                }
                                $select .= '</select>';
                                echo $select;
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('item') . ' ' . lang('group') ?> </label>
                            <div class="col-lg-9">
                                <div class="input-group">
                                    <select name="customer_group_id" class="form-control select_box"
                                            style="width: 100%">
                                        <option value=""><?= lang('none') ?></option>
                                        <?php
                                        if (!empty($all_customer_group)) {
                                            foreach ($all_customer_group as $customer_group) {
                                                ?>
                                                <option value="<?= $customer_group->customer_group_id ?>" <?php
                                                if (!empty($items_info) && $items_info->customer_group_id == $customer_group->customer_group_id) {
                                                    echo 'selected';
                                                }
                                                ?>><?= $customer_group->customer_group ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="input-group-addon"
                                         title="<?= lang('new') . ' ' . lang('item') . ' ' . lang('group') ?>"
                                         data-toggle="tooltip" data-placement="top">
                                        <a data-toggle="modal" data-target="#myModal"
                                           href="<?= base_url() ?>admin/items/items_group"><i
                                                    class="fa fa-plus"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 control-label"><?= lang('tax') ?></label>
                            <div class="col-lg-9">
                                <?php

                                $taxes = $this->db->order_by('tax_rate_percent', 'ASC')->get('tbl_tax_rates')->result();
                                if (!empty($items_info->tax_rates_id) && !is_numeric($items_info->tax_rates_id)) {
                                    $tax_rates_id = json_decode($items_info->tax_rates_id);
                                }
                                $select = '<select class="selectpicker" data-width="100%" name="tax_rates_id[]" multiple data-none-selected-text="' . lang('no_tax') . '">';
                                foreach ($taxes as $tax) {
                                    $selected = '';
                                    if (!empty($tax_rates_id) && is_array($tax_rates_id)) {
                                        if (in_array($tax->tax_rates_id, $tax_rates_id)) {
                                            $selected = ' selected ';
                                        }
                                    }
                                    $select .= '<option value="' . $tax->tax_rates_id . '"' . $selected . 'data-taxrate="' . $tax->tax_rate_percent . '" data-taxname="' . $tax->tax_rate_name . '" data-subtext="' . $tax->tax_rate_name . '">' . $tax->tax_rate_percent . '%</option>';
                                }
                                $select .= '</select>';
                                echo $select;
                                ?>
                            </div>
                        </div>
                        <?php
                        if (!empty($items_info)) {
                            $saved_items_id = $items_info->saved_items_id;
                        } else {
                            $saved_items_id = null;
                        }
                        ?>
                        <?= custom_form_Fields(18, $saved_items_id); ?>


                        <div class="form-group mt-lg">
                            <label class="col-lg-3 control-label"></label>
                            <div class="col-lg-9">
                                <div class="btn-bottom-toolbar">
                                    <?php
                                    if (!empty($items_info)) { ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                                        <button type="button" onclick="goBack()"
                                                class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                                    <?php } else {
                                        ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                                    <?php }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 pull-right">
                        <div class="panel panel-custom">
                            <div class="panel-heading">
                                <?= lang('image') ?> </div>
                            <div class="panel-body">

                                <div id="comments_file-dropzone" class="dropzone mb15">

                                </div>
                                <div id="comments_file-dropzone-scrollbar">
                                    <div id="comments_file-previews">
                                        <div id="file-upload-row" class="mt pull-left">

                                            <div class="preview box-content pr-lg" style="width:100px;">
                                                        <span data-dz-remove class="pull-right" style="cursor: pointer">
                                                            <i class="fa fa-times"></i>
                                                        </span>
                                                <img data-dz-thumbnail class="upload-thumbnail-sm"/>
                                                <input class="file-count-field" type="hidden" name="files[]" value=""/>
                                                <div class="mb progress progress-striped upload-progress-sm active mt-sm"
                                                     role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                                     aria-valuenow="0">
                                                    <div class="progress-bar progress-bar-success" style="width:0%;"
                                                         data-dz-uploadprogress></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if (!empty($items_info->upload_file)) {
                                    $uploaded_file = json_decode($items_info->upload_file);
                                }
                                if (!empty($uploaded_file)) {
                                    foreach ($uploaded_file as $v_files_image) { ?>
                                        <div class="pull-left mt pr-lg mb" style="width:100px;">
                                            <span data-dz-remove class="pull-right existing_image"
                                                  style="cursor: pointer"><i class="fa fa-times"></i></span>
                                            <?php if ($v_files_image->is_image == 1) { ?>
                                                <img data-dz-thumbnail
                                                     src="<?php echo base_url() . $v_files_image->path ?>"
                                                     class="upload-thumbnail-sm"/>
                                            <?php } else { ?>
                                                <span data-toggle="tooltip" data-placement="top"
                                                      title="<?= $v_files_image->fileName ?>"
                                                      class="mailbox-attachment-icon"><i class="fa fa-file-text-o"></i></span>
                                            <?php } ?>

                                            <input type="hidden" name="path[]"
                                                   value="<?php echo $v_files_image->path ?>">
                                            <input type="hidden" name="fileName[]"
                                                   value="<?php echo $v_files_image->fileName ?>">
                                            <input type="hidden" name="fullPath[]"
                                                   value="<?php echo $v_files_image->fullPath ?>">
                                            <input type="hidden" name="size[]"
                                                   value="<?php echo $v_files_image->size ?>">
                                            <input type="hidden" name="is_image[]"
                                                   value="<?php echo $v_files_image->is_image ?>">
                                        </div>
                                    <?php }; ?>
                                <?php }; ?>
                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        $(".existing_image").on("click", function () {
                                            $(this).parent().remove();
                                        });

                                        fileSerial = 0;
                                        // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
                                        var previewNode = document.querySelector("#file-upload-row");
                                        previewNode.id = "";
                                        var previewTemplate = previewNode.parentNode.innerHTML;
                                        previewNode.parentNode.removeChild(previewNode);
                                        Dropzone.autoDiscover = false;
                                        var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                                            url: "<?= base_url() ?>admin/global_controller/upload_file",
                                            thumbnailWidth: 80,
                                            thumbnailHeight: 80,
                                            parallelUploads: 20,
                                            previewTemplate: previewTemplate,
                                            dictDefaultMessage: '<?php echo lang("file_upload_instruction"); ?>',
                                            autoQueue: true,
                                            previewsContainer: "#comments_file-previews",
                                            clickable: true,
                                            accept: function (file, done) {
                                                if (file.name.length > 200) {
                                                    done("Filename is too long.");
                                                    $(file.previewTemplate).find(".description-field").remove();
                                                }
                                                //validate the file
                                                $.ajax({
                                                    url: "<?= base_url() ?>admin/global_controller/validate_project_file",
                                                    data: {
                                                        file_name: file.name,
                                                        file_size: file.size
                                                    },
                                                    cache: false,
                                                    type: 'POST',
                                                    dataType: "json",
                                                    success: function (response) {
                                                        if (response.success) {
                                                            fileSerial++;
                                                            $(file.previewTemplate).find(".description-field").attr("name", "comment_" + fileSerial);
                                                            $(file.previewTemplate).append("<input type='hidden' name='file_name_" + fileSerial + "' value='" + file.name + "' />\n\
                                                                        <input type='hidden' name='file_size_" + fileSerial + "' value='" + file.size + "' />");
                                                            $(file.previewTemplate).find(".file-count-field").val(fileSerial);
                                                            done();
                                                        } else {
                                                            $(file.previewTemplate).find("input").remove();
                                                            done(response.message);
                                                        }
                                                    }
                                                });
                                            },
                                            processing: function () {
                                                $("#file-save-button").prop("disabled", true);
                                            },
                                            queuecomplete: function () {
                                                $("#file-save-button").prop("disabled", false);
                                            },
                                            fallback: function () {
                                                //add custom fallback;
                                                $("body").addClass("dropzone-disabled");
                                                $('.modal-dialog').find('[type="submit"]').removeAttr('disabled');

                                                $("#comments_file-dropzone").hide();

                                                $("#file-modal-footer").prepend("<button id='add-more-file-button' type='button' class='btn  btn-default pull-left'><i class='fa fa-plus-circle'></i> " + "<?php echo lang("add_more"); ?>" + "</button>");

                                                $("#file-modal-footer").on("click", "#add-more-file-button", function () {
                                                    var newFileRow = "<div class='file-row pb pt10 b-b mb10'>" +
                                                        "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>" +
                                                        "<div class='mb5 pb5'><input class='form-control description-field'  name='comment[]'  type='text' style='cursor: auto;' placeholder='<?php echo lang("comment") ?>' /></div>" +
                                                        "</div>";
                                                    $("#comments_file-previews").prepend(newFileRow);
                                                });
                                                $("#add-more-file-button").trigger("click");
                                                $("#comments_file-previews").on("click", ".remove-file", function () {
                                                    $(this).closest(".file-row").remove();
                                                });
                                            },
                                            success: function (file) {
                                                setTimeout(function () {
                                                    $(file.previewElement).find(".progress-striped").removeClass("progress-striped").addClass("progress-bar-success");
                                                }, 1000);
                                            }
                                        });

                                    })
                                </script>
                            </div>
                        </div>
                        <div class="">
                            <label class=""><?= lang('description') ?> </label>
                            <div class="">
                                        <textarea name="item_desc" class="form-control textarea_"><?php
                                            if (!empty($items_info)) {
                                                echo $items_info->item_desc;
                                            }
                                            ?></textarea>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="tab-pane <?= $active == 3 ? 'active' : ''; ?>" id="group">

                <div class="table-responsive">
                    <table class="table table-striped ">
                        <thead>
                        <tr>
                            <th><?= lang('group') . ' ' . lang('name') ?></th>
                            <th><?= lang('description') ?></th>
                            <?php if (!empty($edited) || !empty($deleted)) { ?>
                                <th><?= lang('action') ?></th>
                            <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $all_customer_group = $this->db->where('type', 'items')->get('tbl_customer_group')->result();
                        if (!empty($all_customer_group)) {
                            foreach ($all_customer_group as $customer_group) {
                                ?>
                                <tr id="table_items_group_<?= $customer_group->customer_group_id ?>">
                                    <td><?php
                                        $id = $this->uri->segment(5);
                                        if (!empty($id) && $id == $customer_group->customer_group_id) { ?>
                                        <form method="post" action="<?= base_url() ?>admin/items/saved_group/<?php
                                        if (!empty($group_info)) {
                                            echo $group_info->customer_group_id;
                                        }
                                        ?>" class="form-horizontal">
                                            <input type="text" name="customer_group" value="<?php
                                            if (!empty($customer_group)) {
                                                echo $customer_group->customer_group;
                                            }
                                            ?>" class="form-control"
                                                   placeholder="<?= lang('enter') . ' ' . lang('group') . ' ' . lang('name') ?>"
                                                   required>
                                            <?php } else {
                                                echo $customer_group->customer_group;
                                            }
                                            ?>
                                    </td>
                                    <td><?php
                                        $id = $this->uri->segment(5);
                                        if (!empty($id) && $id == $customer_group->customer_group_id) { ?>
                                            <textarea name="description" rows="1" class="form-control"><?php
                                                if (!empty($customer_group)) {
                                                    echo $customer_group->description;
                                                }
                                                ?></textarea>
                                        <?php } else {
                                            echo $customer_group->description;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $id = $this->uri->segment(5);
                                        if (!empty($id) && $id == $customer_group->customer_group_id) { ?>
                                            <?= btn_update() ?>
                                            </form>
                                            <?= btn_cancel('admin/items/items_list/group/') ?>
                                        <?php } else { ?>
                                        <?= btn_edit('admin/items/items_list/group/' . $customer_group->customer_group_id) ?>
                                        <?php echo ajax_anchor(base_url("admin/items/delete_group/" . $customer_group->customer_group_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_items_group_" . $customer_group->customer_group_id)); ?>
                                    </td>
                                    <?php } ?>
                                </tr>
                            <?php }
                        } ?>
                        <form role="form" enctype="multipart/form-data" id="form"
                              action="<?php echo base_url(); ?>admin/items/saved_group/<?php
                              if (!empty($group_info)) {
                                  echo $group_info->customer_group_id;
                              }
                              ?>" method="post" class="form-horizontal  ">
                            <tr>
                                <td><input required type="text" name="customer_group" class="form-control"
                                           placeholder="<?= lang('enter') . ' ' . lang('group') . ' ' . lang('name') ?>">
                                </td>
                                <td>
                                    <textarea name="description" rows="1" class="form-control"></textarea>
                                </td>
                                <td><?= btn_add() ?></td>
                            </tr>
                        </form>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="stock_use">
                <div class="row mb-lg invoice estimate-template">
                    <form name="myform" role="form" data-parsley-validate="" novalidate=""
                          enctype="multipart/form-data"
                          id="form"
                          action="<?php echo base_url(); ?>admin/purchase/stockIteamAction" method="post" class="form-horizontal">
                        <div class="col-sm-10 col-xs-12  ">
                            <div class="row text-right">
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control select_box" style="width: 100%" name="project_id" onchange="showTask(event , undefined , 'task-lists-for-stock-use')"
                                                id="client_project">
                                            <option value=""><?= lang('none') ?></option>
                                            <?php

                                            $all_project = $this->db->get('tbl_project')->result();
                                            if (!empty($all_project)) {
                                                foreach ($all_project as $v_cproject) {
                                                    ?>
                                                    <option value="<?= $v_cproject->project_id ?>" <?php
                                                    if (!empty($project_id)) {
                                                        echo $v_cproject->project_id == $project_id ? 'selected' : '';
                                                    }
                                                    ?>><?= $v_cproject->project_name ?></option>
                                                    <?php
                                                }
                                            }

                                            ?>
                                        </select>
                                    </div>

                                </div>
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('task') ?> /
                                        Sub <?= lang('task') ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control select_box" style="width: 100%" id="task-lists-for-stock-use" onchange="getStockByProjectOrTask(event , 'task' , 'stock-lists-for-stock-use')"
                                                name="trn_task_id">
                                            <option value=""><?= lang('select') . ' ' . lang('task') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">

                                    <?php
                                     $fetch_data = $this->db->get('tbl_saved_items')->result();
                                    ?>

                                    <label class="col-lg-3 control-label"><?= lang('Select Stock Item') ?> </label>
                                    <div class="col-lg-7" style="text-align: left">
                                        <select name="purchese_item_id" class="form-control select_box"  data-width="100%" onchange="getSelectedItem(event)" id="stock-lists-for-stock-use">
                                            <option value="" selected> Select Stock Item</option>
                                            <?php
                                            foreach ($fetch_data as $_key => $v_purchase) {
                                                ?>
                                                <option value="<?= $v_purchase->saved_items_id ?>">
                                                    <?= $v_purchase->item_name.' ('. $v_purchase->quantity.' '. $v_purchase->unit_type.')' ?>
                                                </option>
                                                <?php
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"><?= lang('Available Stock') ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control" value=""
                                               name="available_stock" id="available_stock" readonly>
                                        <input type="hidden" class="form-control"
                                               name="unit_type" id="unit_type" readonly>


                                    </div>

                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"><?= lang('Used Stock') ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control" value=""
                                               placeholder="Enter Used Stock"
                                               name="used_stock" id="used_stock">
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="used_date" class="col-lg-3 control-label"><?= lang('Used Date') ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="date" class="form-control" value=""
                                               placeholder="Enter Date"
                                               name="used_date" id="used_date" required>
                                    </div>

                                </div>

                                <button type="submit" class="btn btn-info">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
            <!-- End Tasks Management-->
            <div class="tab-pane" id="stock_transfer">
                <div class="row mb-lg invoice estimate-template">
                    <form name="myform" role="form" data-parsley-validate="" novalidate=""
                          enctype="multipart/form-data"
                          id="form"
                          action="<?php echo base_url(); ?>admin/purchase/stockIteamTransfer" method="post" class="form-horizontal">
                        <div class="col-sm-10 col-xs-12  ">
                            <div class="row text-right">
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control select_box" style="width: 100%" name="project_id" onchange="showTask(event , undefined , 'task-lists-for-stock-transfer')"
                                                id="client_project">
                                            <option value=""><?= lang('none') ?></option>
                                            <?php

                                            $all_project = $this->db->get('tbl_project')->result();
                                            if (!empty($all_project)) {
                                                foreach ($all_project as $v_cproject) {
                                                    ?>
                                                    <option value="<?= $v_cproject->project_id ?>" <?php
                                                    if (!empty($project_id)) {
                                                        echo $v_cproject->project_id == $project_id ? 'selected' : '';
                                                    }
                                                    ?>><?= $v_cproject->project_name ?></option>
                                                    <?php
                                                }
                                            }

                                            ?>
                                        </select>
                                    </div>

                                </div>
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('task') ?> /
                                        Sub <?= lang('task') ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control select_box" style="width: 100%" id="task-lists-for-stock-transfer" onchange="getStockByProjectOrTask(event , 'task' , 'stock-lists-for-stock-transfer')"
                                                name="trn_task_id">
                                            <option value=""><?= lang('select') . ' ' . lang('task') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">

                                    <?php
                                    $fetch_data = $this->db->get('tbl_saved_items')->result();
                                    ?>

                                    <label class="col-lg-3 control-label"><?= lang('Select Stock Item') ?> </label>
                                    <div class="col-lg-7" style="text-align: left">
                                        <select name="purchese_item_id" class="form-control select_box" data-width="100%"id="stock-lists-for-stock-transfer" onchange="getSelectedItem1(event)" >
                                            <option value="" selected> Select Stock Item</option>
                                            <?php
                                            foreach ($fetch_data as $_key => $v_purchase) {
                                                ?>
                                                <option value="<?= $v_purchase->saved_items_id ?>">
                                                    <?= $v_purchase->item_name.' ('. $v_purchase->quantity.' '. $v_purchase->unit_type.')' ?>
                                                </option>
                                                <?php
                                            }
                                            ?>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"><?= lang('Available Stock') ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control" value=""
                                               name="available_stockyy" id="available_stock1" disabled>
                                        <input type="hidden" class="form-control" value=""
                                               name="task_id" id="task_id">

                                    </div>

                                </div>
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control select_box" style="width: 100%" name="project_id" onchange="showTask(event )"
                                                id="client_project">
                                            <option value=""><?= lang('none') ?></option>
                                            <?php

                                            $all_project = $this->db->get('tbl_project')->result();
                                            if (!empty($all_project)) {
                                                foreach ($all_project as $v_cproject) {
                                                    ?>
                                                    <option value="<?= $v_cproject->project_id ?>" <?php
                                                    if (!empty($project_id)) {
                                                        echo $v_cproject->project_id == $project_id ? 'selected' : '';
                                                    }
                                                    ?>><?= $v_cproject->project_name ?></option>
                                                    <?php
                                                }
                                            }

                                            ?>
                                        </select>
                                    </div>

                                </div>
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('task') ?> /
                                        Sub <?= lang('task') ?></label>
                                    <div class="col-lg-7">
                                        <select class="form-control select_box" style="width: 100%" id="task-lists"
                                                name="trn_task_id">
                                            <option value=""><?= lang('select') . ' ' . lang('task') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group text-left">
                                    <label class="col-lg-3 control-label"><?= lang('Transfer Amount') ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control" value=""
                                               placeholder="Enter Used Stock"
                                               name="transfer_amount" id="used_stock">
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="used_date" class="col-lg-3 control-label"><?= lang('Used Date') ?>
                                        <span class="text-danger">*</span></label>
                                    <div class="col-lg-7">
                                        <input type="date" class="form-control" value=""
                                               placeholder="Enter Used Stock"
                                               name="used_date" id="used_date" required>
                                    </div>

                                </div>


                                <button type="submit" class="btn btn-info">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>

            <div class="tab-pane" id="stock-expense-and-transfer-history1">
                <div class="table-responsive">
                    <table class="table table-striped DataTables bulk_table" id="DataTables"
                           cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="2"><?= lang('Item Name') ?></th>
                            <th class="2"><?= lang('Transfer/Used From Project') ?></th>
                            <th class="1"><?= lang('Transfer/Used From Task') ?></th>
                            <th class="1"><?= lang('Transfer/Used To Project') ?></th>
                            <th class="1"><?= lang('Transfer/Used To Task') ?></th>
                            <th class="2"><?= lang('quantity') ?></th>
                            <th class="2"><?= lang('Used Date') ?></th>
                            <th class="2"><?= lang('unit') . ' ' . lang('type') ?></th>
                            <th class="2"><?= lang('Type of Transaction') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $this->db->from('tbl_stock_uses');
                        $this->db->join('tbl_saved_items', 'tbl_stock_uses.item_id = tbl_saved_items.saved_items_id', 'left');
                        $this->db->select('tbl_stock_uses.*, tbl_saved_items.item_name');
                        $this->db->order_by('id', 'desc');
                        //                        $this->db->where('tbl_saved_items.task_id', $task_details->task_id);
                        $query_result = $this->db->get();
                        $result = $query_result->result();
                        foreach ($result as $key => $row) {
                            ?>
                            <tr>
                                <td><?= $key+1 ?></td>
                                <td><?= $row->item_name ?></td>
                                <td>
                                    <?php
                                    $this->db->from('tbl_saved_items');
                                    $this->db->join('tbl_project', 'tbl_saved_items.project_id = tbl_project.project_id', 'left');
                                    $this->db->select('tbl_project.project_name');
                                    $this->db->where('saved_items_id', $row->item_id);
                                    $query_result = $this->db->get();
                                    $query_result = $query_result->row();
                                    echo $query_result->project_name ?? '-';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $this->db->from('tbl_saved_items');
                                    $this->db->join('tbl_task', 'tbl_saved_items.task_id = tbl_task.task_id', 'left');
                                    $this->db->select('tbl_task.task_name');
                                    $this->db->where('saved_items_id', $row->item_id);
                                    $query_result = $this->db->get();
                                    $query_result = $query_result->row();
                                    echo $query_result->task_name?? '-';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $this->db->from('tbl_saved_items');
                                    $this->db->join('tbl_project', 'tbl_saved_items.project_id = tbl_project.project_id', 'left');
                                    $this->db->select('tbl_project.project_name');
                                    $this->db->where('saved_items_id', $row->transfer_to_item_id);
                                    $query_result = $this->db->get();
                                    $query_result = $query_result->row();
                                    echo $query_result->project_name?? '-';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $this->db->from('tbl_saved_items');
                                    $this->db->join('tbl_task', 'tbl_saved_items.task_id = tbl_task.task_id', 'left');
                                    $this->db->select('tbl_task.task_name');
                                    $this->db->where('saved_items_id', $row->transfer_to_item_id);
                                    $query_result = $this->db->get();
                                    $query_result = $query_result->row();
                                    echo $query_result->task_name ?? '-';
                                    ?>

                                </td>
                                <td><?= $row->quantity ?></td>
                                <td width="100"><?= $row->used_date ? date_format(date_create($row->used_date),'d-M-Y') : '--' ?></td>
                                <td><?= $row->unit_type ?></td>
                                <td class="text-capitalize">
                                    <a class="btn <?= $row->type == 'expense' ? "btn-info" : "btn-success"?>" href="#"><?= $row->type ?></a>
                                </td>

                            </tr>
                            <?php
                        }
                        ?>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        <?php } ?>


    </div>

    <script type="text/javascript">

        function getXMLHTTP() { //fuction to return the xml http object
            var xmlhttp = false;
            try {
                xmlhttp = new XMLHttpRequest();
            } catch (e) {
                try {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                    try {
                        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                    } catch (e1) {
                        xmlhttp = false;
                    }
                }
            }
            return xmlhttp;
        }

        function showTask(e, project_id ,element_id = "task-lists" ) {
            if (project_id == 77777){
                let url = base_url + 'admin/global_controller/get_tasks/' + e.target.value;
                $.ajax({
                    async: false,
                    url: url,
                    type: 'GET',
                    dataType: "json",
                    success: function (data) {
                        var result = data.responseText;
                        console.log(result);
                        $('#'+element_id).empty();
                        $("#"+element_id).html(result);
                    }

                });
            }
            if (project_id == undefined){
                var base_url = '<?= base_url() ?>';
                var strURL = base_url + 'admin/global_controller/get_tasks/' + e.target.value;
                var req = getXMLHTTP();
                if (req) {
                    req.onreadystatechange = function () {
                        if (req.readyState == 4) {
                            // only if "OK"
                            if (req.status == 200) {
                                var result = req.responseText;
                                $('#'+element_id).empty();
                                $("#"+element_id).append(result);
                            } else {
                                alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                            }
                        }
                    }
                    req.open("POST", strURL, true);
                    req.send(null);
                }
            }
        }
        function getStockByProjectOrTask(e, type = 'task',element_id = null ) {
                var base_url = '<?= base_url() ?>';
                let url_help = type == 'task' ?  null + '/' +e.target.value : e.target.value + '/' + null;
                var strURL = base_url + 'admin/global_controller/get_stock/' + url_help;
                var req = getXMLHTTP();
                if (req) {
                    req.onreadystatechange = function () {
                        if (req.readyState == 4) {
                            // only if "OK"
                            if (req.status == 200) {
                                var result = req.responseText;
                                console.log(result)
                                console.log(element_id)
                                $('#'+element_id).empty();
                                $("#"+element_id).append(result);
                            } else {
                                alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                            }
                        }
                    }
                    req.open("POST", strURL, true);
                    req.send(null);
                }

        }

        function getSelectedItem(e) {
            var items = <?php echo json_encode($fetch_data); ?>;
            // console.log(items)
            // items = JSON.parse(items)
            var item = items.filter(function (item) {
                return item.saved_items_id == e.target.value;
            });
            console.log(item)
            $('#available_stock').val(item[0].quantity);
            $('#unit_type').val(item[0].unit_type);
        }
        function getSelectedItem1(e) {
            var items = <?php echo json_encode($fetch_data); ?>;
            // console.log(items)
            // items = JSON.parse(items)
            var item = items.filter(function (item) {
                return item.saved_items_id == e.target.value;
            });
            $('#available_stock1').val(item[0].quantity);
        }

    </script>