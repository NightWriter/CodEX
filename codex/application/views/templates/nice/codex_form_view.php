<div class="codex-form">
    <?php 
    echo form_open_multipart($form_action,'',array('user_id'=>$this->codexsession->userdata('user_id'),$this->codexadmin->primary_key=>$this->codexadmin->active_id));
    echo $form_html;
    echo $permissions_form;
    ?> 
    <input type="submit" value="<?php echo $this->lang->line('codex_submit'); ?>" name="delete_selected" id="delete-selected">
    <div id="cancel-selected"> or <?php echo codexAnchor($this->controller_link, $this->lang->line('codex_cancel')); ?></div>
    <?php echo form_close(); ?>
    <?php echo $this->codextemplates->get('extra-form-html'); ?>
</div>
