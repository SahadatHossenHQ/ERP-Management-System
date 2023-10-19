<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>
<style>
    .note-editor .note-editable {
        height: 150px;
    }

    a:hover {
        text-decoration: none;
    }
</style>
<?php
$task_ids = get_all_sub_tasks($task_details->task_id);
$task_ids = $task_ids ?? [];
$edited = can_action('54', 'edited');
$task_id_ = $task_details->sub_task_id;
//var_dump($task_details->task_id);
//die();
while ($task_details->project_id === NULL) {
    $tsk = $this->db->where('task_id', $task_id_)->get('tbl_task')->row();
    $task_details->project_id = $tsk->project_id;
    $task_id_ = $tsk->sub_task_id;
}
$can_edit = $this->tasks_model->can_action('tbl_task', 'edit', array('task_id' => $task_details->task_id));
// get all comments by tasks id
$comment_details = $this->db->where(array('task_id' => $task_details->task_id, 'comments_reply_id' => '0', 'task_attachment_id' => '0', 'uploaded_files_id' => '0'))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result();
// get all $total_timer by tasks id
$total_timer = $this->db->where(array('task_id' => $task_details->task_id, 'start_time !=' => 0, 'end_time !=' => 0,))->get('tbl_tasks_timer')->result();
$all_sub_tasks = $this->db->where(array('sub_task_id' => $task_details->task_id))->get('tbl_task')->result();
$activities_info = $this->db->where(array('module' => 'tasks', 'module_field_id' => $task_details->task_id))->order_by('activity_date', 'DESC')->get('tbl_activities')->result();
$all_sub_task_ids = get_all_sub_tasks($task_details->task_id);
$all_requisition_info = $this->db->where_in('task_id' , $all_sub_task_ids)->get('tbl_requisitions')->result();

$all_expense_info = $this->db->where(array( 'type' => 'Expense'))->where_in('task_id',[...$task_ids,$task_details->task_id])->get('tbl_transactions')->result();
$all_estimates_info = $this->db->where(array('task_id' => $task_details->task_id))->get('tbl_estimates')->result();

$where = array('user_id' => $this->session->userdata('user_id'), 'module_id' => $task_details->task_id, 'module_name' => 'tasks');
$check_existing = $this->tasks_model->check_by($where, 'tbl_pinaction');
if (!empty($check_existing)) {
    $url = 'remove_todo/' . $check_existing->pinaction_id;
    $btn = 'danger';
    $title = lang('remove_todo');
} else {
    $url = 'add_todo_list/tasks/' . $task_details->task_id;
    $btn = 'warning';
    $title = lang('add_todo_list');
}
$sub_tasks = config_item('allow_sub_tasks');
?>
<div class="row mt-lg">
    <div class="col-sm-2">
        <!-- Tabs within a box -->
        <ul class="nav nav-pills nav-stacked navbar-custom-nav">

            <li class="<?= $active == 1 ? 'active' : '' ?>"><a href="#task_details"
                                                               data-toggle="tab"><?= lang('tasks') . ' ' . lang('details') ?></a>
            </li>
            <li class="<?= $active == 2 ? 'active' : '' ?>"><a href="#task_comments"
                                                               data-toggle="tab"><?= lang('comments') ?> <strong
                            class="pull-right"><?= (!empty($comment_details) ? count($comment_details) : null) ?></strong></a>
            </li>

            <li class="">
                <a href="#timesheet"
                   data-toggle="tab"><?= lang('Contactor') ?>
                    <!--                    <strong class="pull-right">-->
                    <?php //= (!empty($total_timer) ? count($total_timer) : null) ?><!--</strong>-->
                </a>
            </li>
            <li class="">
                <a href="#requisition"
                   data-toggle="tab"><?= lang('Requisition') ?>
                    <strong class="pull-right"><?= (!empty($all_requisition_info) ? count($all_requisition_info) : null) ?></strong>
                </a>
            </li>
            <li class="">
                <a href="#expense"
                   data-toggle="tab"><?= lang('Expense') ?>

                    <strong class="pull-right"><?= (!empty($all_expense_info) ? count($all_expense_info) : null) ?></strong>
                </a>
            </li>
            <li class="">
                <a title="Stock Report" href="#stock_data" data-toggle="tab">
                    <span><?= lang('Stock') ?></span>
                </a>
            </li>
            <li class="">
                <a title="Purchase Report" href="#purchase" onclick="ins_data" data-toggle="tab">
                    <span><?= lang('Purchase') ?></span>
                </a>
            </li>
            <li class="">
                <a href="#estimates"
                   data-toggle="tab"><?= lang('Estimates') ?><strong class="pull-right">
                        <?= (!empty($all_estimates_info) ? count($all_estimates_info) : null) ?></strong>
                </a>
            </li>
            <li class="<?= $active == 2 ? 'active' : '' ?> sub-var" style="margin-right: 0px; ">
                <a data-toggle="collapse" href="#project_reports" class="collapsed" aria-expanded="false">
                    <span><?= lang('report') ?></span>
                </a>
                <ul id="project_reports" class="nav s-menu collapse" aria-expanded="false" style="height: 0px;">
                    <li class="">
                        <a title="Expense Report" target="_blank"
                           href="<?= base_url() ?>admin/report/expense_report/project/<?= $task_details->project_id ?>">
                            <span>Expense Report</span></a>
                    </li>
                </ul>

            </li>

            <li class="<?= $active == 3 ? 'active' : '' ?>"><a href="#task_attachments"
                                                               data-toggle="tab"><?= lang('attachment') ?>
                    <strong
                            class="pull-right"><?= (!empty($project_files_info) ? count($project_files_info) : null) ?></strong></a>
            </li>
            <li class="<?= $active == 4 ? 'active' : '' ?>"><a href="#task_notes"
                                                               data-toggle="tab"><?= lang('notes') ?></a></li>
            <li class="<?= $active == 5 ? 'active' : '' ?>">
                <a href="#timesheet"
                   data-toggle="tab"><?= lang('timesheet') ?><strong
                            class="pull-right"><?= (!empty($total_timer) ? count($total_timer) : null) ?></strong>
                </a>
            </li>
            <?php if (!empty($sub_tasks)) {
                ?>
                <li class="<?= $active == 7 ? 'active' : '' ?>"><a href="#sub_tasks"
                                                                   data-toggle="tab"><?= lang('sub_tasks') ?><strong
                                class="pull-right"><?= (!empty($all_sub_tasks) ? count($all_sub_tasks) : null) ?></strong></a>
                </li>
            <?php } ?>
            <li class="<?= $active == 6 ? 'active' : '' ?>"><a href="#activities"
                                                               data-toggle="tab"><?= lang('activities') ?><strong
                            class="pull-right"></strong><strong
                            class="pull-right"><?= (!empty($activities_info) ? count($activities_info) : null) ?></strong></a>
            </li>

        </ul>
    </div>
    <?php
    if (!empty($task_details->client_id)) {
        $currency = $this->items_model->client_currency_symbol($task_details->client_id);
    } else {
        $currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
    }
    $all_task_info = $this->db->where('sub_task_id', $task_details->task_id)->order_by('task_id', 'DESC')->get('tbl_task')->result();
    $total_subtask = count($all_task_info);
    $completeds = $this->db->where('sub_task_id', $task_details->task_id)->where('task_status', 'completed')->get('tbl_task')->result();
    $completed_task = count($completeds);

    $billable_amount = 0;
    foreach ($completeds as $completed) {
        $billable_amount += $completed->task_hour * $completed->hourly_rate;
    }
    if ($task_details->task_status === 'completed') {
        $progress = 'progress-bar-success';
        $billable_amount = $task_details->task_hour * $task_details->hourly_rate;
    }

    $total_expense = $this->db->select_sum('amount')->where(array('type' => 'Expense'))->where_in('task_id', [...$task_ids, $task_details->task_id])->get('tbl_transactions')->row();
    //    $billable_expense = $this->db->select_sum('amount')->where(array('task_id' => $task_details->task_id, 'type' => 'Expense', 'billable' => 'Yes'))->get('tbl_transactions')->row();
    //    $not_billable_expense = $this->db->select_sum('amount')->where(array('task_id' => $task_details->task_id, 'type' => 'Expense', 'billable' => 'No'))->get('tbl_transactions')->row();
    $paid_expense = 0;
    $comment_type = 'tasks';

    ?>
    <div class="col-sm-10">
        <div class="tab-content" style="border: 0;padding:0;">
            <!-- Task Comments Panel Starts --->
            <div class="tab-pane <?= $active == 2 ? 'active' : '' ?>" id="task_comments"
                 style="position: relative;">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= lang('comments') ?></h3>
                    </div>
                    <div class="panel-body chat" id="chat-box">
                        <?php echo form_open(base_url("admin/tasks/save_comments"), array("id" => $comment_type . "-comment-form", "class" => "form-horizontal general-form", "enctype" => "multipart/form-data", "role" => "form")); ?>

                        <input type="hidden" name="task_id" value="<?php
                        if (!empty($task_details->task_id)) {
                            echo $task_details->task_id;
                        }
                        ?>" class="form-control">

                        <div class="form-group">
                            <div class="col-sm-12">
                                <?php
                                echo form_textarea(array(
                                    "id" => "comment_description",
                                    "name" => "comment",
                                    "class" => "form-control comment_description",
                                    "placeholder" => $task_details->task_name . ' ' . lang('comments'),
                                    "data-rule-required" => true,
                                    "rows" => 4,
                                    "data-msg-required" => lang("field_required"),
                                ));
                                ?>
                            </div>
                        </div>
                        <div id="new_comments_attachement">
                            <div class="form-group">
                                <div class="col-sm-12">
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
                                                    <input class="file-count-field" type="hidden" name="files[]"
                                                           value=""/>
                                                    <div
                                                            class="mb progress progress-striped upload-progress-sm active mt-sm"
                                                            role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                                            aria-valuenow="0">
                                                        <div class="progress-bar progress-bar-success" style="width:0%;"
                                                             data-dz-uploadprogress></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <button type="submit" id="file-save-button"
                                            class="btn btn-primary"><?= lang('post_comment') ?></button>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <?php echo form_close();
                        $comment_reply_type = 'tasks-reply';
                        ?>
                        <?php $this->load->view('admin/tasks/comments_list', array('comment_details' => $comment_details)) ?>
                        <script type="text/javascript">
                            $(document).ready(function () {
                                $('#file-save-button').on('click', function (e) {
                                    var ubtn = $(this);
                                    ubtn.html('Please wait...');
                                    ubtn.addClass('disabled');
                                });
                                $("#<?php echo $comment_type; ?>-comment-form").appForm({
                                    isModal: false,
                                    onSuccess: function (result) {
                                        $(".comment_description").val("");
                                        $(".dz-complete").remove();
                                        $('#file-save-button').removeClass("disabled").html('<?= lang('post_comment')?>');
                                        $(result.data).insertAfter("#<?php echo $comment_type; ?>-comment-form");
                                        toastr[result.status](result.message);
                                    }
                                });
                                var fileSerial = 0;
                                // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
                                var previewNode = document.querySelector("#file-upload-row");
                                previewNode.id = "";
                                var previewTemplate = previewNode.parentNode.innerHTML;
                                previewNode.parentNode.removeChild(previewNode);
                                Dropzone.autoDiscover = false;
                                var projectFilesDropzone = new Dropzone("#comments_file-dropzone", {
                                    url: "<?= base_url()?>admin/global_controller/upload_file",
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
                                            url: "<?= base_url()?>admin/global_controller/validate_project_file",
                                            data: {file_name: file.name, file_size: file.size},
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
                                            var newFileRow = "<div class='file-row pb pt10 b-b mb10'>"
                                                + "<div class='pb clearfix '><button type='button' class='btn btn-xs btn-danger pull-left mr remove-file'><i class='fa fa-times'></i></button> <input class='pull-left' type='file' name='manualFiles[]' /></div>"
                                                + "<div class='mb5 pb5'><input class='form-control description-field'  name='comment[]'  type='text' style='cursor: auto;' placeholder='<?php echo lang("comment") ?>' /></div>"
                                                + "</div>";
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
            </div>
            <!-- Task Details tab Starts -->

            <!-- Task Details tab Ends -->
            <div class="tab-pane <?= $active == 1 ? 'active' : '' ?>" id="task_details"
                 style="position: relative;">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php if (!empty($task_details->task_name)) echo $task_details->task_name; ?>
                            <div class="pull-right ml-sm">
                                <a data-toggle="tooltip" data-placement="top" title="<?= $title ?>"
                                   href="<?= base_url() ?>admin/projects/<?= $url ?>"
                                   class="btn-xs btn btn-<?= $btn ?>"><i class="fa fa-thumb-tack"></i></a>
                            </div>
                            <div class="pull-right ml-sm">
                                <a data-toggle="tooltip" data-placement="top" title="<?= lang('export_report') ?>"
                                   href="<?= base_url() ?>admin/tasks/export_report/<?= $task_details->task_id ?>"
                                   class="btn-xs btn btn-success"><i class="fa fa-file-pdf-o"></i></a>
                            </div>
                            <?php

                            if (!empty($can_edit) && !empty($edited)) {
                                ?>
                                <span class="btn-xs pull-right"><a
                                            href="<?= base_url() ?>admin/tasks/all_task/<?= $task_details->task_id ?>"><?= lang('edit') . ' ' . lang('task') ?></a>
                                </span>
                            <?php } ?>


                        </h3>
                    </div>
                    <?php
                    $p_category = $this->db->where('customer_group_id', $task_details->category_id)->get('tbl_customer_group')->row();
                    if (!empty($p_category)) {
                        $pc_name = $p_category->customer_group;
                    } else {
                        $pc_name = '-';
                    }


                    $p_contactor = $this->db->where('customer_group_id', $task_details->contactor_id)->get('tbl_customer_group')->row();
                    if (!empty($p_contactor)) {
                        $pcon_name = $p_contactor->customer_group;
                    } else {
                        $pcon_name = '-';
                    }

                    ?>
                    <div class="panel-body form-horizontal task_details">
                        <?php $task_details_view = config_item('task_details_view');
                        if (!empty($task_details_view) && $task_details_view == '2') {
                            ?>
                            <div class="row">
                                <div class="col-md-3 br">
                                    <p class="lead bb"></p>
                                    <form class="form-horizontal p-20">
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('task_name') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details->task_name)) {
                                                    echo $task_details->task_name;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('categories') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($pc_name)) {
                                                    echo $pc_name;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('tags') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details)) {
                                                    echo get_tags($task_details->tags, true);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('budget') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details)) {
                                                    echo get_tags($task_details->budget, true);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <!--                                        --><?php
                                        //                                        if (!empty($task_details->project_id)):
                                        //                                            $project_info = $this->db->where('project_id', $task_details->project_id)->get('tbl_project')->row();
                                        //                                            $milestones_info = $this->db->where('milestones_id', $task_details->milestones_id)->get('tbl_milestones')->row();
                                        //                                            ?>
                                        <!--                                            <div class="form-group ">-->
                                        <!--                                                <div class="col-sm-4"><strong>--><?php //= lang('project_name') ?>
                                        <!--                                                        :</strong></div>-->
                                        <!--                                                <div class="col-sm-8 ">-->
                                        <!--                                                    --><?php //if (!empty($project_info->project_name)) echo $project_info->project_name; ?>
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                            <div class="form-group">-->
                                        <!--                                                <div class="col-sm-4"><strong>--><?php //= lang('milestone') ?>
                                        <!--                                                        :</strong></div>-->
                                        <!--                                                <div class="col-sm-8 ">-->
                                        <!--                                                    --><?php //if (!empty($milestones_info->milestone_name)) echo $milestones_info->milestone_name; ?>
                                        <!--                                                </div>-->
                                        <!--                                            </div>-->
                                        <!--                                        --><?php //endif ?>
                                        <?php
                                        if (!empty($task_details->opportunities_id)):
                                            $opportunity_info = $this->db->where('opportunities_id', $task_details->opportunities_id)->get('tbl_opportunities')->row();
                                            ?>
                                            <div class="form-group">
                                                <div class="col-sm-4"><strong
                                                            class="mr-sm"><?= lang('opportunity_name') ?></strong></div>
                                                <div class="col-sm-8">
                                                    <?php if (!empty($opportunity_info->opportunity_name)) echo $opportunity_info->opportunity_name; ?>
                                                </div>
                                            </div>
                                        <?php endif ?>

                                        <?php
                                        if (!empty($task_details->leads_id)):
                                            $leads_info = $this->db->where('leads_id', $task_details->leads_id)->get('tbl_leads')->row();
                                            ?>
                                            <div class="form-group">
                                                <div class="col-sm-4"><strong
                                                            class="mr-sm"><?= lang('leads_name') ?></strong></div>
                                                <div class="col-sm-8">
                                                    <?php if (!empty($leads_info->lead_name)) echo $leads_info->lead_name; ?>
                                                </div>
                                            </div>
                                        <?php endif ?>

                                        <?php
                                        if (!empty($task_details->bug_id)):
                                            $bugs_info = $this->db->where('bug_id', $task_details->bug_id)->get('tbl_bug')->row();
                                            ?>
                                            <div class="form-group">
                                                <div class="col-sm-4"><strong
                                                            class="mr-sm"><?= lang('bug_title') ?></strong></div>
                                                <div class="col-sm-8">
                                                    <?php if (!empty($bugs_info->bug_title)) echo $bugs_info->bug_title; ?>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                        <?php
                                        if (!empty($task_details->goal_tracking_id)):
                                            $goal_tracking_info = $this->db->where('goal_tracking_id', $task_details->goal_tracking_id)->get('tbl_goal_tracking')->row();
                                            ?>
                                            <div class="form-group">
                                                <div class="col-sm-4"><strong
                                                            class="mr-sm"><?= lang('goal_tracking') ?></strong></div>
                                                <div class="col-sm-8">
                                                    <?php if (!empty($goal_tracking_info->subject)) echo $goal_tracking_info->subject; ?>
                                                </div>
                                            </div>
                                        <?php endif ?>
                                        <?php
                                        if (!empty($task_details->sub_task_id)):
                                            $sub_task = $this->db->where('task_id', $task_details->sub_task_id)->get('tbl_task')->row();
                                            ?>
                                            <div class="form-group">
                                                <div class="col-sm-4"><strong
                                                            class="mr-sm"><?= lang('sub_tasks') ?></strong></div>
                                                <div class="col-sm-8">
                                                    <?php if (!empty($sub_task->task_name)) echo $sub_task->task_name; ?>
                                                </div>
                                            </div>
                                        <?php endif ?>

                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('start_date') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details->task_start_date)) {
                                                    echo strftime(config_item('date_format'), strtotime($task_details->task_start_date));
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <?php
                                        $due_date = $task_details->due_date;
                                        $due_time = strtotime($due_date);
                                        $current_time = strtotime(date('Y-m-d'));
                                        if ($current_time > $due_time && $task_details->task_status != 'completed') {
                                            $text = 'text-danger';
                                        } else {
                                            $text = null;
                                        }
                                        ?>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong class="<?= $text ?>"><?= lang('due_date') ?>
                                                    :</strong></div>
                                            <div class="col-sm-8 <?= $text ?>">
                                                <?php
                                                if (!empty($task_details->due_date)) {
                                                    echo strftime(config_item('date_format'), strtotime($task_details->due_date));
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('task_status') ?>
                                                    :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                $disabled = null;
                                                if ($task_details->task_status == 'completed') {
                                                    $label = 'success';
                                                    $disabled = 'disabled';
                                                } elseif ($task_details->task_status == 'not_started') {
                                                    $label = 'info';
                                                } elseif ($task_details->task_status == 'deferred') {
                                                    $label = 'danger';
                                                } else {
                                                    $label = 'warning';
                                                }
                                                ?>
                                                <div
                                                        class="label label-<?= $label ?>  "><?= lang($task_details->task_status) ?></div>
                                                <?php
                                                ?>
                                                <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                                    <div class="btn-group">
                                                        <button class="btn btn-xs btn-success dropdown-toggle"
                                                                data-toggle="dropdown">
                                                            <?= lang('change') ?>
                                                            <span class="caret"></span></button>
                                                        <ul class="dropdown-menu animated zoomIn">
                                                            <li>
                                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/not_started' ?>"><?= lang('not_started') ?></a>
                                                            </li>
                                                            <li>
                                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/in_progress' ?>"><?= lang('in_progress') ?></a>
                                                            </li>
                                                            <li>
                                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/completed' ?>"><?= lang('completed') ?></a>
                                                            </li>
                                                            <li>
                                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/deferred' ?>"><?= lang('deferred') ?></a>
                                                            </li>
                                                            <li>
                                                                <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/waiting_for_someone' ?>"><?= lang('waiting_for_someone') ?></a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="col-md-3 br">
                                    <p class="lead bb"></p>
                                    <form class="form-horizontal p-20">
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('timer_status') ?>:</strong></div>
                                            <div class="col-sm-8">
                                                <?php if (timer_status('tasks', $task_details->task_id, 'on')) { ?>
                                                    <span class="label label-success"><?= lang('on') ?></span>

                                                    <a class="btn btn-xs btn-danger "
                                                       href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><?= lang('stop_timer') ?> </a>
                                                <?php } else {
                                                    ?>
                                                    <span class="label label-danger"><?= lang('off') ?></span>
                                                    <?php $this_permission = $this->tasks_model->can_action('tbl_task', 'view', array('task_id' => $task_details->task_id), true);
                                                    if (!empty($this_permission)) { ?>
                                                        <a class="btn btn-xs btn-success <?= $disabled ?>"
                                                           href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><?= lang('start_timer') ?> </a>
                                                    <?php }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('project_hourly_rate') ?> :</strong>
                                            </div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details->hourly_rate)) {
                                                    echo $task_details->hourly_rate;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('created_by') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details->created_by)) {
                                                    echo $this->db->where('user_id', $task_details->created_by)->get('tbl_account_details')->row()->fullname;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4">
                                                <small><?= lang('created_date') ?> :</small>
                                            </div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($task_details->due_date)) {
                                                    echo strftime(config_item('date_format'), strtotime($task_details->task_created_date)) . ' ' . display_time($task_details->task_created_date);
                                                }
                                                ?>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div class="col-md-3 br">
                                    <p class="lead bb"></p>
                                    <form class="form-horizontal p-20">

                                        <?php $show_custom_fields = custom_form_label(3, $task_details->task_id);

                                        if (!empty($show_custom_fields)) {
                                            foreach ($show_custom_fields as $c_label => $v_fields) {
                                                if (!empty($v_fields)) {
                                                    ?>
                                                    <div class="form-group">
                                                        <div class="col-sm-4"><strong><?= $c_label ?> :</strong></div>
                                                        <div class="col-sm-8">
                                                            <?= $v_fields ?>
                                                        </div>
                                                    </div>
                                                <?php }
                                            }
                                        }
                                        ?>

                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('estimated_hour') ?>
                                                    :</strong></div>
                                            <div class="col-sm-8 ">
                                                <?php if (!empty($task_details->task_hour)) echo $task_details->task_hour; ?> <?= lang('hours') ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('billable') ?>
                                                    :</strong></div>
                                            <div class="col-sm-8 ">
                                                <?php if (!empty($task_details->billable)) {
                                                    if ($task_details->billable == 'Yes') {
                                                        $billable = 'success';
                                                        $text = lang('yes');
                                                    } else {
                                                        $billable = 'danger';
                                                        $text = lang('no');
                                                    };
                                                } else {
                                                    $billable = '';
                                                    $text = '-';
                                                }; ?>
                                                <strong class="label label-<?= $billable ?>">
                                                    <?= $text ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('participants') ?>
                                                    :</strong></div>
                                            <div class="col-sm-8 ">
                                                <?php
                                                if ($task_details->permission != 'all') {
                                                    $get_permission = json_decode($task_details->permission);
                                                    if (is_object($get_permission)) :
                                                        foreach ($get_permission as $permission => $v_permission) :
                                                            $user_info = $this->db->where(array('user_id' => $permission))->get('tbl_users')->row();
                                                            if ($user_info->role_id == 1) {
                                                                $label = 'circle-danger';
                                                            } else {
                                                                $label = 'circle-success';
                                                            }
                                                            $profile_info = $this->db->where(array('user_id' => $permission))->get('tbl_account_details')->row();
                                                            ?>


                                                            <a href="#" data-toggle="tooltip" data-placement="top"
                                                               title="<?= $profile_info->fullname ?>"><img
                                                                        src="<?= base_url() . $profile_info->avatar ?>"
                                                                        class="img-circle img-xs" alt="">
                                                                <span class="custom-permission circle <?= $label ?>  circle-lg"></span>
                                                            </a>
                                                        <?php
                                                        endforeach;
                                                    endif;
                                                } else { ?><strong><?= lang('everyone') ?></strong>
                                                    <i
                                                            title="<?= lang('permission_for_all') ?>"
                                                            class="fa fa-question-circle" data-toggle="tooltip"
                                                            data-placement="top"></i>

                                                    <?php
                                                }
                                                ?>
                                                <?php
                                                $can_edit = $this->tasks_model->can_action('tbl_task', 'edit', array('task_id' => $task_details->task_id));
                                                if (!empty($can_edit) && !empty($edited)) {
                                                    ?>
                                                    <span data-placement="top" data-toggle="tooltip"
                                                          title="<?= lang('add_more') ?>">
                                            <a data-toggle="modal" data-target="#myModal"
                                               href="<?= base_url() ?>admin/tasks/update_users/<?= $task_details->task_id ?>"
                                               class="text-default ml"><i class="fa fa-plus"></i></a>
                                                </span>
                                                    <?php
                                                }
                                                ?>

                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-4"><strong><?= lang('contactor') ?> :</strong></div>
                                            <div class="col-sm-8">
                                                <?php
                                                if (!empty($pcon_name)) {
                                                    echo $pcon_name;
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-3">
                                    <p class="lead bb"></p>
                                    <form class="form-horizontal p-20">

                                        <?php

                                        $task_time = $this->tasks_model->task_spent_time_by_id($task_details->task_id);
                                        ?>
                                        <?= $this->tasks_model->get_time_spent_result($task_time) ?>
                                        <?php
                                        if (!empty($task_details->billable) && $task_details->billable == 'Yes') {
                                            $total_time = $task_time / 3600;
                                            $total_cost = $total_time * $task_details->hourly_rate;
                                            $currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
                                            ?>
                                            <h2 class="text-center"><?= lang('total_bill') ?>
                                                : <?= display_money($total_cost, $currency->symbol) ?></h2>
                                        <?php }
                                        $estimate_hours = $task_details->task_hour;
                                        $percentage = $this->tasks_model->get_estime_time($estimate_hours);

                                        if ($task_time < $percentage) {
                                            $total_time = $percentage - $task_time;
                                            $worked = '<storng style="font-size: 15px;"  class="required">' . lang('left_works') . '</storng>';
                                        } else {
                                            $total_time = $task_time - $percentage;
                                            $worked = '<storng style="font-size: 15px" class="required">' . lang('extra_works') . '</storng>';
                                        }

                                        ?>
                                        <div class="text-center">
                                            <div class="">
                                                <?= $worked ?>
                                            </div>
                                            <div class="">
                                                <?= $this->tasks_model->get_spent_time($total_time) ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-md-6 br ">
                                    <p class="lead bb"></p>
                                    <form class="form-horizontal p-20">
                                        <blockquote style="font-size: 12px;word-wrap: break-word;"><?php
                                            if (!empty($task_details->task_description)) {
                                                echo $task_details->task_description;
                                            }
                                            ?></blockquote>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <p class="lead bb"></p>
                                    <form class="form-horizontal p-20">
                                        <div class="col-sm-12">
                                            <strong><?= lang('completed') ?>:</strong>
                                        </div>
                                        <div class="col-sm-12">
                                            <?php
                                            $task_progress = 0;
                                            if (!empty($total_subtask)) {
                                                if ($total_subtask !== 0) {
                                                    $task_progress = $completed_task / $total_subtask * 100;
                                                }
                                                if ($task_progress > 100) {
                                                    $task_progress = 100;
                                                }
                                                if ($task_progress < 49) {
                                                    $progress = 'progress-bar-danger';
                                                } elseif ($task_progress < 79) {
                                                    $progress = 'progress-bar-primary';
                                                } else {
                                                    $progress = 'progress-bar-success';
                                                }
                                            } else {
                                                $progress = 'progress-bar-danger';
                                                $task_progress = 0;
                                            }

                                            if ($total_subtask <= 0 || $task_details->calculate_progress !== 'through_sub_tasks') {
                                                $progress = 'progress-bar-success';
                                                $task_progress = $task_details->task_progress;
                                            }

                                            if ($task_details->task_status === 'completed') {
                                                $progress = 'progress-bar-success';
                                                $task_progress = 100;
                                            }
                                            ?>
                                            <span class="">
                                <div class="mt progress progress-striped ">
                                    <div class="progress-bar <?= $progress ?> " data-toggle="tooltip"
                                         data-original-title="<?= $task_progress ?>%"
                                         style="width: <?= $task_progress ?>%"></div>
                                </div>
                                </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="form-group col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('task_name') ?>
                                        :</strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static"><?= ($task_details->task_name) ?></p>
                                </div>
                            </div>

                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-4"><strong><?= lang('categories') ?>
                                        :</strong></label>
                                <div class="col-sm-8">
                                    <p class="form-control-static"><?php if (!empty($pc_name)) echo $pc_name; ?></p>
                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('budget') ?>
                                        : </strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static" style="padding-bottom: 6px"><strong><?php
                                            echo display_money($task_details->budget ?? 0);
                                            ?></strong></p>
                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('tags') ?>
                                        :</strong></label>
                                <div class="col-sm-7">
                                    <p class="form-control-static"><?php
                                        if (!empty($task_details->tags)) {
                                            echo get_tags($task_details->tags, true);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?></p>
                                </div>
                            </div>

                            <div class="form-group col-sm-6">
                                <label class="control-label col-sm-4"><strong><?= lang('task_status') ?>
                                        :</strong></label>
                                <div class="pull-left mt">
                                    <?php
                                    $disabled = null;
                                    if ($task_details->task_status == 'completed') {
                                        $label = 'success';
                                        $disabled = 'disabled';
                                    } elseif ($task_details->task_status == 'not_started') {
                                        $label = 'info';
                                    } elseif ($task_details->task_status == 'deferred') {
                                        $label = 'danger';
                                    } else {
                                        $label = 'warning';
                                    }
                                    ?>
                                    <p class="form-control-static label label-<?= $label ?>  "><?= lang($task_details->task_status) ?></p>
                                </div>
                                <?php if (!empty($can_edit) && !empty($edited)) { ?>
                                    <div class="col-sm-1 mt">
                                        <div class="btn-group">
                                            <button class="btn btn-xs btn-success dropdown-toggle"
                                                    data-toggle="dropdown">
                                                <?= lang('change') ?>
                                                <span class="caret"></span></button>
                                            <ul class="dropdown-menu animated zoomIn">
                                                <li>
                                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/not_started' ?>"><?= lang('not_started') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/in_progress' ?>"><?= lang('in_progress') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/completed' ?>"><?= lang('completed') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/deferred' ?>"><?= lang('deferred') ?></a>
                                                </li>
                                                <li>
                                                    <a href="<?= base_url() ?>admin/tasks/change_status/<?= $task_details->task_id . '/waiting_for_someone' ?>"><?= lang('waiting_for_someone') ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-4"><strong><?= lang('timer_status') ?>
                                        :</strong></label>
                                <div class="col-sm-8 mt">
                                    <?php if (timer_status('tasks', $task_details->task_id, 'on')) { ?>
                                        <span class="label label-success"><?= lang('on') ?></span>

                                        <a class="btn btn-xs btn-danger "
                                           href="<?= base_url() ?>admin/tasks/tasks_timer/off/<?= $task_details->task_id ?>"><?= lang('stop_timer') ?> </a>
                                    <?php } else {
                                        ?>
                                        <span class="label label-danger"><?= lang('off') ?></span>
                                        <?php $this_permission = $this->tasks_model->can_action('tbl_task', 'view', array('task_id' => $task_details->task_id), true);
                                        if (!empty($this_permission)) { ?>
                                            <a class="btn btn-xs btn-success <?= $disabled ?>"
                                               href="<?= base_url() ?>admin/tasks/tasks_timer/on/<?= $task_details->task_id ?>"><?= lang('start_timer') ?> </a>
                                        <?php }
                                    }
                                    ?>
                                </div>
                            </div>


                            <?php
                            if (!empty($task_details->project_id)):
                                $project_info = $this->db->where('project_id', $task_details->project_id)->get('tbl_project')->row();
                                $milestones_info = $this->db->where('milestones_id', $task_details->milestones_id)->get('tbl_milestones')->row();
                                ?>
                                <div class="form-group  col-sm-6">
                                    <label class="control-label col-sm-5"><strong><?= lang('project_name') ?>
                                            :</strong></label>
                                    <div class="col-sm-7 ">
                                        <p class="form-control-static"><?php if (!empty($project_info->project_name)) echo $project_info->project_name; ?></p>
                                    </div>
                                </div>
                                <!--                                <div class="form-group  col-sm-6">-->
                                <!--                                    <label class="control-label col-sm-4"><strong>--><?php //= lang('milestone')
                                ?>
                                <!--                                            :</strong></label>-->
                                <!--                                    <div class="col-sm-8 ">-->
                                <!--                                        <p class="form-control-static">--><?php //if (!empty($milestones_info->milestone_name)) echo $milestones_info->milestone_name;
                                ?><!--</p>-->
                                <!--                                    </div>-->
                                <!--                                </div>-->
                            <?php endif ?>
                            <?php
                            if (!empty($task_details->opportunities_id)):
                                $opportunity_info = $this->db->where('opportunities_id', $task_details->opportunities_id)->get('tbl_opportunities')->row();
                                ?>
                                <div class="form-group  col-sm-10">
                                    <label class="control-label col-sm-3 "><strong
                                                class="mr-sm"><?= lang('opportunity_name') ?></strong></label>
                                    <div class="col-sm-8 " style="margin-left: -5px;">
                                        <p class="form-control-static"><?php if (!empty($opportunity_info->opportunity_name)) echo $opportunity_info->opportunity_name; ?></p>
                                    </div>
                                </div>
                            <?php endif ?>

                            <?php
                            if (!empty($task_details->leads_id)):
                                $leads_info = $this->db->where('leads_id', $task_details->leads_id)->get('tbl_leads')->row();
                                ?>
                                <div class="form-group  col-sm-10">
                                    <label class="control-label col-sm-3 "><strong
                                                class="mr-sm"><?= lang('leads_name') ?></strong></label>
                                    <div class="col-sm-8 " style="margin-left: -5px;">
                                        <p class="form-control-static"><?php if (!empty($leads_info->lead_name)) echo $leads_info->lead_name; ?></p>
                                    </div>
                                </div>
                            <?php endif ?>

                            <?php
                            if (!empty($task_details->bug_id)):
                                $bugs_info = $this->db->where('bug_id', $task_details->bug_id)->get('tbl_bug')->row();
                                ?>
                                <div class="form-group  col-sm-10">
                                    <label class="control-label col-sm-3 "><strong
                                                class="mr-sm"><?= lang('bug_title') ?></strong></label>
                                    <div class="col-sm-8 " style="margin-left: -5px;">
                                        <p class="form-control-static"><?php if (!empty($bugs_info->bug_title)) echo $bugs_info->bug_title; ?></p>
                                    </div>
                                </div>
                            <?php endif ?>
                            <?php
                            if (!empty($task_details->goal_tracking_id)):
                                $goal_tracking_info = $this->db->where('goal_tracking_id', $task_details->goal_tracking_id)->get('tbl_goal_tracking')->row();
                                ?>
                                <div class="form-group  col-sm-10">
                                    <label class="control-label col-sm-3 "><strong
                                                class="mr-sm"><?= lang('goal_tracking') ?></strong></label>
                                    <div class="col-sm-8 " style="margin-left: -5px;">
                                        <p class="form-control-static"><?php if (!empty($goal_tracking_info->subject)) echo $goal_tracking_info->subject; ?></p>
                                    </div>
                                </div>
                            <?php endif ?>
                            <?php
                            if (!empty($task_details->sub_task_id)):
                                $sub_task = $this->db->where('task_id', $task_details->sub_task_id)->get('tbl_task')->row();
                                ?>
                                <div class="form-group  col-sm-10">
                                    <label class="control-label col-sm-3 "><strong
                                                class="mr-sm"><?= lang('sub_tasks') ?></strong></label>
                                    <div class="col-sm-8 " style="margin-left: -5px;">
                                        <p class="form-control-static"><?php if (!empty($sub_task->task_name)) echo $sub_task->task_name; ?></p>
                                    </div>
                                </div>
                            <?php endif ?>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('start_date') ?>
                                        :</strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static"><?php
                                        if (!empty($task_details->task_start_date)) {
                                            echo strftime(config_item('date_format'), strtotime($task_details->task_start_date));
                                        }
                                        ?></p>
                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <?php
                                $due_date = $task_details->due_date;
                                $due_time = strtotime($due_date);
                                $current_time = strtotime(date('Y-m-d'));
                                if ($current_time > $due_time) {
                                    $text = 'text-danger';
                                } else {
                                    $text = null;
                                }
                                ?>

                                <label class="control-label col-sm-4"><strong
                                            class="<?= $text ?>"><?= lang('due_date') ?>
                                        :</strong></label>
                                <div class="col-sm-8 ">
                                    <p class="form-control-static"><?php
                                        if (!empty($task_details->due_date)) {
                                            echo strftime(config_item('date_format'), strtotime($task_details->due_date));
                                        }
                                        ?></p>

                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('created_by') ?>
                                        :</strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static"><?php
                                        if (!empty($task_details->created_by)) {
                                            echo $this->db->where('user_id', $task_details->created_by)->get('tbl_account_details')->row()->fullname;
                                        }
                                        ?></p>

                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-4"><strong><?= lang('created_date') ?>
                                        :</strong></label>
                                <div class="col-sm-8 ">
                                    <p class="form-control-static"><?php
                                        if (!empty($task_details->due_date)) {
                                            echo strftime(config_item('date_format'), strtotime($task_details->task_created_date));
                                        }
                                        ?></p>

                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('project_hourly_rate') ?>
                                        :</strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static"><?php
                                        if (!empty($task_details->hourly_rate)) {
                                            echo $task_details->hourly_rate;
                                        }
                                        ?></p>
                                </div>
                            </div>

                            <?php $show_custom_fields = custom_form_label(3, $task_details->task_id);

                            if (!empty($show_custom_fields)) {
                                foreach ($show_custom_fields as $c_label => $v_fields) {
                                    if (!empty($v_fields)) {
                                        if (count($v_fields) == 1) {
                                            $col = 'col-sm-10';
                                            $sub_col = 'col-sm-3';
                                            $style = 'padding-left:8px';
                                        } else {
                                            $col = 'col-sm-6';
                                            $sub_col = 'col-sm-5';
                                            $style = null;
                                        }

                                        ?>
                                        <div class="form-group  <?= $col ?>" style="<?= $style ?>">
                                            <label class="control-label <?= $sub_col ?>"><strong><?= $c_label ?>
                                                    :</strong></label>
                                            <div class="col-sm-7 ">
                                                <p class="form-control-static">
                                                    <strong><?= $v_fields ?></strong>
                                                </p>
                                            </div>
                                        </div>
                                    <?php }
                                }
                            }
                            ?>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('estimated_hour') ?>
                                        :</strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static">
                                        <strong><?php if (!empty($task_details->task_hour)) echo $task_details->task_hour; ?> <?= lang('hours') ?></strong>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-5"><strong><?= lang('billable') ?>
                                        :</strong></label>
                                <div class="col-sm-7 ">
                                    <p class="form-control-static">
                                        <?php if (!empty($task_details->billable)) {
                                            if ($task_details->billable == 'Yes') {
                                                $billable = 'success';
                                                $text = lang('yes');
                                            } else {
                                                $billable = 'danger';
                                                $text = lang('no');
                                            };
                                        } else {
                                            $billable = '';
                                            $text = '-';
                                        }; ?>
                                        <strong class="label label-<?= $billable ?>">
                                            <?= $text ?>
                                        </strong>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <label class="control-label col-sm-4"><strong><?= lang('participants') ?>
                                        :</strong></label>
                                <div class="col-sm-8 ">
                                    <?php
                                    if (!empty($task_details->permission) && $task_details->permission != 'all') {
                                        $get_permission = json_decode($task_details->permission);
                                        if (is_object($get_permission) && !empty($get_permission)) :
                                            foreach ($get_permission as $permission => $v_permission) :
                                                $user_info = $this->db->where(array('user_id' => $permission))->get('tbl_users')->row();
                                                if (!empty($user_info)) {
                                                    if ($user_info->role_id == 1) {
                                                        $label = 'circle-danger';
                                                    } else {
                                                        $label = 'circle-success';
                                                    }
                                                    $profile_info = $this->db->where(array('user_id' => $permission))->get('tbl_account_details')->row();
                                                    ?>


                                                    <a href="#" data-toggle="tooltip" data-placement="top"
                                                       title="<?= $profile_info->fullname ?>"><img
                                                                src="<?= base_url() . $profile_info->avatar ?>"
                                                                class="img-circle img-xs" alt="">
                                                        <span class="custom-permission circle <?= $label ?>  circle-lg"></span>
                                                    </a>
                                                    <?php
                                                }
                                            endforeach;
                                        endif;
                                    } else { ?>
                                    <p class="form-control-static"><strong><?= lang('everyone') ?></strong>
                                        <i
                                                title="<?= lang('permission_for_all') ?>"
                                                class="fa fa-question-circle" data-toggle="tooltip"
                                                data-placement="top"></i>

                                        <?php
                                        }
                                        ?>
                                        <?php
                                        $can_edit = $this->tasks_model->can_action('tbl_task', 'edit', array('task_id' => $task_details->task_id));
                                        if (!empty($can_edit) && !empty($edited)) {
                                        ?>
                                        <span data-placement="top" data-toggle="tooltip"
                                              title="<?= lang('add_more') ?>">
                                            <a data-toggle="modal" data-target="#myModal"
                                               href="<?= base_url() ?>admin/tasks/update_users/<?= $task_details->task_id ?>"
                                               class="text-default ml"><i class="fa fa-plus"></i></a>
                                                </span>
                                    </p>
                                <?php
                                }
                                ?>

                                </div>
                            </div>
                            <div class="form-group  col-sm-6">
                                <div class="control-label col-sm-5"><strong><?= lang('contactor') ?> :</strong></div>
                                <div class="col-sm-7">
                                    <p class="form-control-static"><strong>
                                            <?php
                                            if (!empty($pcon_name)) {
                                                echo $pcon_name;
                                            }
                                            ?>
                                        </strong>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group  col-sm-10">
                                <label class="control-label col-sm-3 "><strong class="mr-sm"><?= lang('completed') ?>
                                        :</strong></label>
                                <div class="col-sm-9 " style="margin-left: -5px;">
                                    <?php
                                    $task_progress = 0;
                                    if (!empty($total_subtask)) {
                                        if ($total_subtask !== 0) {
                                            $task_progress = $completed_task / $total_subtask * 100;
                                        }
                                        if ($task_progress > 100) {
                                            $task_progress = 100;
                                        }
                                        if ($task_progress < 49) {
                                            $progress = 'progress-bar-danger';
                                        } elseif ($task_progress < 79) {
                                            $progress = 'progress-bar-primary';
                                        } else {
                                            $progress = 'progress-bar-success';
                                        }
                                    } else {
                                        $progress = 'progress-bar-danger';
                                        $task_progress = 0;
                                    }
                                    if ($total_subtask <= 0 || $task_details->calculate_progress !== 'through_sub_tasks') {
                                        $progress = 'progress-bar-success';
                                        $task_progress = $task_details->task_progress;
                                    }

                                    if ($task_details->task_status === 'completed') {
                                        $progress = 'progress-bar-success';
                                        $task_progress = 100;
                                    }
                                    ?>
                                    <span class="">
                                        <div class="mt progress progress-striped ">
                                            <div class="progress-bar <?= $progress ?> " data-toggle="tooltip"
                                                 data-original-title="<?= $task_progress ?>%"
                                                 style="width: <?= $task_progress ?>%"></div>
                                        </div>
                                    </span>
                                </div>

                            </div>
                            <div class="form-group col-sm-12">
                                <?php

                                $task_time = $this->tasks_model->task_spent_time_by_id($task_details->task_id);
                                ?>
                                <?= $this->tasks_model->get_time_spent_result($task_time) ?>

                                <?php
                                if (!empty($task_details->billable) && $task_details->billable == 'Yes' || 1) {
                                    $total_time = $task_time / 3600;
                                    $total_cost = $total_time * $task_details->hourly_rate;
                                    $currency = $this->db->where('code', config_item('default_currency'))->get('tbl_currencies')->row();
                                    ?>
                                    <div class="col-sm-12 text-center">
                                        <p class="p0 m0 text-warning"
                                           style="background: #d30000;    color: white;    font-size: 22px;">
                                            <?php
                                            $taSeckkId = $task_details->sub_task_id ?? $task_details->task_id;
                                            $sub_task_ids = get_all_sub_tasks($taSeckkId);
                                            $total_subtask_budget = $this->db->select_sum('budget')->where_in('task_id', $task_ids)->where_not_in('task_id', [$taSeckkId])->get('tbl_task')->row();
                                            $taskdetails = $this->db->where('task_id', $taSeckkId)->get('tbl_task')->row();


                                            if ($taskdetails->budget > 0) {
                                                $percentage = ($total_subtask_budget->budget ?? 0 / ($taskdetails->budget)) * 100;
                                                if ($total_subtask_budget->budget == $taskdetails->budget) {
                                                    $ddd = $total_subtask_budget->budget - $taskdetails->budget;
                                                    echo "<strong>You have 100 % Of Budget Used for sub-task</strong>";
                                                }
                                                if ($total_subtask_budget->budget > $taskdetails->budget) {
                                                    $ddd = $total_subtask_budget->budget - $taskdetails->budget;
                                                    echo "<strong>Need to update task Budget</strong>";
                                                } else if ($percentage >= 90 && $percentage < 100) {
                                                    echo "<strong>You have $percentage % Of Budget Used for sub-task</strong>";
                                                }
                                            }
                                            ?>
                                            <?php if (($task_details->budget - $total_expense->amount) < 1) { ?>
                                                <strong>You have expensed more than your budget</strong>
                                            <?php } ?>
                                        </p>
                                        <p class="p0 m0">
                                            <strong><?= lang('total') . ' Task ' . lang('budget') ?></strong>: <?= display_money($task_details->budget, $currency->symbol) ?>
                                        </p>
                                        <p class="p0 m0">
                                            <strong><?= lang('total') . ' Sub Task ' . lang('budget') ?></strong>: <?= display_money($total_subtask_budget->budget ?? 0, $currency->symbol) ?>
                                        </p>
                                        <p class="p0 m0">
                                            <strong><?= lang('total') . ' Task ' . lang('expense') ?></strong>: <?= display_money($total_expense->amount, $currency->symbol) ?>
                                        </p>
                                        <p class="p0 m0" <?php if (($task_details->budget - $total_expense->amount) < 1) { ?>  style="color: red;font-size :22px"   <?php } ?> >
                                            <strong><?= lang('total') . ' Task ' . lang('balance') ?></strong>: <?= display_money($task_details->budget - $total_expense->amount, $currency->symbol) ?>
                                        </p>
                                        <!--                                        <p class="p0 m0">-->
                                        <!--                                            <strong>-->
                                        <?php //= lang('not_billable') . ' ' . lang('expense') ?><!--</strong>: --><?php //= display_money($not_billable_expense->amount, $currency->symbol) ?>
                                        <!--                                        </p>-->
                                        <!--                                        <p class="p0 m0">-->
                                        <!--                                            <strong>-->
                                        <?php //= lang('billed') . ' ' . lang('expense') ?><!--</strong>: --><?php //= display_money($paid_expense, $currency->symbol) ?>
                                        <!--                                        </p>-->
                                        <!--                                        <p class="p0 m0">-->
                                        <!--                                            <strong>-->
                                        <?php //= lang('unbilled') . ' ' . lang('expense') ?><!--</strong>: --><?php //= display_money($task_details->task_hour * $task_details->hourly_rate - $paid_expense, $currency->symbol) ?>
                                        <!--                                        </p>-->
                                    </div>
                                    <h2 class="text-center"><?= lang('total_bill') ?>
                                        : <?= display_money(($task_details->task_hour * $task_details->hourly_rate), $currency->symbol) ?></h2>
                                <?php }
                                $estimate_hours = $task_details->task_hour;
                                $percentage = $this->tasks_model->get_estime_time($estimate_hours);

                                if ($task_time < $percentage) {
                                    $total_time = $percentage - $task_time;
                                    $worked = '<storng style="font-size: 15px;"  class="required">' . lang('left_works') . '</storng>';
                                } else {
                                    $total_time = $task_time - $percentage;
                                    $worked = '<storng style="font-size: 15px" class="required">' . lang('extra_works') . '</storng>';
                                }

                                ?>
                                <div class="col-sm-12 mt-lg">
                                    <?php
                                    try {
                                        $now = time();
                                        $task_start_date = strtotime($task_details->task_start_date);
                                        $task_due_date = strtotime($task_details->due_date);
                                        $totalDays = round(($task_due_date - $task_start_date) / 3600 / 24);
                                        $TotalGone = $totalDays;
                                        $tprogress = 100;
                                        if ($task_start_date < time() && $task_due_date > time()) {
                                            $TotalGone = round(($task_due_date - time()) / 3600 / 24);
                                            $tprogress = $TotalGone / $totalDays * 100;
                                        }
                                        if ($task_due_date < time()) {
                                            $TotalGone = 0;
                                            $tprogress = 0;
                                        }
                                        if (strtotime(date('Y-m-d')) > strtotime($task_details->due_date . '00:00')) {
                                            $lang = lang('days_gone');
                                        } else {
                                            $lang = lang('days_left');
                                        }
                                        if ($tprogress < 50) {
                                            $p_bar = 'bar-success';
                                        } else {
                                            $p_bar = 'bar-danger';
                                        }
                                    } catch (Exception $e) {
                                        $totalDays = 0;
                                        $TotalGone = 0;
                                        $tprogress = 0;
                                        $lang = lang('days_left');
                                        $p_bar = 'bar-danger';
                                    }


                                    ?>
                                    <div class="col-sm-4">
                                        <strong><?= $TotalGone . ' / ' . $totalDays . ' ' . $lang . ' (' . round($tprogress, 2) . '% )'; ?></strong>
                                        <div class="mt progress progress-striped progress-xs">
                                            <div class="progress-bar progress-<?= $p_bar ?> " data-toggle="tooltip"
                                                 data-original-title="<?= round($tprogress, 2) ?>%"
                                                 style="width: <?= round($tprogress, 2) ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="text-center">
                                            <div class="">
                                                <?= $worked ?>
                                            </div>
                                            <div class="">
                                                <?= $this->tasks_model->get_spent_time($total_time) ?>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-sm-4">
                                        <strong><?= $completed_task . ' / ' . $total_subtask . ' ' . lang('open') . ' ' . lang('tasks') . ' (' . round($task_progress, 2) . '% )'; ?> </strong>
                                        <div class="mt progress progress-striped progress-xs">
                                            <div class="progress-bar <?= $progress ?> " data-toggle="tooltip"
                                                 data-original-title="<?= $task_progress ?>%"
                                                 style="width: <?= $task_progress ?>%"></div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="col-sm-12">
                                <blockquote
                                        style="font-size: 12px; margin-top: 5px;word-wrap: break-word;width: 100%"><?php if (!empty($task_details->task_description)) echo $task_details->task_description; ?></blockquote>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
            <!-- Task Comments Panel Ends--->
            <div class="tab-pane " id="stock_data" style="position: relative;">

                <div class="box" style="border: none; " data-collapsed="0">
                    <div class="btn-group pull-right btn-with-tooltip-group" data-toggle="tooltip"
                         data-title="<?php echo lang('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left"
                            style="width:300px;<?php if (!empty($type) && $type == 'category') {
                                echo 'display:block';
                            } ?>">
                            <li class="<?php
                            if (empty($type)) {
                                echo 'active';
                            } ?>">
                                <a target="_blank"
                                   href="<?= base_url() ?>admin/projects/project_details/<?= $task_details->project_id ?>/10"><?php echo lang('all'); ?></a>
                            </li>
                            <li class="divider"></li>

                        </ul>
                    </div>
                    <div class="nav-tabs-custom">
                        <!-- Tabs within a box -->
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#manage_stock1" data-toggle="tab"><?= lang('Stock') ?></a>
                            </li>
                            <li class=""><a href="#stock_use" data-toggle="tab"><?= lang('Use Stock') ?></a>
                            </li>
                            <li class=""><a href="#stock_transfer"
                                            data-toggle="tab"><?= lang('Stock Transfer') ?></a>
                            </li>
                            <li class=""><a href="#stock-expense-and-transfer-history"
                                            data-toggle="tab"><?= lang('Stock Uses & Transfer History') ?></a>
                            </li>
                            <li class="">
                                <a target="_blank"
                                   href="<?= base_url() ?>admin/items/items_list/<?= $task_details->project_id ?>/project?task_id=<?= $task_details->task_id ?>"><?= lang('New Stock') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content bg-white">
                            <!-- ************** general *************-->
                            <div class="tab-pane active" id="manage_stock1">
                                <div class="table-responsive">
                                    <table class="table table-striped DataTables bulk_table" id="DataTables"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>

                                            <th data-orderable="false">
                                                <div class="checkbox c-checkbox">
                                                    <label class="needsclick">
                                                        <input id="select_all" type="checkbox">
                                                        <span class="fa fa-check"></span></label>
                                                </div>
                                            </th>

                                            <th><?= lang('item') ?></th>
                                            <?php
                                            $invoice_view = config_item('invoice_view');
                                            if (!empty($invoice_view) && $invoice_view == '2') {
                                                ?>
                                                <th><?= lang('hsn_code') ?></th>
                                            <?php } ?>
                                            <?php if (admin()) { ?>
                                                <th class="col-sm-1"><?= lang('cost_price') ?></th>
                                            <?php } ?>
                                            <th class="col-sm-1"><?= lang('unit_price') ?></th>
                                            <th class="col-sm-1"><?= lang('unit') . ' ' . lang('type') ?></th>
                                            <th class="col-sm-2"><?= lang('project') ?></th>
                                            <th class="col-sm-2"><?= lang('task') ?></th>
                                            <th class="col-sm-1"><?= lang('Tax') ?></th>
                                            <th class="col-sm-1"><?= lang('group') ?></th>
<!--                                            --><?php //$show_custom_fields = custom_form_table(18, null);
//                                            if (!empty($show_custom_fields)) {
//                                                foreach ($show_custom_fields as $c_label => $v_fields) {
//                                                    if (!empty($c_label)) {
//                                                        ?>
<!--                                                        <th>--><?php //= $c_label ?><!-- </th>-->
<!--                                                    --><?php //}
//                                                }
//                                            }
//                                            ?>
<!--                                            --><?php //if (!empty($edited) || !empty($deleted)) { ?>
<!--                                                <th class="col-sm-1">--><?php //= lang('action') ?><!--</th>-->
<!--                                            --><?php //} ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <script type="text/javascript">
                                            $(document).ready(function () {
                                                list = base_url + "admin/items/itemsList" + "<?php echo('/' . $task_details->task_id . '/task'); ?>";
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
                            <!-- End Tasks Management-->
                            <div class="tab-pane" id="stock_use">
                                <div class="row mb-lg invoice estimate-template">
                                    <form name="myform" role="form" data-parsley-validate="" novalidate=""
                                          enctype="multipart/form-data"
                                          id="form"
                                          action="<?php echo base_url(); ?>admin/purchase/stockIteamAction"
                                          method="post" class="form-horizontal">
                                        <div class="col-sm-10 col-xs-12  ">
                                            <div class="row text-right">
                                                <div class="form-group">

                                                    <?php
                                                    $fetch_data = $this->db->where_in('task_id', [$task_details->task_id])->get('tbl_saved_items')->result();
                                                    ?>

                                                    <label class="col-lg-3 control-label"><?= lang('Select Stock Item') ?> </label>
                                                    <div class="col-lg-7">
                                                        <select name="purchese_item_id" class="selectpicker"
                                                                data-width="100%" onchange="getSelectedItem(event)">
                                                            <option value="" selected> Select Stock Item</option>
                                                            <?php
                                                            foreach ($fetch_data as $_key => $v_purchase) {
                                                                ?>
                                                                <option value="<?= $v_purchase->saved_items_id ?>">
                                                                    <?= $v_purchase->item_name . ' (' . $v_purchase->quantity . ' ' . $v_purchase->unit_type . ')' ?>
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
                                          action="<?php echo base_url(); ?>admin/purchase/stockIteamTransfer"
                                          method="post" class="form-horizontal">
                                        <div class="col-sm-10 col-xs-12  ">
                                            <div class="row text-right">
                                                <div class="form-group">

                                                    <?php
                                                    $fetch_data = $this->db->where_in('task_id', [$task_details->task_id])->get('tbl_saved_items')->result();
                                                    ?>

                                                    <label class="col-lg-3 control-label"><?= lang('Select Stock Item') ?> </label>
                                                    <div class="col-lg-7">
                                                        <select name="purchese_item_id" class="selectpicker"
                                                                data-width="100%" onchange="getSelectedItem1(event)">
                                                            <option value="" selected> Select Stock Item</option>
                                                            <?php
                                                            foreach ($fetch_data as $_key => $v_purchase) {
                                                                ?>
                                                                <option value="<?= $v_purchase->saved_items_id ?>">
                                                                    <?= $v_purchase->item_name . ' (' . $v_purchase->quantity . ' ' . $v_purchase->unit_type . ')' ?>
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
                                                        <input type="hidden" class="form-control"
                                                               value="<?= $task_details->task_id ?>"
                                                               name="task_id" id="task_id">

                                                    </div>

                                                </div>
                                                <div class="form-group text-left">
                                                    <label class="col-lg-3 control-label"><?= lang('project') ?></label>
                                                    <div class="col-lg-7">
                                                        <select class="form-control select_box" style="width: 100%"
                                                                name="project_id" onchange="showTask(event )"
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
                                                        <select class="form-control select_box" style="width: 100%"
                                                                id="task-lists"
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


                                                <button type="submit" class="btn btn-info">Save</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>

                            <div class="tab-pane " id="stock-expense-and-transfer-history">
                                <div class="table-responsive">

                                    <table class="table table-striped DataTables bulk_table" id="DataTables"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="col-sm-2"><?= lang('Item Name') ?></th>
                                            <th class="col-sm-2"><?= lang('Transfer/Used From Project') ?></th>
                                            <th class="col-sm-2"><?= lang('Transfer/Used From Task') ?></th>
                                            <th class="col-sm-2"><?= lang('Transfer/Used To Project') ?></th>
                                            <th class="col-sm-2"><?= lang('Transfer/Used To Task') ?></th>
                                            <th class="col-sm-2"><?= lang('quantity') ?></th>
                                            <th class="col-sm-2"><?= lang('unit') . ' ' . lang('type') ?></th>
                                            <th class="col-sm-2"><?= lang('Type of Transaction') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $this->db->from('tbl_stock_uses');
                                        $this->db->join('tbl_saved_items', 'tbl_stock_uses.item_id = tbl_saved_items.saved_items_id', 'left');
                                        $this->db->join('tbl_saved_items as item_2', 'tbl_stock_uses.transfer_to_item_id = item_2.saved_items_id', 'left');
                                        $this->db->select('tbl_stock_uses.*, tbl_saved_items.item_name');
                                        $this->db->where('tbl_saved_items.task_id', $task_details->task_id);
                                        $this->db->or_where('item_2.task_id', $task_details->task_id);
                                        $query_result = $this->db->get();
                                        $result = $query_result->result();

                                        foreach ($result as $key => $row) {
                                            ?>
                                            <tr>
                                                <td><?= $key + 1 ?></td>
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
                                                    echo $query_result->task_name ?? '-';
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
                                                    echo $query_result->project_name ?? '-';
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
                                                <td><?= $row->unit_type ?></td>
                                                <td class="text-capitalize">
                                                    <a class="btn <?= $row->type == 'expense' ? "btn-info" : "btn-success" ?>"
                                                       href="#"><?= $row->type ?></a>
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
                    </div>
                </div>
            </div>
            <div class="tab-pane " id="purchase" style="position: relative;">
                <div class="box" style="border: none; " data-collapsed="0">
                    <div class="btn-group pull-right btn-with-tooltip-group" data-toggle="tooltip"
                         data-title="<?php echo lang('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left"
                            style="width:300px;<?php if (!empty($type) && $type == 'category') {
                                echo 'display:block';
                            } ?>">
                            <li class="<?php
                            if (empty($type)) {
                                echo 'active';
                            } ?>">
                                <a target="_blank"
                                   href="<?= base_url() ?>admin/projects/project_details/<?= $task_details->project_id ?>/10"><?php echo lang('all'); ?></a>
                            </li>
                            <li class="divider"></li>
                            <?php if (count($expense_category ?? []) > 0) { ?>
                                <?php foreach ($expense_category as $v_category) {
                                    ?>
                                    <li class="<?php if (!empty($category_id)) {
                                        if ($type == 'category') {
                                            if ($category_id == $v_category->expense_category_id) {
                                                echo 'active';
                                            }
                                        }
                                    } ?>">
                                        <a target="_blank"
                                           href="<?= base_url() ?>admin/projects/project_details/<?= $task_details->project_id ?>/10/category/<?php echo $v_category->expense_category_id; ?>"><?php echo $v_category->expense_category; ?></a>
                                    </li>
                                <?php }
                                ?>
                                <div class="clearfix"></div>
                                <li class="divider"></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="nav-tabs-custom">
                        <!-- Tabs within a box -->
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#manage_expense" data-toggle="tab"><?= lang('Purchase') ?></a>
                            </li>
                            <li class=""><a
                                        href="<?= base_url() ?>admin/purchase/index/<?= $task_details->project_id ?>/project?task_id=<?= $task_details->task_id ?>"><?= lang('New Purchase') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content bg-white">
                            <!-- ************** general *************-->
                            <div class="tab-pane active" id="manage_expense">
                                <div class="table-responsive">
                                    <table class="table table-striped DataTables " id="DataTables1" width="100%">
                                        <thead>
                                        <tr>
                                            <th><?= lang('reference_no') ?></th>
                                            <th><?= lang('Item Name') ?></th>
                                            <th><?= lang('supplier') ?></th>
                                            <th><?= lang('project') ?></th>
                                            <th><?= lang('task') ?></th>
                                            <th><?= lang('purchase_date') ?></th>
                                            <th><?= lang('due_amount') ?></th>
                                            <th><?= lang('status') ?></th>
                                            <?php $show_custom_fields = custom_form_table(20, null);
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
                                                <th class="col-options no-sort"><?= lang('action') ?></th>
                                            <?php } ?>
                                        </tr>
                                        </thead>
                                        <tbody id="purchase_body">
                                        <script type="text/javascript">
                                            ttable1 = 'DataTables1';
                                            list1 = base_url + "admin/purchase/purchaseList/<?php echo $task_details->project_id; ?>" + "?task_id=<?php echo $task_details->task_id; ?>";
                                        </script>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- End Tasks Management-->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Task Attachment Panel Starts --->
            <div class="tab-pane <?= $active == 3 ? 'active' : '' ?>" id="task_attachments">
                <div class="panel panel-custom">
                    <div class="panel-heading mb0">
                        <?php
                        $attach_list = $this->session->userdata('tasks_media_view');
                        if (empty($attach_list)) {
                            $attach_list = 'list_view';
                        }
                        ?>
                        <h3 class="panel-title"><?= lang('attach_file_list') ?>
                            <a data-toggle="tooltip" data-placement="top"
                               href="<?= base_url('admin/global_controller/download_all_attachment/task_id/' . $task_details->task_id) ?>"
                               class="btn btn-default"
                               title="<?= lang('download') . ' ' . lang('all') . ' ' . lang('attachment') ?>"><i
                                        class="fa fa-cloud-download"></i></a>
                            <a data-toggle="tooltip" data-placement="top"
                               class="btn btn-default toggle-media-view <?= (!empty($attach_list) && $attach_list == 'list_view' ? 'hidden' : '') ?>"
                               data-type="list_view"
                               title="<?= lang('switch_to') . ' ' . lang('media_view') ?>"><i
                                        class="fa fa-image"></i></a>
                            <a data-toggle="tooltip" data-placement="top"
                               class="btn btn-default toggle-media-view <?= (!empty($attach_list) && $attach_list == 'media_view' ? 'hidden' : '') ?>"
                               data-type="media_view"
                               title="<?= lang('switch_to') . ' ' . lang('list_view') ?>"><i
                                        class="fa fa-list"></i></a>

                            <div class="pull-right hidden-print" style="padding-top: 0px;padding-bottom: 8px">
                                <a href="<?= base_url() ?>admin/tasks/new_attachment/<?= $task_details->task_id ?>"
                                   class="text-purple text-sm" data-toggle="modal" data-placement="top"
                                   data-target="#myModal_extra_lg">
                                    <i class="fa fa-plus "></i> <?= lang('new') . ' ' . lang('attachment') ?></a>
                            </div>
                        </h3>
                    </div>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $(".toggle-media-view").on("click", function () {
                                $(".media-view-container").toggleClass('hidden');
                                $(".toggle-media-view").toggleClass('hidden');
                                $(".media-list-container").toggleClass('hidden');
                                var type = $(this).data('type');
                                var module = 'tasks';
                                $.get('<?= base_url()?>admin/global_controller/set_media_view/' + type + '/' + module, function (response) {
                                });
                            });
                        });
                    </script>
                    <?php
                    $this->load->helper('file');
                    if (empty($project_files_info)) {
                        $project_files_info = array();
                    } ?>
                    <div
                            class="p media-view-container <?= (!empty($attach_list) && $attach_list == 'media_view' ? 'hidden' : '') ?>">
                        <div class="row">
                            <?php $this->load->view('admin/tasks/attachment_list', array('project_files_info' => $project_files_info)); ?>
                        </div>
                    </div>
                    <div
                            class="media-list-container <?= (!empty($attach_list) && $attach_list == 'list_view' ? 'hidden' : '') ?>">
                        <?php
                        if (!empty($project_files_info)) {
                            foreach ($project_files_info as $key => $v_files_info) {
                                ?>
                                <div class="panel-group"
                                     id="media_list_container-<?= $files_info[$key]->task_attachment_id ?>"
                                     style="margin:8px 0px;" role="tablist"
                                     aria-multiselectable="true">
                                    <div class="box box-info" style="border-radius: 0px ">
                                        <div class="p pb-sm" role="tab" id="headingOne"
                                             style="border-bottom: 1px solid #dde6e9">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion"
                                                   href="#<?php echo $key ?>" aria-expanded="true"
                                                   aria-controls="collapseOne">
                                                    <strong
                                                            class="text-alpha-inverse"><?php echo $files_info[$key]->title; ?> </strong>
                                                    <small style="color:#ffffff " class="pull-right">
                                                        <?php if ($files_info[$key]->user_id == $this->session->userdata('user_id')) { ?>
                                                            <?php echo ajax_anchor(base_url("admin/tasks/delete_task_files/" . $files_info[$key]->task_attachment_id), "<i class='text-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#media_list_container-" . $files_info[$key]->task_attachment_id)); ?>
                                                        <?php } ?></small>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="<?php echo $key ?>" class="panel-collapse collapse <?php
                                        if (!empty($in) && $files_info[$key]->files_id == $in) {
                                            echo 'in';
                                        }
                                        ?>" role="tabpanel" aria-labelledby="headingOne">
                                            <div class="content p">
                                                <div class="table-responsive">
                                                    <table id="table-files" class="table table-striped ">
                                                        <thead>
                                                        <tr>
                                                            <th><?= lang('files') ?></th>
                                                            <th class=""><?= lang('size') ?></th>
                                                            <th><?= lang('date') ?></th>
                                                            <th><?= lang('total') . ' ' . lang('comments') ?></th>
                                                            <th><?= lang('uploaded_by') ?></th>
                                                            <th><?= lang('action') ?></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php
                                                        $this->load->helper('file');

                                                        if (!empty($v_files_info)) {
                                                            foreach ($v_files_info as $v_files) {
                                                                $user_info = $this->db->where(array('user_id' => $files_info[$key]->user_id))->get('tbl_users')->row();
                                                                $total_file_comment = count($this->db->where(array('uploaded_files_id' => $v_files->uploaded_files_id))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result());
                                                                ?>
                                                                <tr class="file-item">
                                                                    <td data-toggle="tooltip"
                                                                        data-placement="top"
                                                                        data-original-title="<?= $files_info[$key]->description ?>">
                                                                        <?php if ($v_files->is_image == 1) : ?>
                                                                            <div class="file-icon"><a
                                                                                        data-toggle="modal"
                                                                                        data-target="#myModal_extra_lg"
                                                                                        href="<?= base_url() ?>admin/tasks/attachment_details/r/<?= $files_info[$key]->task_attachment_id . '/' . $v_files->uploaded_files_id ?>">
                                                                                    <img
                                                                                            style="width: 50px;border-radius: 5px;"
                                                                                            src="<?= base_url() . $v_files->files ?>"/></a>
                                                                            </div>
                                                                        <?php else : ?>
                                                                            <div class="file-icon"><i
                                                                                        class="fa fa-file-o"></i>
                                                                                <a data-toggle="modal"
                                                                                   data-target="#myModal_extra_lg"
                                                                                   href="<?= base_url() ?>admin/tasks/attachment_details/r/<?= $files_info[$key]->task_attachment_id . '/' . $v_files->uploaded_files_id ?>"><?= $v_files->file_name ?></a>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </td>

                                                                    <td class=""><?= $v_files->size ?>Kb</td>
                                                                    <td class="col-date"><?= date('Y-m-d' . "<br/> h:m A", strtotime($files_info[$key]->upload_time)); ?></td>
                                                                    <td class=""><?= $total_file_comment ?></td>
                                                                    <td>
                                                                        <?= $user_info->username ?>
                                                                    </td>
                                                                    <td>
                                                                        <a class="btn btn-xs btn-dark"
                                                                           data-toggle="tooltip"
                                                                           data-placement="top"
                                                                           title="Download"
                                                                           href="<?= base_url() ?>admin/tasks/download_files/<?= $v_files->uploaded_files_id ?>"><i
                                                                                    class="fa fa-download"></i></a>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="5">
                                                                    <?= lang('nothing_to_display') ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <!-- Task Attachment Panel Ends --->
            <div class="tab-pane <?= $active == 4 ? 'active' : '' ?>" id="task_notes"
                 style="position: relative;">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= lang('notes') ?></h3>
                    </div>
                    <div class="panel-body">

                        <form action="<?= base_url() ?>admin/tasks/save_tasks_notes/<?php
                        if (!empty($task_details)) {
                            echo $task_details->task_id;
                        }
                        ?>" enctype="multipart/form-data" method="post" id="form" class="form-horizontal">
                            <div class="form-group">
                                <div class="col-lg-12">
                                                <textarea class="form-control textarea"
                                                          name="tasks_notes"><?= $task_details->tasks_notes ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-2">
                                    <button type="submit" id="sbtn"
                                            class="btn btn-primary"><?= lang('updates') ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane <?= $active == 5 ? 'active' : '' ?>" id="timesheet"
                 style="position: relative;">
                <style>
                    .tooltip-inner {
                        white-space: pre-wrap;
                    }
                </style>
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs">
                        <li class="<?= $time_active == 1 ? 'active' : ''; ?>"><a href="#general"
                                                                                 data-toggle="tab"><?= lang('Contactor') ?></a>
                        </li>
<!--                        <li class="--><?php //= $time_active == 2 ? 'active' : ''; ?><!--"><a href="#contact"-->
<!--                                                                                 data-toggle="tab">--><?php //= lang('manual_entry') ?><!--</a>-->
<!--                        </li>-->
                    </ul>
                    <div class="tab-content bg-white">
                        <!-- ************** general *************-->
                        <div class="tab-pane <?= $time_active == 1 ? 'active' : ''; ?>" id="general">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th><?= lang('name') ?></th>
                                        <th><?= lang('task') ?> Name</th>
                                        <th><?= lang('progress') ?></th>
                                        <th><?= lang('status') ?></th>
                                        <th><?= lang('budget') ?></th>
                                        <th><?= lang('Paid') ?></th>
                                        <th><?= lang('Due') ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php

                                    $all_sub_task_ids = get_all_sub_tasks($task_details->task_id);
                                    $tasks = $this->db->where_in('tbl_task.task_id', $all_sub_task_ids)
                                        ->select('tbl_task.*,tbl_customer_group.customer_group')
                                        ->join('tbl_customer_group', 'tbl_customer_group.customer_group_id = tbl_task.contactor_id')
                                        ->get('tbl_task')
                                        ->result();


                                    if (!empty($tasks)) :
                                        foreach ($tasks as $key => $task) :
                                            $expense = $tasks = $this->db->select('tbl_transactions.name,tbl_transactions.amount,tbl_transactions.task_id')
                                                ->where('task_id', $task->task_id)
                                                ->get('tbl_transactions')
                                                ->row();
                                            ?>
                                            <tr id="table-bugs-<?= $task->task_id ?>">
                                                <td>
                                                    <a class="text-info"
                                                       href="#"><?php echo $task->customer_group; ?></a>
                                                </td>
                                                <td>
                                                    <a class="text-info"
                                                       href="<?= base_url() ?>admin/tasks/view_task_details/<?= $task->task_id ?>"><?php echo $task->task_name; ?></a>
                                                </td>

                                                <td>
                                                    <div class="inline ">
                                                        <div class="easypiechart text-success" style="margin: 0px;"
                                                             data-percent="<?= $task->task_progress ?>"
                                                             data-line-width="5" data-track-Color="#f0f0f0"
                                                             data-bar-color="#<?php
                                                             if ($task->task_progress == 100) {
                                                                 echo '8ec165';
                                                             } else {
                                                                 echo 'fb6b5b';
                                                             }
                                                             ?>" data-rotate="270" data-scale-Color="false"
                                                             data-size="50" data-animate="2000">
                                                                    <span class="small text-muted"><?= $task->task_progress ?>
                                                                        %</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $disabled = null;
                                                    if (!empty($task->task_status)) {
                                                        if ($task->task_status == 'completed') {
                                                            $status = "<div class='label label-success'>" . lang($task->task_status) . "</div>";
                                                            $disabled = 'disabled';
                                                        } elseif ($task->task_status == 'in_progress') {
                                                            $status = "<div class='label label-primary'>" . lang($task->task_status) . "</div>";
                                                        } elseif ($task->task_status == 'cancel') {
                                                            $status = "<div class='label label-danger'>" . lang($task->task_status) . "</div>";
                                                        } else {
                                                            $status = "<div class='label label-warning'>" . lang($task->task_status) . "</div>";
                                                        } ?>
                                                        <?= $status; ?>
                                                    <?php }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="label label-info"><?= display_money($task->budget) ?></span>
                                                </td>
                                                <td>
                                                    <span class="label label-success"><?= display_money($expense->amount) ?></span>
                                                </td>
                                                <td>
                                                    <span class="label label-danger"><?= display_money($task->budget - $expense->amount) ?></span>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane <?= $time_active == 2 ? 'active' : ''; ?>" id="contact">
                            <form role="form" enctype="multipart/form-data" id="form"
                                  action="<?php echo base_url(); ?>admin/tasks/update_tasks_timer/<?php
                                  if (!empty($tasks_timer_info)) {
                                      echo $tasks_timer_info->tasks_timer_id;
                                  }
                                  ?>" method="post" class="form-horizontal">
                                <?php
                                if (!empty($tasks_timer_info)) {
                                    $start_date = date('Y-m-d', $tasks_timer_info->start_time);
                                    $start_time = date('H:i', $tasks_timer_info->start_time);
                                    $end_date = date('Y-m-d', $tasks_timer_info->end_time);
                                    $end_time = date('H:i', $tasks_timer_info->end_time);
                                } else {
                                    $start_date = '';
                                    $start_time = '';
                                    $end_date = '';
                                    $end_time = '';
                                }
                                ?>
                                <?php if ($this->session->userdata('user_type') == '1' && empty($tasks_timer_info->tasks_timer_id)) { ?>
                                    <div class="form-group margin">
                                        <div class="col-sm-8 center-block">
                                            <label
                                                    class="control-label"><?= lang('select') . ' ' . lang('tasks') ?>
                                                <span
                                                        class="required">*</span></label>
                                            <select class="form-control select_box" name="task_id"
                                                    required="" style="width: 100%">
                                                <?php
                                                $all_tasks_info = $this->db->get('tbl_task')->result();
                                                if (!empty($all_tasks_info)):foreach ($all_tasks_info as $v_task_info):
                                                    ?>
                                                    <option
                                                            value="<?= $v_task_info->task_id ?>" <?= $v_task_info->task_id == $task_details->task_id ? 'selected' : null ?>><?= $v_task_info->task_name ?></option>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <input type="hidden" name="task_id"
                                           value="<?= $task_details->task_id ?>">
                                <?php } ?>
                                <div class="form-group margin">
                                    <div class="col-sm-4">
                                        <label class="control-label"><?= lang('start_date') ?> </label>
                                        <div class="input-group">
                                            <input type="text" name="start_date"
                                                   class="form-control start_date"
                                                   value="<?= $start_date ?>"
                                                   data-date-format="<?= config_item('date_picker_format'); ?>">
                                            <div class="input-group-addon">
                                                <a href="#"><i class="fa fa-calendar"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="control-label"><?= lang('start_time') ?></label>
                                        <div class="input-group">
                                            <input type="text" name="start_time"
                                                   class="form-control timepicker2"
                                                   value="<?= $start_time ?>">
                                            <div class="input-group-addon">
                                                <a href="#"><i class="fa fa-clock-o"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group margin">
                                    <div class="col-sm-4">
                                        <label class="control-label"><?= lang('end_date') ?></label>
                                        <div class="input-group">
                                            <input type="text" name="end_date"
                                                   class="form-control end_date" value="<?= $end_date ?>"
                                                   data-date-format="<?= config_item('date_picker_format'); ?>">
                                            <div class="input-group-addon">
                                                <a href="#"><i class="fa fa-calendar"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="control-label"><?= lang('end_time') ?></label>
                                        <div class="input-group">
                                            <input type="text" name="end_time"
                                                   class="form-control timepicker2"
                                                   value="<?= $end_time ?>">
                                            <div class="input-group-addon">
                                                <a href="#"><i class="fa fa-clock-o"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group margin">
                                    <div class="col-sm-8 center-block">
                                        <label class="control-label"><?= lang('edit_reason') ?><span
                                                    class="required">*</span></label>
                                        <div>
                                                <textarea class="form-control" name="reason" required="" rows="6"><?php
                                                    if (!empty($tasks_timer_info)) {
                                                        echo $tasks_timer_info->reason;
                                                    }
                                                    ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-top: 20px;">
                                    <div class="col-lg-6">
                                        <button type="submit"
                                                class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>


                    </div>
                </div>
            </div>
            <div class="tab-pane <?= $active == 20 ? 'active' : '' ?>" id="requisition" style="position: relative;">
                <div class="box" style="border: none; " data-collapsed="0">
                    <div class="nav-tabs-custom">
                        <!-- Tabs within a box -->
                        <ul class="nav nav-tabs">
                            <li class=""><a href="#manage_estimates"
                                            data-toggle="tab"><?= lang('requisition') ?></a>
                            </li>
                            <li class="">
                                <a target="_blank"
                                   href="<?= base_url() ?>admin/requisition/index/project/<?= $task_details->project_id ?>?task_id=<?= $task_details->task_id ?>">
                                    <?= lang('new_requisition') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content bg-white">
                            <!-- ************** general *************-->
                            <div class="tab-pane active" id="manage_estimates">
                                <div class="table-responsive">
                                    <table id="table-estimates" class="table table-striped ">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?= lang('requisition') ?></th>
                                            <th><?= lang('due_date') ?></th>
                                            <th><?= lang('amount') ?></th>
                                            <th><?= lang('status') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($all_requisition_info as $key => $v_requisition) {
                                            $name = str_replace("[INV]", "[REQ]", $v_requisition->reference_no);
                                            $name = str_replace("[EXP]", "[REQ]", $name);
                                            if ($v_requisition->status == 'Pending') {
                                                $label = "info";
                                            } elseif ($v_requisition->status == 'accepted') {
                                                $label = "success";
                                            } else {
                                                $label = "danger";
                                            }
                                            ?>
                                            <tr>
                                                <td><?= $key + 1 ?></td>
                                                <td>
                                                    <a class="text-info"
                                                       href="<?= base_url() ?>admin/requisition/index/requisition_details/<?= $v_requisition->requisition_id ?? 0 ?>"><?= $name ?></a>
                                                </td>
                                                <td><?= strftime(config_item('date_format'), strtotime($v_requisition->due_date ?? 0)) ?>
                                                    <?php
                                                    if (strtotime($v_requisition->due_date ?? 0) < strtotime(date('Y-m-d')) && $v_requisition->status == 'Pending') { ?>
                                                        <span class="label label-danger "><?= lang('expired') ?></span>
                                                    <?php }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?= display_money($this->requisition_model->requisition_calculation('requisition_amount', $v_requisition->requisition_id), $currency->symbol); ?>
                                                </td>
                                                <td>
                                                    <span class="label label-<?= $label ?>"><?= lang(strtolower($v_requisition->status)) ?></span>
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
                    </div>
                </div>
            </div>

            <div class="tab-pane " id="expense" style="position: relative;">
                <div class="box" style="border: none; " data-collapsed="0">
                    <div class="nav-tabs-custom">
                        <!-- Tabs within a box -->
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#manage_expense" data-toggle="tab"><?= lang('expense') ?></a>
                            </li>
                            <li class=""><a
                                        href="<?= base_url() ?>admin/transactions/expense/project_expense/<?= $task_details->project_id ?>?task_id=<?= $task_details->task_id ?>"><?= lang('new_expense') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content bg-white">
                            <!-- ************** general *************-->
                            <div class="tab-pane active" id="manage_expense">
                                <div class="table-responsive">
                                    <table id="manage_expense" class="table table-striped ">
                                        <thead>
                                        <tr>
                                            <th class="col-date"><?= lang('name') . '/' . lang('title') ?></th>
                                            <th><?= lang('date') ?></th>
                                            <th><?= lang('categories') ?></th>
                                            <th class="col-currency"><?= lang('amount') ?></th>
                                            <th><?= lang('attachment') ?></th>
                                            <th class="col-options no-sort"><?= lang('action') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (!empty($type) && $type == 'category') {
                                            $cate_expense_info = array();
                                            $expense_id = $this->uri->segment(7);
                                            if (!empty($all_expense_info)) {
                                                foreach ($all_expense_info as $v_expense) {
                                                    if ($v_expense->type == 'Expense' && $v_expense->category_id == $expense_id) {
                                                        array_push($cate_expense_info, $v_expense);
                                                    }
                                                }
                                            }
                                            $all_expense_info = $cate_expense_info;
                                        }
                                        $all_expense_info = array_reverse($all_expense_info);
                                        if (!empty($all_expense_info)) :
                                            foreach ($all_expense_info as $v_expense) :
                                                if ($v_expense->type == 'Expense') :
                                                    $category_info = $this->db->where('expense_category_id', $v_expense->category_id)->get('tbl_expense_category')->row();
                                                    if (!empty($category_info)) {
                                                        $category = $category_info->expense_category;
                                                    } else {
                                                        $category = lang('undefined_category');
                                                    }

                                                    $can_edit = $this->items_model->can_action('tbl_transactions', 'edit', array('transactions_id' => $v_expense->transactions_id));
                                                    $can_delete = $this->items_model->can_action('tbl_transactions', 'delete', array('transactions_id' => $v_expense->transactions_id));
                                                    $e_edited = can_action('31', 'edited');
                                                    $e_deleted = can_action('31', 'deleted');

                                                    $account_info = $this->items_model->check_by(array('account_id' => $v_expense->account_id), 'tbl_accounts');
                                                    ?>
                                                    <tr id="table-expense-<?= $v_expense->transactions_id ?>">
                                                        <td>
                                                            <a target="_blank"
                                                               href="<?= base_url() ?>admin/transactions/view_expense/<?= $v_expense->transactions_id ?>">
                                                                <?= (!empty($v_expense->name) ? $v_expense->name : '-') ?>
                                                            </a>
                                                        </td>
                                                        <td><?= strftime(config_item('date_format'), strtotime($v_expense->date)); ?></td>
                                                        <td><?= $category ?></td>
                                                        <td><?= display_money($v_expense->amount, $currency->symbol) ?></td>

                                                        <td>
                                                            <?php
                                                            $attachement_info = json_decode($v_expense->attachement);
                                                            if (!empty($attachement_info)) { ?>
                                                                <a href="<?= base_url() ?>admin/transactions/download/<?= $v_expense->transactions_id ?>"><?= lang('download') ?></a>
                                                            <?php } ?>
                                                        </td>

                                                        <td class="">
                                                            <a class="btn btn-info btn-xs"
                                                               href="<?= base_url() ?>admin/transactions/view_expense/<?= $v_expense->transactions_id ?>">
                                                                <span class="fa fa-list-alt"></span>
                                                            </a>
                                                            <?php if (!empty($can_edit) && !empty($e_edited)) { ?>
                                                                <?= btn_edit('admin/transactions/expense/' . $v_expense->transactions_id) ?>
                                                            <?php }
                                                            if (!empty($can_delete) && !empty($e_deleted)) {
                                                                ?>
                                                                <?php echo ajax_anchor(base_url("admin/transactions/delete_expense/" . $v_expense->transactions_id), "<i class='btn btn-danger btn-xs fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table-expense-" . $v_expense->transactions_id)); ?>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endif;
                                            endforeach;
                                        endif;
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- End Tasks Management-->

                        </div>
                    </div>
                </div>
            </div>


            <div class="tab-pane" id="estimates" style="position: relative;">
                <div class="box" style="border: none; " data-collapsed="0">
                    <div class="nav-tabs-custom">
                        <!-- Tabs within a box -->
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#manage_estimates"
                                                  data-toggle="tab"><?= lang('estimates') ?></a>
                            </li>
                            <li class=""><a
                                        href="<?= base_url() ?>admin/estimates/index/project/<?= $task_details->project_id ?>?task_id=<?= $task_details->task_id ?>"><?= lang('new_estimate') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content bg-white">
                            <!-- ************** general *************-->
                            <div class="tab-pane active" id="manage_estimates">
                                <div class="table-responsive">
                                    <table id="table-estimates" class="table table-striped ">
                                        <thead>
                                        <tr>
                                            <th><?= lang('estimate') ?></th>
                                            <th><?= lang('due_date') ?></th>
                                            <th><?= lang('amount') ?></th>
                                            <th><?= lang('status') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($all_estimates_info as $v_estimates) {
                                            if ($v_estimates->status == 'Pending') {
                                                $label = "info";
                                            } elseif ($v_estimates->status == 'Accepted') {
                                                $label = "success";
                                            } else {
                                                $label = "danger";
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <a class="text-info"
                                                       href="<?= base_url() ?>admin/estimates/index/estimates_details/<?= $v_estimates->estimates_id ?>"><?= $v_estimates->reference_no ?></a>
                                                </td>
                                                <td><?= strftime(config_item('date_format'), strtotime($v_estimates->due_date)) ?>
                                                    <?php
                                                    if (strtotime($v_estimates->due_date) < strtotime(date('Y-m-d')) && $v_estimates->status == 'Pending') { ?>
                                                        <span class="label label-danger "><?= lang('expired') ?></span>
                                                    <?php }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?= display_money($this->estimates_model->estimate_calculation('estimate_amount', $v_estimates->estimates_id), $currency->symbol); ?>
                                                </td>
                                                <td>
                                                    <span class="label label-<?= $label ?>"><?= lang(strtolower($v_estimates->status)) ?></span>
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
                    </div>
                </div>
            </div>
            <?php if (!empty($sub_tasks)) { ?>
                <div class="tab-pane <?= $active == 7 ? 'active' : '' ?>" id="sub_tasks">
                    <div class="nav-tabs-custom">
                        <!-- Tabs within a box -->
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#sub_general"
                                                  data-toggle="tab"><?= lang('all') . ' ' . lang('sub_tasks') ?></a>
                            </li>
                            <li>
                                <a href="<?= base_url('admin/tasks/all_task/sub_tasks/' . $task_details->task_id) ?>"><?= lang('new') . ' ' . lang('sub_tasks') ?></a>
                            </li>
                        </ul>
                        <div class="tab-content bg-white">
                            <!-- ************** general *************-->
                            <div class="tab-pane <?= $time_active == 1 ? 'active' : ''; ?>" id="sub_general">
                                <div class="table-responsive">
                                    <table id="table-tasks" class="table table-striped     DataTables">
                                        <thead>
                                        <tr>
                                            <th data-check-all>

                                            </th>
                                            <th><?= lang('task_name') ?></th>
                                            <th><?= lang('due_date') ?></th>
                                            <th class="col-sm-1"><?= lang('progress') ?></th>
                                            <th class="col-sm-1"><?= lang('status') ?></th>
                                            <th class="col-sm-2"><?= lang('changes/view') ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $t_edited = can_action('54', 'edited');
                                        if (!empty($all_sub_tasks)):foreach ($all_sub_tasks as $key => $v_task):
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="is_complete checkbox c-checkbox">
                                                        <label>
                                                            <input type="checkbox" data-id="<?= $v_task->task_id ?>"
                                                                   style="position: absolute;" <?php
                                                            if ($v_task->task_progress >= 100) {
                                                                echo 'checked';
                                                            }
                                                            ?>>
                                                            <span class="fa fa-check"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td><a class="text-info" style="<?php
                                                    if ($v_task->task_progress >= 100) {
                                                        echo 'text-decoration: line-through;';
                                                    }
                                                    ?>"
                                                       href="<?= base_url() ?>admin/tasks/view_task_details/<?= $v_task->task_id ?>"><?php echo $v_task->task_name; ?></a>
                                                </td>

                                                <td><?php
                                                    $due_date = $v_task->due_date;
                                                    $due_time = strtotime($due_date);
                                                    $current_time = strtotime(date('Y-m-d'));
                                                    ?>
                                                    <?= strftime(config_item('date_format'), strtotime($due_date)) ?>
                                                    <?php if ($current_time > $due_time && $v_task->task_progress < 100) { ?>
                                                        <span class="label label-danger"><?= lang('overdue') ?></span>
                                                    <?php } ?></td>
                                                <td>
                                                    <div class="inline ">
                                                        <div class="easypiechart text-success" style="margin: 0px;"
                                                             data-percent="<?= $v_task->task_progress ?>"
                                                             data-line-width="5" data-track-Color="#f0f0f0"
                                                             data-bar-color="#<?php
                                                             if ($v_task->task_progress == 100) {
                                                                 echo '8ec165';
                                                             } else {
                                                                 echo 'fb6b5b';
                                                             }
                                                             ?>" data-rotate="270" data-scale-Color="false"
                                                             data-size="50" data-animate="2000">
                                                            <span class="small text-muted"><?= $v_task->task_progress ?>
                                                                %</span>
                                                        </div>
                                                    </div>

                                                </td>
                                                <td>
                                                    <?php
                                                    if ($v_task->task_status == 'completed') {
                                                        $label = 'success';
                                                    } elseif ($v_task->task_status == 'not_started') {
                                                        $label = 'info';
                                                    } elseif ($v_task->task_status == 'deferred') {
                                                        $label = 'danger';
                                                    } else {
                                                        $label = 'warning';
                                                    }
                                                    ?>
                                                    <span
                                                            class="label label-<?= $label ?>"><?= lang($v_task->task_status) ?> </span>
                                                </td>
                                                <td>
                                                    <?php echo btn_view('admin/tasks/view_task_details/' . $v_task->task_id) ?>
                                                    <?php if (!empty($t_edited)) { ?>
                                                        <?php echo btn_edit('admin/tasks/all_task/' . $v_task->task_id) ?>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane <?= $time_active == 2 ? 'active' : ''; ?>" id="contact">
                                <form role="form" enctype="multipart/form-data" id="form"
                                      action="<?php echo base_url(); ?>admin/tasks/update_tasks_timer/<?php
                                      if (!empty($tasks_timer_info)) {
                                          echo $tasks_timer_info->tasks_timer_id;
                                      }
                                      ?>" method="post" class="form-horizontal">
                                    <?php
                                    if (!empty($tasks_timer_info)) {
                                        $start_date = date('Y-m-d', $tasks_timer_info->start_time);
                                        $start_time = date('H:i', $tasks_timer_info->start_time);
                                        $end_date = date('Y-m-d', $tasks_timer_info->end_time);
                                        $end_time = date('H:i', $tasks_timer_info->end_time);
                                    } else {
                                        $start_date = '';
                                        $start_time = '';
                                        $end_date = '';
                                        $end_time = '';
                                    }
                                    ?>
                                    <?php if ($this->session->userdata('user_type') == '1' && empty($tasks_timer_info->tasks_timer_id)) { ?>
                                        <div class="form-group margin">
                                            <div class="col-sm-8 center-block">
                                                <label
                                                        class="control-label"><?= lang('select') . ' ' . lang('tasks') ?>
                                                    <span
                                                            class="required">*</span></label>
                                                <select class="form-control select_box" name="task_id"
                                                        required="" style="width: 100%">
                                                    <?php
                                                    $all_tasks_info = $this->db->get('tbl_task')->result();
                                                    if (!empty($all_tasks_info)):foreach ($all_tasks_info as $v_task_info):
                                                        ?>
                                                        <option
                                                                value="<?= $v_task_info->task_id ?>" <?= $v_task_info->task_id == $task_details->task_id ? 'selected' : null ?>><?= $v_task_info->task_name ?></option>
                                                    <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <input type="hidden" name="task_id"
                                               value="<?= $task_details->task_id ?>">
                                    <?php } ?>
                                    <div class="form-group margin">
                                        <div class="col-sm-4">
                                            <label class="control-label"><?= lang('start_date') ?> </label>
                                            <div class="input-group">
                                                <input type="text" name="start_date"
                                                       class="form-control datepicker"
                                                       value="<?= $start_date ?>"
                                                       data-date-format="<?= config_item('date_picker_format'); ?>">
                                                <div class="input-group-addon">
                                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="control-label"><?= lang('start_time') ?></label>
                                            <div class="input-group">
                                                <input type="text" name="start_time"
                                                       class="form-control timepicker2"
                                                       value="<?= $start_time ?>">
                                                <div class="input-group-addon">
                                                    <a href="#"><i class="fa fa-clock-o"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group margin">
                                        <div class="col-sm-4">
                                            <label class="control-label"><?= lang('end_date') ?></label>
                                            <div class="input-group">
                                                <input type="text" name="end_date"
                                                       class="form-control datepicker" value="<?= $end_date ?>"
                                                       data-date-format="<?= config_item('date_picker_format'); ?>">
                                                <div class="input-group-addon">
                                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label class="control-label"><?= lang('end_time') ?></label>
                                            <div class="input-group">
                                                <input type="text" name="end_time"
                                                       class="form-control timepicker2"
                                                       value="<?= $end_time ?>">
                                                <div class="input-group-addon">
                                                    <a href="#"><i class="fa fa-clock-o"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group margin">
                                        <div class="col-sm-8 center-block">
                                            <label class="control-label"><?= lang('edit_reason') ?><span
                                                        class="required">*</span></label>
                                            <div>
                                                <textarea class="form-control" name="reason" required="" rows="6"><?php
                                                    if (!empty($tasks_timer_info)) {
                                                        echo $tasks_timer_info->reason;
                                                    }
                                                    ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-top: 20px;">
                                        <div class="col-lg-6">
                                            <button type="submit"
                                                    class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                                        </div>
                                    </div>
                                </form>
                            </div>


                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="tab-pane " id="activities">
                <div class="panel panel-custom">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?= lang('activities') ?>
                            <?php
                            $role = $this->session->userdata('user_type');
                            if ($role == 1) {
                                ?>
                                <span class="btn-xs pull-right">
                            <a href="<?= base_url() ?>admin/tasks/claer_activities/tasks/<?= $task_details->task_id ?>"><?= lang('clear') . ' ' . lang('activities') ?></a>
                            </span>
                            <?php } ?>
                        </h3>
                    </div>
                    <div class="panel-body " id="chat-box">
                        <?php
                        if (!empty($activities_info)) {
                            foreach ($activities_info as $v_activities) {
                                $profile_info = $this->db->where(array('user_id' => $v_activities->user))->get('tbl_account_details')->row();
                                $user_info = $this->db->where(array('user_id' => $v_activities->user))->get('tbl_users')->row();
                                ?>
                                <div class="timeline-2">
                                    <div class="time-item">
                                        <div class="item-info">
                                            <small data-toggle="tooltip" data-placement="top"
                                                   title="<?= display_datetime($v_activities->activity_date) ?>"
                                                   class="text-muted"><?= time_ago($v_activities->activity_date); ?></small>

                                            <p><strong>
                                                    <?php if (!empty($profile_info)) {
                                                        ?>
                                                        <a href="<?= base_url() ?>admin/user/user_details/<?= $profile_info->user_id ?>"
                                                           class="text-info"><?= $profile_info->fullname ?></a>
                                                    <?php } ?>
                                                </strong> <?= sprintf(lang($v_activities->activity)) ?>
                                                <strong><?= $v_activities->value1 ?></strong>
                                                <?php if (!empty($v_activities->value2)){ ?>
                                            <p class="m0 p0"><strong><?= $v_activities->value2 ?></strong></p>
                                            <?php } ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

    function showTask(e, project_id) {
        if (project_id == 77777) {
            let url = base_url + 'admin/global_controller/get_tasks/' + e.target.value;
            $.ajax({
                async: false,
                url: url,
                type: 'GET',
                dataType: "json",
                success: function (data) {
                    var result = data.responseText;
                    console.log(result);
                    $('#task-lists').empty();
                    $("#task-lists").html(result);
                }

            });
        }
        if (project_id == undefined) {
            var base_url = '<?= base_url() ?>';
            var strURL = base_url + 'admin/global_controller/get_tasks/' + e.target.value;
            var req = getXMLHTTP();
            if (req) {
                req.onreadystatechange = function () {
                    if (req.readyState == 4) {
                        // only if "OK"
                        if (req.status == 200) {
                            var result = req.responseText;
                            $('#task-lists').empty();
                            $("#task-lists").append(result);
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