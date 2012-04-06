<div class="codex-form">
    <?php 
    echo form_open_multipart($form_action,'class="form-horizontal well"',array('user_id'=>$this->codexsession->userdata('user_id'),$this->codexadmin->primary_key=>$this->codexadmin->active_id));
    ?>
     <fieldset>
     <?
    echo $form_html;
    echo $permissions_form;
    ?> 
    </fieldset>
 <div class="form-actions"  style="background:none">
    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('codex_submit'); ?></button>
    <a class="btn" href="<?=site_url($this->controller_link)?>"><?=$this->lang->line('codex_cancel')?></a>
    </div> 
    <?php echo form_close(); ?>
    <?php echo $this->codextemplates->get('extra-form-html'); ?>
</div>
