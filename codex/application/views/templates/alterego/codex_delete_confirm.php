<div class="notice">
    <h3> <?php echo $message?> </h3>

    <?php echo form_open($form_action,'',array($this->codexadmin->primary_key=>$id)); ?>
    <input type="submit" value="<?php echo $this->lang->line('codexadmin_delete_confirm_link'); ?>" name="delete_selected" id="delete-selected">
    <div class="clear"></div>
</div>
