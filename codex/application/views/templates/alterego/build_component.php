<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#blanck_fields').hide();
    
    jQuery('#add_field').click(function(){
        
        jQuery('#area_fields').after(jQuery('#blanck_fields').html());
        return false;
    });
    
    jQuery('#alias').keyup(function(e){
            jQuery('#alias_yes').hide();
            jQuery('#alias_no').hide();
            
            var obj = jQuery(this);
            jQuery.post('<?=site_url('construct/check_alias')?>',{name:obj.val()},function(data){
                if(data == 1)
                    jQuery('#alias_yes').show();
                else
                    jQuery('#alias_no').show();
            });
            
        });
});
</script>
<?php
if(!empty($errors))
{
    foreach($errors as $error)
    {
        echo $error.'<br>';
    }
}
?>
<form action="<?=site_url('construct/build')?>" method="post" class="form-horizontal">
<fieldset>
      <legend>New Component</legend>
    <div class="control-group">
        <label class="control-label" for="title">Title</label>
        <div class="controls">
            <input type="text" value="" name="title" id="title" /> 
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="alias">Alias</label>
        <div class="controls">
            <input type="text" name="alias" value="" id="alias" /> 
            
            <img src="<?=base_url()?>codex/images/no.jpg" id="alias_no" style="display:none;width: 20px;" class="alias_img">
            <img src="<?=base_url()?>codex/images/yes.jpg" id="alias_yes" style="display:none;width: 20px;" class="alias_img">
            <span class="help-inline">It should only contain letters and numbers</span>
        </div>
    </div>
    
</fieldset>
<fieldset>
    <legend>Fields</legend>
    <a id="add_field" class="btn btn-success" href="#"><i class="icon-plus icon-white"></i> Добавить поле</a>
    
    
    <div id="blanck_fields">
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
        <div class="control-group ">
            <label class="control-label" for="type_field">Type field</label>
            <div class="controls">
                <select name="type_field[]" id="type_field">
                    <option value="-">-</option>
                    <option value="textbox">Text</option>
                    <option value="password">Password</option>
                    <option value="textarea">Textarea</option>
                    <option value="date">Date</option>
                    <option value="time">Date Time</option>
                    <option value="dropdown">DropDown</option>
                    <option value="dbdropdown">DbDropDown</option>
                    <option value="manytomany">ManyToMany</option>
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio button</option>
                    <option value="file">File</option>
                    <option value="image">Image</option>
                </select>
            </div>
        </div>
    </div>
    <div id="area_fields">
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
        <div class="control-group ">
            <label class="control-label" for="type_field">Type field</label>
            <div class="controls">
                <select name="type_field[]" id="type_field">
                    <option value="-">-</option>
                    <option value="textbox">Text</option>
                    <option value="password">Password</option>
                    <option value="textarea">Textarea</option>
                    <option value="date">Date</option>
                    <option value="time">Date Time</option>
                    <option value="dropdown">DropDown</option>
                    <!--<option value="dbdropdown">DbDropDown</option>-->
                    <option value="checkbox">Checkbox</option>
                    <option value="radio">Radio button</option>
                    <option value="file">File</option>
                    <option value="image">Image</option>
                </select>
            </div>
        </div>
    </div>
    
</fieldset>
<div class="form-actions">
    <button type="submit" name="codex_installer_submit" class="btn btn-primary">Save</button>    
</div>
</form>