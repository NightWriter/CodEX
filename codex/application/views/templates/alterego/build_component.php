<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#blanck_fields').hide();
    jQuery('#dictionaries').hide();
    
    jQuery('.type_field').change(function(){
        
        jQuery('#dictionaries').hide();
        
        var val = jQuery(this).val();
        
        if( val == 'dropdown' || val == 'checkbox' || val == 'radio' )
            jQuery('#dictionaries').show();
        
    });
    
    jQuery('#add_field').click(function(){
        
        jQuery('#area_fields').after('<div class="group-new">'+jQuery('#blanck_fields').html()+'</div>');
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
    
});
</script>
<div id="messages">
    <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_messages');?>
</div>
<form action="<?=site_url('construct/build')?>" method="post" class="form-horizontal">
<fieldset>
      <legend>New Component</legend>
    <div class="control-group">
        <label class="control-label" for="title">Title</label>
        <div class="controls">
            <input type="text" value="" name="title" id="title" /> 
        </div>
    </div>
    <div class="control-group" id="div_alias">
        <label class="control-label" for="alias">Alias</label>
        <div class="controls">
            <input type="text" name="alias" value="" id="alias" /> 
            
            <span class="help-inline">It should only contain letters and numbers</span>
        </div>
    </div>
    
</fieldset>
<fieldset>
    <legend>Fields</legend>
    
    
    
    <div id="blanck_fields" >
        <div class="control-group">
            <label class="control-label" for="label_field">Label field</label>
            <div class="controls">
                <input type="text" name="label_field[]" value="" id="label_field" /> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="name_field">Name field</label>
            <div class="controls">
                <input type="text" name="name_field[]" value="" id="name_field" /> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="required_field">Required field</label>
            <div class="controls">
                <input type="hidden" class="tmp-ckeck-parent" name="required_field[]" value="0">
                <input type="checkbox" class="tmp-ckeck" value="1" /> 
            </div>
        </div>
        <div class="control-group ">
            <label class="control-label" for="type_field">Type field</label>
            <div class="controls">
                <select name="type_field[]" class="type_field" id="type_field">
                    <option value="-">-</option>
                    <option value="textbox">Text</option>
                    <option value="password">Password</option>
                    <option value="textarea">Textarea</option>
                    <option value="date">Date</option>
                    <option value="time">Date Time</option>
                    <option value="dropdown">DropDown</option>
                    <!--<option value="dbdropdown">DbDropDown</option>
                    <option value="manytomany">ManyToMany</option>-->
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio button</option>
                    <option value="file">File</option>
                    <option value="image">Image</option>
                </select>
                
            </div>
        </div>
        <div class="form-actions">
            <a class="btn btn-danger remove-rows" href="#"><i class="icon-trash icon-white"></i>Delete</a>
        </div>
    </div>
    <div id="area_fields" style="padding-top: 20px;">
        <div class="control-group">
            <label class="control-label" for="label_field">Label field</label>
            <div class="controls">
                <input type="text" name="label_field[]" value="" id="label_field" /> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="name_field">Name field</label>
            <div class="controls">
                <input type="text" name="name_field[]" value="" id="name_field" /> 
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="required_field">Required field</label>
            <div class="controls">
                <input type="hidden" class="tmp-ckeck-parent" name="required_field[]" value="0">
                <input type="checkbox" class="tmp-ckeck" value="1" /> 
            </div>
        </div>
        <div class="control-group ">
            <label class="control-label" for="type_field">Type field</label>
            <div class="controls">
                <select name="type_field[]" class="type_field" id="type_field">
                    <option value="-">-</option>
                    <option value="textbox">Text</option>
                    <option value="password">Password</option>
                    <option value="textarea">Textarea</option>
                    <option value="date">Date</option>
                    <option value="time">Date Time</option>
                    <option value="dropdown">DropDown</option>
                    <!--<option value="dbdropdown">DbDropDown</option>
                    <option value="manytomany">ManyToMany</option>-->
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio button</option>
                    <option value="file">File</option>
                    <option value="image">Image</option>
                </select>
                <? if(!empty($dictionaries)): ?>
                <select name="dictionaries[]" id="dictionaries">
                    <option value="0">выбери из списка</option>
                    <? foreach($dictionaries as $row): ?>
                        <option value="<?=$row->id?>"><?=$row->desc?></option>
                    <? endforeach; ?>
                </select>
                <? endif; ?>
            </div>
        </div>
    </div>
<div class="form-actions" style="background:none"> <button  id="add_field" class="btn btn-success" href="#"><i class="icon-plus icon-white"></i> Add new field</button>   </div>
</fieldset>
<div class="form-actions">
    <button type="submit" name="codex_installer_submit" class="btn btn-primary">Create new component</button>    
</div>
</form>
