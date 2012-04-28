<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#blanck_fields').hide();
    jQuery('#dictionaries').hide();
    jQuery('.dictionaries').hide();
    
    jQuery('body').delegate('.type_field','change',function(){
        
        //jQuery('.dictionaries').hide();
        if (jQuery(this).next().hasClass('dictionaries'))
            jQuery(this).next().hide();
        
        var val = jQuery(this).val();
        
        if( val == 'dropdown' || val == 'checkbox' || val == 'radio' )
            if (jQuery(this).next().hasClass('dictionaries'))
                jQuery(this).next().show();
        
    });
    
    jQuery('.add_field').click(function(){
        
        jQuery('#area_fields .group-new:last-child').after('<div class="group-new"><hr>'+jQuery('#blanck_fields').html()+'</div>');
        return false;
    });
    
    jQuery('#alias').keyup(function(e){
            jQuery('#alias_yes').hide();
            jQuery('#alias_no').hide();
            
            var obj = jQuery(this);
            jQuery.post('<?=site_url('construct/check_alias')?>',{name:obj.val()},function(data){
                if(data == 1)
                {
                    jQuery('#div_alias').removeClass('error');
                    jQuery('#div_alias').addClass('success');
                    
                }else
                {
                    jQuery('#div_alias').removeClass('success');
                    jQuery('#div_alias').addClass('error');                
                }
            });
            
        });
    
  
    jQuery('#area_fields').delegate('.name_field', 'keyup', function(e){
        jQuery('.name_field_yes').hide();
        jQuery('.name_field_no').hide();
        var obj = jQuery(this);
        jQuery.post('<?=site_url('construct/check_field_name')?>',{name:obj.val()},function(data)
        {
            if(data == 1)
            {
                obj.parents('.control-group').removeClass('error');
                obj.parents('.control-group').addClass('success');    
            }    
            else
            {
                obj.parents('.control-group').removeClass('success');
                obj.parents('.control-group').addClass('error');       
            }
        });
    });
    
    
    jQuery('fieldset').delegate('.tmp-ckeck', 'change',function(){
        if (jQuery(this).attr('checked'))
            jQuery(this).prev('.tmp-ckeck-parent').val(1);
        else
            jQuery(this).prev('.tmp-ckeck-parent').val(0);
    });
    jQuery('fieldset').delegate('.remove-rows', 'click',function(){
        jQuery(this).parents('.group-new').remove();
        return false;
    });
    
    jQuery('.form_action').submit(function(){
        var url = jQuery(this).attr('action');
        var data = jQuery(this).serialize();
        
        jQuery.post(url,data,function(responce){
            if(responce.success)
                location.href = responce.redirect;
                
            var output = '';
            for(i in responce.messages)
            {
                for(k in responce.messages[i])
                {
                    var prefix = '';
                    var suffix = '';
                    
                    switch(i)
                    {
                        case 'success':
                            prefix = '<div class="alert alert-success">';
                            suffix = '</div>';
                        break;
                        case 'info':
                            prefix = '<div class="alert alert-info">';
                            suffix = '</div>';
                        break;
                        case 'failure':
                            prefix = '<div class="alert alert-error">';
                            suffix = '</div>';
                        break;
                        default:
                            prefix = '<div class="alert alert-info">';
                            suffix = '</div>';
                        break;
                    }
                    output += prefix+responce.messages[i][k]+suffix+"\n";
                }
            }
            jQuery('#messages').html(output);
        },'json');
        return false;
    });
});
</script>
<div id="messages">
    <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_messages');?>
</div>
<form action="<?=site_url('construct/build')?>" method="post" class="form-horizontal form_action">
<input type="hidden" name="act" value="create_component">
<fieldset>
      <legend><?=$this->lang->line('codex_new_component')?></legend>
    <div class="control-group">
        <label class="control-label" for="title"><?=$this->lang->line('codex_title')?></label>
        <div class="controls">
            <input type="text" value="<?=$this->input->post('title')?>" name="title" id="title" /> 
        </div>
    </div>
    <div class="control-group" id="div_alias">
        <label class="control-label" for="alias"><?=$this->lang->line('codex_alias')?></label>
        <div class="controls">
            <input type="text" name="alias" value="<?=$this->input->post('alias')?>" id="alias" /> 
            
            <span class="help-inline"><?=$this->lang->line('codex_contain_letters_numbers')?></span>
        </div>
    </div>
    
</fieldset>
<fieldset>
    <legend><?=$this->lang->line('codex_fields')?></legend>
    
    <div id="blanck_fields" >
        <div class="control-group">
            <label class="control-label" for="label_field"><?=$this->lang->line('codex_label_field')?></label>
            <div class="controls">
                <input type="text" name="label_field[]" value="" class="label_field" /> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="name_field"><?=$this->lang->line('codex_name_field')?></label>
            <div class="controls">
                <input type="text" name="name_field[]" value="" class="name_field" /> 
                <span class="help-inline"><?=$this->lang->line('codex_contain_letters_numbers')?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="required_field"><?=$this->lang->line('codex_required_field')?></label>
            <div class="controls">
                <input type="hidden" class="tmp-ckeck-parent" name="required_field[]" value="0">
                <input type="checkbox" class="tmp-ckeck" value="1" /> 
            </div>
        </div>
        <div class="control-group ">
            <label class="control-label" for="type_field"><?=$this->lang->line('codex_type_field')?></label>
            <div class="controls">
                <select name="type_field[]" class="type_field" id="type_field">
                    <option value="-">-</option>
                    <option value="textbox"><?=$this->lang->line('codex_type_text')?></option>
                    <option value="password"><?=$this->lang->line('codex_type_password')?></option>
                    <option value="textarea"><?=$this->lang->line('codex_type_textarea')?></option>
                    <option value="date"><?=$this->lang->line('codex_type_date')?></option>
                    <option value="time"><?=$this->lang->line('codex_type_date_time')?></option>
                    <option value="dropdown"><?=$this->lang->line('codex_type_drop_down')?></option>
                    <!--<option value="dbdropdown"><?=$this->lang->line('codex_type_db_drop_down')?></option>
                    <option value="manytomany"><?=$this->lang->line('codex_type_many_to_many')?></option>-->
                    <option value="checkbox"><?=$this->lang->line('codex_type_checkbox')?></option>
                    <option value="radio"><?=$this->lang->line('codex_type_radio')?></option>
                    <option value="file"><?=$this->lang->line('codex_type_file')?></option>
                    <option value="image"><?=$this->lang->line('codex_type_image')?></option>
                </select>
                <? if(!empty($dictionaries)): ?>
                <select name="dictionaries[]" class="dictionaries">
                    <option value="0"><?=$this->lang->line('codex_select_from_list')?></option>
                    <? foreach($dictionaries as $row): ?>
                        <option value="<?=$row->id?>"><?=$row->desc?></option>
                    <? endforeach; ?>
                </select>
                <? endif; ?>
                <a class="btn btn-danger remove-rows" href="#"><i class="icon-trash icon-white"></i><?=$this->lang->line('codex_delete')?></a>
            </div>
        </div>
       
    </div>
    <div id="area_fields" style="padding-top: 20px;">
        <div class="control-group">
            <label class="control-label" for="label_field"><?=$this->lang->line('codex_label_field')?></label>
            <div class="controls">
                <input type="text" name="label_field[]" value="" class="label_field" /> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="name_field"><?=$this->lang->line('codex_name_field')?></label>
            <div class="controls">
                <input type="text" name="name_field[]" value="" class="name_field" /> 
                <span class="help-inline"><?=$this->lang->line('codex_contain_letters_numbers')?></span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="required_field"><?=$this->lang->line('codex_required_field')?></label>
            <div class="controls">
                <input type="hidden" class="tmp-ckeck-parent" name="required_field[]" value="0">
                <input type="checkbox" class="tmp-ckeck" value="1" /> 
            </div>
        </div>
        <div class="control-group ">
            <label class="control-label" for="type_field"><?=$this->lang->line('codex_type_field')?></label>
            <div class="controls">
                <select name="type_field[]" class="type_field" class="type_field">
                    <option value="-">-</option>
                    <option value="textbox"><?=$this->lang->line('codex_type_text')?></option>
                    <option value="password"><?=$this->lang->line('codex_type_password')?></option>
                    <option value="textarea"><?=$this->lang->line('codex_type_textarea')?></option>
                    <option value="date"><?=$this->lang->line('codex_type_date')?></option>
                    <option value="time"><?=$this->lang->line('codex_type_date_time')?></option>
                    <option value="dropdown"><?=$this->lang->line('codex_type_drop_down')?></option>
                    <!--<option value="dbdropdown"><?=$this->lang->line('codex_type_db_drop_down')?></option>
                    <option value="manytomany"><?=$this->lang->line('codex_type_many_to_many')?></option>-->
                    <option value="checkbox"><?=$this->lang->line('codex_type_checkbox')?></option>
                    <option value="radio"><?=$this->lang->line('codex_type_radio')?></option>
                    <option value="file"><?=$this->lang->line('codex_type_file')?></option>
                    <option value="image"><?=$this->lang->line('codex_type_image')?></option>
                </select>
                <? if(!empty($dictionaries)): ?>
                <select name="dictionaries[]" class="dictionaries">
                    <option value="0"><?=$this->lang->line('codex_select_from_list')?></option>
                    <? foreach($dictionaries as $row): ?>
                        <option value="<?=$row->id?>"><?=$row->desc?></option>
                    <? endforeach; ?>
                </select>
                <? endif; ?>
            </div>
        </div>
        <div class="group-new"></div>
    </div>
<div class="form-actions" style="background:none"> <button  id="add_field" class="btn btn-success add_field" href="#"><i class="icon-plus icon-white"></i> <?=$this->lang->line('codex_add_new_field')?></button>   </div>
</fieldset>
<div class="form-actions">
    <button type="submit" class="btn btn-primary"><?=$this->lang->line('codex_create_new_component')?></button>    
</div>
</form>
