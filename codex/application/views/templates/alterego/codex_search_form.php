<?php 

$js = "
    $(document).ready(function() {
            $('.search-remove').click(function(){
                $(this).parent().slideUp('fast',function(){\$(this).remove();});
            });

            $('#search-expand').click(function(){
                $('#search-overflow-html .search-item').clone(true).appendTo('#search-overflow').hide().slideDown('fast');
            });
    });
";
$this->codextemplates->inlineJS('js-search', $js); 
function getSearchItemHTML($fields,$query,$selected_field,$asset_folder){

    $search_item_expand = '<div class="search-item">
    <button type="button" class="search-remove btn" ><i class="icon-minus"></i></button> 
                               
                                '.form_input('query[]',$query).'
                                <select name="fields[]">';
                                if(isset($fields))
                                    foreach($fields as $field=>$header){
                                        if($field == $selected_field)
                                            $search_item_expand .= '<option value="'.$field.'" selected>'.humanize((empty($header))?$field:$header).'</option>';
                                        else
                                            $search_item_expand .= '<option value="'.$field.'">'.humanize((empty($header))?$field:$header).'</option>';
                                    }
    $search_item_expand.='
                                </select>
                            </div>
                            ';
    return $search_item_expand;
};

?>
<style type="text/css">
#search-form-content input, #search-form-content select {margin-bottom: 0;}
.search-item {margin-top: 10px;}
</style>

                <div id="search-form">
 
                    <div id="search-form-content">
                    <?php
                    if(!empty($this->search_action)){
                    ?>
                        <?=form_open($this->search_action); ?>
                      <button type="button" id="search-expand" class="btn"><i class="icon-plus"></i></button> 
                        <?php
                            $queries = $this->input->post('query');
                            $fields = $this->input->post('fields');

                            if(count($queries) != count($fields))
                                show_error("Problem with setup of keywords vs fields...");

                            if($queries){
                                echo form_input('query[]',current($queries)); 
                                array_shift($queries);
                            }
                            else
                                echo form_input('query[]'); 
                        
                        ?>
                        <select name="fields[]">
                        <?php
                            $headers = $this->codexforms->iterate('getDisplayName'); 
                            foreach($this->codexadmin->display_fields as $field=>$header){
                                if($fields AND $field == reset($fields)){
                                    echo '<option value="'.current($fields).'" selected>'.humanize((empty($header))?$field:$header)."</option>\n";
                                }
                                else
                                    echo '<option value="'.$field.'">'.humanize((empty($header))?$field:$header)."</option>\n";
                            }
                            if(is_array($fields))
                                array_shift($fields); 
                            ?>
                        </select>
                                <button type="submit" class="btn">Search</button>
                       
                        <div id="search-overflow">
                        <?php
                            if($queries)
                                for($i=0;$i<count($queries);$i++){
                                    echo getSearchItemHTML($this->codexadmin->display_fields,$queries[$i],$fields[$i],$this->codexadmin->asset_folder);
                                }
                        ?>
                        </div>
                        </form>
                    <?php
                    }
                    ?>
                    </div>
                  
                </div>
            <div class="hidden" id="search-overflow-html">
                <?php echo getSearchItemHTML($this->codexadmin->display_fields,'','',$this->codexadmin->asset_folder); ?>
            </div>
