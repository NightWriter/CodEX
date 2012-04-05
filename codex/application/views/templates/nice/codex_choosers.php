        <div id="choosers">
            
            <div id="template-chooser">
                <?php echo form_open($this->theme_chooser_action); ?>
                Themes:
                <select name="template">
                    <?php 
                        $this->load->helper('directory'); 
                        $templates_dir = 'codex/application/views/templates/';
                        $entities = directory_map($templates_dir,true); 
                        foreach($entities as $entity){
                            if(is_dir($templates_dir.$entity)){
                                echo '<option value="'.$entity.'"';
                                if($entity == $this->template) echo 'selected="selected"'; 
                                echo '>'.humanize($entity).'</option>';
                            }
                        }
                    ?>
                </select>
                <input type="submit" value="Submit" name="submit">
                <?php echo form_close(); ?>
            </div>
            <div class="clear"></div>
        </div>
