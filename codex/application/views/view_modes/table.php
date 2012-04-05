<?php
$this->codextemplates->jsFromAssets('js-tablesorter','jquery.tablesorter.pack.js');
$this->codextemplates->jsFromAssets('js-tablesorter-pager','jquery.tablesorter.pager.js');

$queries = $this->input->post('query');
$fields = $this->input->post('fields');

$query = '';
$field = '';
if(!empty($queries) && !empty($fields) && sizeof($queries) == sizeof($fields))
{
    for($i=0;$i<count($queries);$i++){
        $query .= "'".$queries[$i]."',";
    }
    $query = rtrim($query,',');
    for($i=0;$i<count($fields);$i++){
        $field .= "'".$fields[$i]."',";
    }
    $field = rtrim($field,',');
}else{
    $query = '""';
    $field = '""';
}

$table_sorter_js = "

    $(document).ready(function() {
 
        $('#main-table')
            .tablesorter({widthFixed: true, widgets: ['zebra']})
            //.tablesorterPager({container: $('#pager')});
            .tablesorterPager({totalRows:".$total_rows.", container: $('#pager'),ajax: true,controllerLink:'".str_replace('&amp;','&',$this->pagination_link)."',query:".$query.",field:".$field."});


            $('#codex-table tr').each(function(){
                $(this).dblclick(function(){
                    if($(this).hasClass('selected'))
                        $(this).removeClass('selected');
                    else
                        $(this).addClass('selected');
                    if($('input:first', this).attr('checked') == true)
                        $('input:first', this).removeAttr('checked');
                    else
                        $('input:first', this).attr('checked','checked');
                });
            });
        
            var selected = false;
            $('#select-all-anchor').bind('click',function(){
                if(selected){
                    $('#main-table input').attr('checked','');
                    $('#select-all-anchor').text('Select All');
                    selected = false;
                }
                else{
                    $('#main-table input').attr('checked','checked');
                    $('#select-all-anchor').text('Select None');
                    selected = true;
                }
                return false;
            });

        });

";

$this->codextemplates->inlineJS('js-tablesorter-init', $table_sorter_js); ?>
<div id="codex-table">

        <?php if(count($entries) == 0) echo '<div class="info">'.$this->lang->line('codexadmin_no_entries').'</div>'; else {?>
        <?=form_open($this->delete_action); ?>
        <table id="main-table" class="table table-bordered">
            <thead>
                <tr id="header-row">
                    <?php 
                        echo '<th colspan="1"></th>'."\n";

                        $headers = $this->codexforms->iterate('getDisplayName'); 
                        
                        foreach($this->codexadmin->display_fields as $field=>$header){
                                    /*if(function_exists('humanize'))
                                        echo '<th>'.humanize($headers[$field]).'</th>'."\n";
                                    else*/
                                    if(isset($headers[$field]))
                                        echo '<th id="'.$field.'">'.mb_ucfirst($headers[$field]).'</th>'."\n";
                                    else
                                        echo '<th id="'.$field.'">'.mb_ucfirst($field).'</th>'."\n";
                        }
                        if(!empty($this->user_link))
                        {
                            if(!empty($this->user_link['title']))
                            {
                                echo '<th>&nbsp;</th>',"\n";
                            }else{
                                foreach($this->user_link as $link)
                                    echo '<th>&nbsp;</th>',"\n";
                            }
                        }
                    ?>
                </tr>
            </thead>
            <tbody>
        <?php 
        
        $j=0;foreach($entries as $entry): 
            if($j % 2 == 0)                 
                echo '<tr class="even">';
            else
                echo '<tr class="odd">';
?>
                <td class="first"><input class="edit-button" type="checkbox" value="<?php echo $entry[$this->codexadmin->primary_key]?>" name="selected_rows[]"/></td>
                   <?php $i = 0; 
                   
                   foreach($this->codexadmin->display_fields as $field=>$foo){
                           echo '<td align="center"  onClick="document.location=\''.site_url(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->edit_link)).'\'">';
                               if($i == 0){
                                    $anchor = $entry[$field];
                                    
                                    if(empty($anchor) && $anchor !=0)
                                        $anchor = 'Click to edit';

                                    echo codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->edit_link),$anchor,array('title'=>'')).'</td>';
                                    $i++; 
                               }
                               //elseif($field == 'user_id' && $this->controller_name == 'Vin')
                                   //echo '<a href="'.base_url().'backend.php?c=crud&m=manage&t=USERS&a=edit&id='.$entry[$field].'">'.$entry[$field].'</a></td>'."\n";
                               else
                                   echo $entry[$field],'</td>',"\n";
                          }
                          
                          if(!empty($this->user_link))
                          {
                            if(!empty($this->user_link['title']))
                            {
                                if(isset($entry[$this->user_link['title']]))
                                    $title = $entry[$this->user_link['title']];
                                  else
                                    $title = $this->user_link['title'];
                                
                                $link = str_replace('__','&',$this->user_link['link']);
                                    
                                echo '<td>'.codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$link),$title,array('title'=>$title)).'</td>'."\n"; 
                            }else{
                                foreach($this->user_link as $link){
                                      
                                      if(isset($entry[$link['title']]))
                                        $title = $entry[$link['title']];
                                      else
                                        $title = $link['title'];
                                    
                                    $link = str_replace('__','&',$link['link']);
                                        
                                    echo '<td>'.codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$link),$title,array('title'=>$title)).'</td>'."\n"; 
                                  }
                            }
                          }
                    ?>

            </tr>
        <?php $j++; endforeach; ?>
            </tbody>
        </table>

        <input type="submit" value="<?=$this->lang->line('codex_delete_selected'); ?>" name="delete_selected" id="delete-selected">
        <input type="submit" value="<?=$this->lang->line('codex_copy_selected'); ?>" name="copy_selected" id="delete-selected">
        <?=form_close(); ?>
        <div id="select-all">
            <a id="select-all-anchor" href="#">Select All</a>

            <div id="pager" class="pager">
                <form>
                    <img src="<?php echo $this->config->item('codex_asset_folder'); ?>images/pager/icons/first.png" class="first"/>
                    <img src="<?php echo $this->config->item('codex_asset_folder'); ?>images/pager/icons/prev.png" class="prev"/>
                    <input type="text" class="pagedisplay"/>
                    <img src="<?php echo $this->config->item('codex_asset_folder'); ?>images/pager/icons/next.png" class="next"/>
                    <img src="<?php echo $this->config->item('codex_asset_folder'); ?>images/pager/icons/last.png" class="last"/>
                    <select class="pagesize">
                        <option selected="selected" value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option  value="40">40</option>
                    </select>
                </form>
            </div>
            
            <div class="clear"></div>
            
        </div>
        <?php $this->load->view('templates/'.$this->template.'/codex_choosers'); ?>
        <?php } ?>
</div>
<div class="clear"></div>