<?php
$this->codextemplates->jsFromAssets('js-tablesorter','jquery.tablesorter.pack.js');
$this->codextemplates->jsFromAssets('js-tablesorter-pager','jquery.tablesorter.pager.js');

$table_sorter_js = "

    $(document).ready(function() {
 
        $('#main-table')
            .tablesorter({widthFixed: true, widgets: ['zebra']})
            //.tablesorterPager({container: $('#pager')});
            .tablesorterPager({totalRows:".$total_rows.", container: $('#pager'),ajax: true,controllerLink:'".str_replace('&amp;','&',$this->pagination_link)."'});


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

$this->codextemplates->inlineJS('js-tablesorter-init', $table_sorter_js);

$this->codextemplates->cssFromAssets('css-datepicker','ui.datepicker.css');
$this->codextemplates->jsFromAssets('js-datepicker','ui.datepicker.js');
    
    $js ="$(document).ready(function() {
                $('#codexdatepicker_from').datepicker({dateFormat: 'yy-mm-dd'});
                $('#codexdatepicker_to').datepicker({dateFormat: 'yy-mm-dd'});
              });";
              
$this->codextemplates->inlineJS('js-init',$js); 
?>
<div id="codex-table">
<form method="post">
<?=$this->lang->line('codex_consumer')?>:
            <select name="filter_user">
                <option value=""></option>
                <?php
                if(isset($this->user_data['users']))
                    foreach($this->user_data['users'] as $row){
                            echo '<option value="'.$row->user_id.'" ';
                                if($row->user_id == $this->input->post('filter_user'))
                                    echo 'SELECTED="SELETCED"';
                            echo '>'.humanize($row->username)."</option>\n";
                    }
                ?>
            </select>
<?=$this->lang->line('codex_table')?>:
          <select name="filter_table">
            <option value=""></option>
            <?php
                if(isset($this->user_data['tables']))
                    foreach($this->user_data['tables'] as $row){
                            echo '<option value="'.$row->table.'" ';
                                if($row->table == $this->input->post('filter_table'))
                                    echo 'SELECTED="SELETCED"';
                            echo '>'.humanize($row->table)."</option>\n";
                    }
                ?>
            </select>
<?=$this->lang->line('codex_date_from')?>: <input class="text" size="7" id="codexdatepicker_from" type="text" value="<?=$this->input->post('filter_date_from')?>" name="filter_date_from">
<?=$this->lang->line('codex_date_to')?>: <input class="text" size="7" id="codexdatepicker_to" type="text" value="<?=$this->input->post('filter_date_to')?>" name="filter_date_to">

<input type="image" src="<?php echo $this->codexadmin->asset_folder; ?>/images/search.png" id="search-submit" />
</form>
        <?php if(count($entries) == 0) echo '<div class="info">'.$this->lang->line('codexadmin_no_entries').'</div>'; else {?>
        <?php echo form_open($this->delete_action); ?>
        <table id="main-table">
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
                                        echo '<th>'.mb_ucfirst($headers[$field]).'</th>'."\n";
                                    else
                                        echo '<th>'.mb_ucfirst($field).'</th>'."\n";
                        }
                        if(!empty($this->user_link)) 
                            echo '<th>&nbsp;</th>',"\n";
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
                   <?php $i = 0; foreach($this->codexadmin->display_fields as $field=>$foo){
                           echo '<td align="center">';
                               if($i == 0){
                                    $anchor = $entry[$field];

                                    if(empty($anchor))
                                        $anchor = 'Click to edit';

                                    echo codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->edit_link),$anchor,array('title'=>'')).'</td>';
                                    $i++; 
                               }
                               else
                                   echo $entry[$field],'</td>',"\n";
                          }
                          if(!empty($this->user_link)) 
                            echo '<td>'.codexAnchor(str_replace('{num}',$entry[$this->codexadmin->primary_key],$this->user_link['link']),$this->user_link['title'],array('title'=>$this->user_link['title'])).'</td>'."\n"; 
                    ?>

            </tr>
        <?php $j++; endforeach; ?>
            </tbody>
        </table>

        <input type="submit" value="<?php echo $this->lang->line('codex_delete_selected'); ?>" name="delete_selected" id="delete-selected">
        <?php echo form_close(); ?>

        <div id="select-all">
            <a id="select-all-anchor" href="#"><?=$this->lang->line('codex_select_all')?></a>

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