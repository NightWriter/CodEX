<?php
$url =  site_url('ajaxportal/');
$expander = "
    $(document).ready(function() {
        $('#permissions-form-header .user-permission').prepend('<img class=\"remove-permission\" src=\"".$this->config->item('codex_asset_folder')."images/minus.png\">');

        $('#permissions-form legend').bind('click',function(){
            $('#permissions-form-content').slideToggle('fast');
        });
                             
        $('.remove-permission').bind('click',function(){
            var obj = $(this);
            $.post('$url', { 'action': '_removePermission', 'params': $(this).next().html() }, function(data) {
                if(data == 'success')
                    obj.parent().slideUp('fast',function(){\$(this).remove();});
            },'html');       
        });                  
    });                      
";                           
                             
$this->db->select('users.username, user_records.id, user_records.user_id, user_records.permissions, user_records.table_name');
$this->db->where('record_id',$this->codexadmin->active_id);
$this->db->where('table_name',$this->codexadmin->db_table);
$this->db->join('users','users.id = user_records.user_id');
                            
$query = $this->db->get('user_records');
$results = $query->result_array();
                            
$this->codextemplates->inlineJS('js-permissions-expander', $expander); ?>
<fieldset id="permissions-form">
    <legend><?php echo $this->lang->line('codex_specify_permissions'); ?></legend>
    <div id="permissions-form-content">
        <?php if(count($results) > 0){ ?>
            <div id="permissions-form-header">
                <?php
                        echo "<h3>Predefined permissions:</h3>";
                    foreach($results as $row){
                        $row['permissions'] = explode(',',$row['permissions']);
                        $owner = false;

                        for($i=0;$i<count($row['permissions']);$i++){
                            if($row['permissions'][$i] == 'owner'){
                                $owner = true;
                                unset($row['permissions'][$i]);
                            }
                        }
                        if($owner)
                            echo "<p>User <b>".$row['username'].'</b> is the <b>owner</b> of this record, and has full access to it.</p>';
                        else                                                     
                            echo "<p class=\"user-permission\"><span class=\"hidden\">user|".$row['user_id'].";record_id|".$this->codexadmin->active_id.";table|".$row['table_name']."</span>User <b>".$row['username'].'</b> has access to <b>'.implode(',',$row['permissions']).'</b> it.</p>';
                    }
                ?>
            </div>
        <?php } ?>
        <div class="permissions-form-left">
            <div class="label"><?php echo $this->lang->line('codex_users'); ?></div>
            <select size="6" name="permissions_users[0][]" multiple>
                <?php
                foreach($user_list as $user){
                    echo '<option value='.$user['id'].'>'.$user['username'].'</option>';
                }
                ?>
            </select>
        </div>
        <div class="permissions-form-right">
            <div class="label"><?php echo $this->lang->line('codex_permissions'); ?></div>
            <div><input type="checkbox" name="permissions_access[0][]" value="edit"> <?php echo $this->lang->line('codex_modify'); ?></div>
            <div><input type="checkbox" name="permissions_access[0][]" value="delete"> <?php echo $this->lang->line('codex_delete'); ?></div>
        </div>
        <div class="clear"></div>
    </div>
</fieldset>
