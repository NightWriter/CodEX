<script type="text/javascript">
function change_numbers()
{
    var supplier = $("#supplier :selected").val();
    var id_brand = $("#numbers :selected").val();
    var id_sclad = $("#numbers :selected").attr('rel');
    var art_nr   = $("#numbers :selected").attr('def');
    
    $.post('<?=site_url('articles/get_info')?>',{supplier:supplier,id_brand:id_brand,id_sclad:id_sclad,art_nr:art_nr},function(data){
        if(data)
        {
            $('#mode').val('add');
            $('#id').val(0);
            $('#e').val(art_nr);
            
            $("#main_sup :contains('"+data.title_brand+"')").attr("selected", "selected");
            $("#warehouse [value='"+id_sclad+"']").attr("selected", "selected");
            $('#price').val(data.PRICE);
            $('#count').val(data.COUNT);
        }
    },'json');
}
function change_supplier(id)
{
    $('#tr_numbers').hide();
    if(id != '')
    {
        $.post('<?=site_url('articles/get_numbers')?>',{id:id},function(data){
            if(data)
            {
                $('#numbers').html(data);
                $('#tr_numbers').show();
            }
        });
    }
}
function delete_item(id,number,sup_brand,id_sclad)
{
    if(!confirm('Вы уверены?'))
        return false;
    
    $.post('<?=site_url('articles/delete_item')?>',{number:number,sup_brand:sup_brand,id_sclad:id_sclad},function(data){
        if(data)
            $('#item_'+id+'_'+sup_brand+'_'+id_sclad).remove();
    });
}
function add_item()
{
    $('#mode').val('add');
    $('#id').val(0);
    $('#x').val('');
    $('#e').val('');
    
    $("#main_sup").attr("selected", "selected");
    $("#analag_sup").attr("selected", "selected");
    $("#warehouse").attr("selected", "selected");
    $('#price').val(0);
    $('#count').val(0);
    
    $('#recalc_data').show();
}
function before_recalc(id,w,x,e,sup_id,warehouse,price,count)
{
    $('#mode').val('');
    $('#id').val(id);
    $('#x').val(x);
    $('#e').val(e);
    
    $("#main_sup [value='"+sup_id+"']").attr("selected", "selected");
    $("#analag_sup :contains('"+w+"')").attr("selected", "selected");
    $("#warehouse [value='"+warehouse+"']").attr("selected", "selected");
    $('#price').val(price);
    $('#count').val(count);
    
    $('#recalc_data').show();
}
function recalc_item()
{
    var main_sup   = $("#main_sup :selected").val();
    var analag_sup = $("#analag_sup :selected").val();
    var warehouse  = $("#warehouse :selected").val();
    var x          = $("#x").val();
    var e          = $("#e").val();
    var price      = $("#price").val();
    var count      = $("#count").val();
    var id         = $("#id").val();
    var mode       = $("#mode").val();
    
    $.post('<?=site_url('articles/recalc_item')?>',{mode:mode,price:price,count:count,main_sup:main_sup,warehouse:warehouse,id:id,analag_sup:analag_sup,x:x,e:e},function(data){
        if(data == 1)
            document.location.reload();
        else
            alert(data);
        $('#recalc_data').hide();
    });
}
</script>
Список добавленных запчастей
<div id="codex-table">
<form method=post>
    <input type="text" name="search" value="<?=$this->input->post('search')?>" />
    <select name="field">
        <option value="ART_ID" <?=(($this->input->post('field')=='ART_ID')?'SELECTED="SELECTED"':'')?>>Art ID</option>
        <option value="ART_ARTICLE_NR" <?=(($this->input->post('field')=='ART_ARTICLE_NR')?'SELECTED="SELECTED"':'')?>>Номер</option>
        <option value="sup_brand" <?=(($this->input->post('field')=='sup_brand')?'SELECTED="SELECTED"':'')?>>Бренд</option>
        <option value="x" <?=(($this->input->post('field')=='x')?'SELECTED="SELECTED"':'')?>>Номер аналога</option>
        <option value="w" <?=(($this->input->post('field')=='w')?'SELECTED="SELECTED"':'')?>>Бренд аналога</option>
        <option value="sclad_title" <?=(($this->input->post('field')=='sclad_title')?'SELECTED="SELECTED"':'')?>>Название склад</option>
        <option value="price" <?=(($this->input->post('field')=='price')?'SELECTED="SELECTED"':'')?>>Цена</option>
        <option value="count" <?=(($this->input->post('field')=='count')?'SELECTED="SELECTED"':'')?>>Кол-во</option>
    </select>
    <input type="submit" value="Искать" />
    <input type="button" value="Добавить" onClick="add_item()" />
</form>
<div id="recalc_data" style="display:none;">
<form method=post>    
<input type="hidden" id="mode" />
<input type="hidden" id="id" />
    <table style="background-color: #A7C1DE; position: absolute; left: 500px; margin-top:0px; width: 350px;">
        <tr>
            <td>Поставщик</td>
            <td>
                <select id="supplier" onChange="change_supplier(this.value)">
                    <option value="">-</option>
                    <option value="autoteam">AutoTeam</option>
                    <option value="elit">Elit</option>
                    <option value="intercars">InterCars</option>
                    <option value="oem">OEM</option>
                    <option value="vengria">Венгрия</option>
                    <option value="vlad">Владислав</option>
                    <option value="fota">ФОТА</option>
                </select>
            </td>
        </tr>
        <tr id="tr_numbers" style="display:none">
            <td>Номер</td>
            <td>
                <select id="numbers" onChange="change_numbers()">
                    
                </select>
            </td>
        </tr>
        <tr>
            <td>Оригинальный номер</td>
            <td><input type="text" id="e" value="" /></td>
        </tr>
        <tr>
            <td>Бренд</td>
            <td>
            <select id="main_sup"><option value="">Не менять</value>
                <?php
                $_suppliers = '';
                if(!empty($suppliers))
                    foreach($suppliers as $supplier)
                    {
                        $_suppliers .= '<option value="'.$supplier->SUP_ID.'">'.$supplier->SUP_BRAND.'</option>';
                    }
                echo $_suppliers;
                ?>
                </select> (по умолчанию JS AUTO)
            </td>
        </tr>
        <tr>
            <td>Бренд аналога</td>
            <td>
                <select id="analag_sup"><option value="">Не менять</value>
                <?php
                $_suppliers = '';
                if(!empty($suppliers))
                    foreach($suppliers as $supplier)
                    {
                        $_suppliers .= '<option value="'.$supplier->SUP_BRAND.'">'.$supplier->SUP_BRAND.'</option>';
                    }
                echo $_suppliers;
                ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Номер аналога</td>
            <td><input type="text" id="x" value="" /></td>
        </tr>
        <tr>
            <td>Склад</td>
            <td>
                <select id="warehouse"><option value="">Не менять</value>
                <?php
                $_warehouse = '';
                if(!empty($warehouses))
                    foreach($warehouses as $warehouse)
                    {
                        $_warehouse .= '<option value="'.$warehouse->id.'">'.$warehouse->title.'('.$warehouse->code.')</option>';
                    }
                echo $_warehouse .= '</select>';
                ?>
                если не указать, присвоится значение аналога
            </td>
        </tr>
        <tr>
            <td>Цена</td>
            <td><input type="text" id="price" value="" /></td>
        </tr>
        <tr>
            <td>Количество</td>
            <td><input type="text" id="count" value="" /></td>
        </tr>
        <tr>
            <td colspan=2 style="text-align:center;">
                <a href="#" onClick="recalc_item()">Сохранить</a>
                <a href="#" onClick="$('#recalc_data').hide();">Отмена</a>
            </td>
        </tr>
    </table>
</form>
</div>
<?php
if(!empty($rows))
{
    echo '<table id="main-table">
          <thead>
              <tr id="header-row">
                <th>Art ID</th>
                <th>Номер</th>
                <th>Бренд</th>
                <th>Номер аналога</th>
                <th>Бренд аналога</th>
                <th>Склад</th>
                <th>Цена</th>
                <th>Кол-во</th>
                <th>&nbsp;</th>
              </tr>
          </thead>';

    foreach($rows as $row)
    {
        echo '<tr id="item_'.$row->ART_ID.'_'.$row->ART_SUP_ID.'_'.$row->id_sclad.'">
                <td>'.$row->ART_ID.'</td>
                <td>'.$row->ART_ARTICLE_NR.'</td>
                <td>'.$row->SUP_BRAND.'</td>
                <td>'.$row->X.'</td>
                <td>'.$row->W.'</td>
                <td>'.$row->sclad_title.'('.$row->sclad_code.')</td>
                <td>'.$row->PRICE.'</td>
                <td>'.$row->COUNT.'</td>
                <td><a href="#" onClick="before_recalc(\''.$row->ART_ID.'\',\''.$row->W.'\',\''.$row->X.'\',\''.$row->ART_ARTICLE_NR.'\',\''.$row->ART_SUP_ID.'\',\''.$row->id_sclad.'\',\''.$row->PRICE.'\',\''.$row->COUNT.'\')">Пересчитать</a>
                    <a href="#" onClick="delete_item(\''.$row->ART_ID.'\',\''.$row->ART_ARTICLE_NR.'\',\''.$row->ART_SUP_ID.'\',\''.$row->id_sclad.'\')">Удалить</a>
                </td>
              </tr>';
    }
    echo '</table>';
}
if(isset($pagination))
    echo '<div class="pagination">'.$pagination.'</div>';
?>
</div>