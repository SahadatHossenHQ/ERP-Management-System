<script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
<label class="col-lg-2 control-label">Sub <?= lang('task') ?></label>
<div class="col-lg-4">
    <select class="form-control select_box" style="width: 100%" onchange="getSubTask(this.value)"
            name="sub_task_id[]">
        <option value=""><?= lang('select') . ' ' . lang('task') ?></option>
        <?php
        foreach ($tasks as $key => $task) {
            ?>
            <option value="<?php echo $task->task_id; ?>"><?= $task->task_name ?></option>
            <?php
        }
        ?>
    </select>
</div>