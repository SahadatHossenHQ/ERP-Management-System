<?= message_box('success'); ?>
<?= message_box('error'); ?>
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>

<div id="transactions_state_report_div">
    <?php //$this->load->view("admin/transactions/transactions_state_report"); ?>
</div>


<?php
$created = can_action('31', 'created');
$edited = can_action('31', 'edited');
$deleted = can_action('31', 'deleted');
$expense_category = $this->db->get('tbl_expense_category')->result();
$id = $this->uri->segment(5);
if (!empty($created) || !empty($edited)){
?>
<div class="row">
    <div class="col-sm-12">
        <?php $is_department_head = is_department_head();
        if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) { ?>
            <div class="btn-group pull-right btn-with-tooltip-group _filter_data filtered" data-toggle="tooltip"
                 data-title="<?php echo lang('filter_by'); ?>">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu group animated zoomIn"
                    style="width:300px;">
                    <li class="filter_by all_filter"><a href="#"><?php echo lang('all'); ?></a></li>
                    <li class="divider"></li>

                    <li class="dropdown-submenu pull-left  " id="from_account">
                        <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('account'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left from_account"
                            style="">
                            <?php
                            $account_info = $this->db->order_by('account_id', 'DESC')->get('tbl_accounts')->result();
                            if (!empty($account_info)) {
                                foreach ($account_info as $v_account) {
                                    ?>
                                    <li class="filter_by" id="<?= $v_account->account_id ?>" search-type="by_account">
                                        <a href="#"><?php echo $v_account->account_name; ?></a>
                                    </li>
                                <?php }
                            }
                            ?>
                        </ul>
                    </li>
                    <div class="clearfix"></div>
                    <li class="dropdown-submenu pull-left " id="to_account">
                        <a href="#" tabindex="-1"><?php echo lang('by') . ' ' . lang('categories'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left to_account"
                            style="">
                            <?php
                            $income_category = $this->db->get('tbl_expense_category')->result();
                            if (count($income_category) > 0) { ?>
                                <?php foreach ($income_category as $v_category) {
                                    ?>
                                    <li class="filter_by" id="<?= $v_category->expense_category_id ?>"
                                        search-type="by_category">
                                        <a href="#"><?php echo $v_category->expense_category; ?></a>
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
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="<?= $active == 1 ? 'active' : ''; ?>">
                    <a href="#manage"
                       data-toggle="tab"><?= lang('ALl Branches') ?></a>
                </li>
                <li class="<?= $active == 2 ? 'active' : ''; ?>">
                    <a href="#create"
                       data-toggle="tab"><?= lang('New Branches') ?></a>
                </li>
<!--                <li><a class="import"-->
<!--                       href="--><?php //= base_url() ?><!--admin/transactions/import/Expense">--><?php //= lang('import') . ' ' . lang('expense') ?><!--</a>-->
<!--                </li>-->
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
                            <div class="panel-title"><strong><?= lang('all_expense') ?></strong></div>
                        </header>
                        <?php } ?>
                        <div class="table-responsive">
                            <table class="table table-striped DataTables bulk_table" id="DataTables" cellspacing="0"
                                   width="100%">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('Branch') . ' ' . lang('name') ?></th>
                                    <th><?= lang('Branch') . ' '  . lang('address') ?></th>
                                    <th><?= lang('Details')?></th>
                                    <th><?= lang('action') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        list = base_url + "admin/branches/branchesList";
                                        bulk_url = base_url + "admin/branches/bulk_delete_expense";
                                        $('.filtered > .dropdown-toggle').on('click', function () {
                                            if ($('.group').css('display') == 'block') {
                                                $('.group').css('display', 'none');
                                            } else {
                                                $('.group').css('display', 'block')
                                            }
                                        });
                                        $('.all_filter').on('click', function () {
                                            $('.to_account').removeAttr("style");
                                            $('.from_account').removeAttr("style");
                                        });
                                        $('.from_account li').on('click', function () {
                                            if ($('.to_account').css('display') == 'block') {
                                                $('.to_account').removeAttr("style");
                                                $('.from_account').css('display', 'block');
                                            } else {
                                                $('.from_account').css('display', 'block')
                                            }
                                        });

                                        $('.to_account li').on('click', function () {
                                            if ($('.from_account').css('display') == 'block') {
                                                $('.from_account').removeAttr("style");
                                                $('.to_account').css('display', 'block');
                                            } else {
                                                $('.to_account').css('display', 'block');
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
                                            table_url(base_url + "admin/branches/branchesList/" + filter_by + search_type);
                                        });
                                    });
                                </script>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if (!empty($created) || !empty($edited)) { ?>
                        <div class="tab-pane <?= $active == 2 ? 'active' : ''; ?>" id="create">
                            <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data"
                                  action="<?php echo base_url(); ?>admin/branches/save_branche/<?php
                                  if (!empty($expense_info)) {
                                      echo $expense_info->id;
                                  }
                                  ?>" method="post" class="form-horizontal  ">

                                <div class="form-group">
                                    <label class="col-lg-2 control-label"><?= lang('name') . '/' . lang('title') ?></label>
                                    <div class="col-lg-4">
                                        <input type="text" required
                                               placeholder="<?= lang('enter') . ' ' . lang('name') . '/' . lang('title') . ' ' . lang('for_personal') ?>"
                                               name="name" class="form-control" value="<?php
                                        if (!empty($expense_info->name)) {
                                            echo $expense_info->name;
                                        } ?>">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 0px">
                                    <label class="col-lg-2 control-label"><?= lang('address ') ?> </label>
                                    <div class="col-lg-4">
                                        <textarea name="address" class="form-control"><?php
                                            if (!empty($expense_info)) {
                                                echo $expense_info->notes;
                                            }
                                            ?></textarea>
                                    </div>

                                </div>

                                <div class="btn-bottom-toolbar text-right">
                                    <?php
                                    if (!empty($expense_info)) { ?>
                                        <button type="submit" id="file-save-button"
                                                class="btn btn-sm btn-primary"><?= lang('updates') ?></button>
                                        <button type="button" onclick="goBack()"
                                                class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                                    <?php } else {
                                        ?>
                                        <button type="submit" id="file-save-button"
                                                class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                                    <?php }
                                    ?>
                                </div>
                            </form>
                        </div>
                    <?php }else{ ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<script>
    function getSubTask(val, id = 'show-sub-task'){
        if (val == '') {
            return false;
        }
        var showSubTaskDiv = document.getElementById(id);
        var element = document.getElementById(val);
        if (element != null) {
            element.remove();
        }
        fetch(base_url + "admin/tasks/getSubTaskByTask/" + val)
            .then(response => response.json())
            .then(data => {
                if(data.tasks.length > 0){
                    var newElement = document.createElement('div');
                    newElement.id = val;
                    newElement.innerHTML = data.task_select_options;
                    showSubTaskDiv.appendChild(newElement);
                    setTimeout(function () {
                        $('.select_box').select2();
                    }, 300);
                } else {
                    setTimeout(function () {
                        // $('#'+val).hide();
                        // const e = document.getElementById(val);
                        // e.style.display = 'none';

                    }, 300);
                }
            })
            .catch(error => {
                // Handle any errors that occur during the API call
                console.error(error)
            });

    }
    $('#repeat_every').on('change', function () {
        if ($('input[name="billable"]').prop('checked') == true) {
            $('.billable_recurring_options').removeClass('hide');
        } else {
            $('.billable_recurring_options').addClass('hide');
        }
    });
    // hide invoice recurring options on page load
    $('#repeat_every').trigger('change');
</script>

<script>
    $(document).ready(function () {
        ins_data(base_url + 'admin/transactions/transactions_state_report')
    });
</script>