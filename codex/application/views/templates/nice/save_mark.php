<script type="text/javascript">
    function save_mark(id){
        $.post('<?=site_url('savedata/save_data_mark')?>',{text_id:id,partsell_title:$("#name_"+id).val()},function(data){
            if(data == 1)
                alert('Сохранено!');
            else
                alert('При сохранении произошла ошибка!\nПопробуйте ещё раз');
        })
    }
</script>
<form method="post" action="" id="save_form">
<select name="brands" onchange="if(this.value!=-1)$('#save_form').submit()">
<option value="-1">Выберите бренд</option>
<?php
if(isset($all_brands))
    foreach($all_brands as $row){
        echo '<option value="'.$row->MFA_ID.'" ';
            if($this->input->post('brands') == $row->MFA_ID)
                echo 'SELECTED="SELECTED"';
        echo '>'.$row->MFA_BRAND.'</option>';
    }
?>
</select>
<div id="codex-table">
    <table id="main-table">
        <thead>
            <tr id="header-row">
                <th>Марка (TecDoc)</th>
                <th>Марка (PartSell)</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if(isset($models))
                foreach($models as $row){
        ?>
            <tr>
                <td><input type="text" style="width:100%" disabled="disabled" value="<?=$row->MOD_CDS_TEXT_tecdoc?>" /></td>
                <td><input type="text" style="width:100%" id="name_<?=$row->TEXT_ID_tecdoc?>" value="<?=((empty($row->MOD_CDS_TEXT_partsell))?$row->MOD_CDS_TEXT_tecdoc:$row->MOD_CDS_TEXT_partsell)?>" /></td>
                <td><input type="button" value="Сохранить" onclick="save_mark(<?=$row->TEXT_ID_tecdoc?>)" /></td>
            </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
</div>
</form>