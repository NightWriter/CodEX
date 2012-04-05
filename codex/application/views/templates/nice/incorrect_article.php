Список необработанных запчастей (<?=$count?>)
<div id="codex-table">
<form method=post id="supplier">
    Поставщик 
    <select name="supplier" onChange="$('#supplier').submit()">
        <option value="">-</option>
        <option value="autoteam" <?=(($this->input->post('supplier')=='autoteam')?'SELECTED="SELECTED"':'')?>>AutoTeam</option>
        <option value="elit" <?=(($this->input->post('supplier')=='elit')?'SELECTED="SELECTED"':'')?>>Elit</option>
        <option value="intercars" <?=(($this->input->post('supplier')=='intercars')?'SELECTED="SELECTED"':'')?>>InterCars</option>
        <option value="oem" <?=(($this->input->post('supplier')=='oem')?'SELECTED="SELECTED"':'')?>>OEM</option>
        <option value="vengria" <?=(($this->input->post('supplier')=='vengria')?'SELECTED="SELECTED"':'')?>>Венгрия</option>
        <option value="vlad" <?=(($this->input->post('supplier')=='vlad')?'SELECTED="SELECTED"':'')?>>Владислав</option>
        <option value="fota" <?=(($this->input->post('supplier')=='fota')?'SELECTED="SELECTED"':'')?>>ФОТА</option>
        <option value="avi" <?=(($this->input->post('supplier')=='avi')?'SELECTED="SELECTED"':'')?>>AVI</option>
        <option value="qparts" <?=(($this->input->post('supplier')=='qparts')?'SELECTED="SELECTED"':'')?>>QParts</option>
    </select>
    <?php
    if(!empty($brands))
    {
        echo '<select name="brand" onChange="$(\'#supplier\').submit()">
              <option value="">-</option>';
            foreach($brands as $row)
            {
                echo '<option value="'.$row->id.'" ';
                    if($row->id == $this->input->post('brand'))
                        echo 'SELECTED="SELECTED"';
                echo '>'.$row->title.'</option>';
            }
        echo '</select>';
    }
    ?>
</form>
<form method=post>
<input type="hidden" name="supplier" value="<?=$this->input->post('supplier')?>" />
<input type="hidden" name="brand" value="<?=$this->input->post('brand')?>" />
    <table id="main-table">
          <thead>
              <tr id="header-row">
                <th>Номер</th>
                <th>Бренд</th>
                <th>Бренд аналога</th>
                <th>Номер аналога</th>
              </tr>
          </thead>
    <?php
    $analag_supplier = '';
                if(!empty($suppliers))
                    foreach($suppliers as $row)
                    {
                        $analag_supplier .= '<option value="'.$row->SUP_BRAND.'" ';
                            if($row->SUP_BRAND == $this->input->post('analag_supplier'))
                                $analag_supplier .= 'SELECTED=SELECTED';
                        $analag_supplier .= '>'.$row->SUP_BRAND.'</option>';
                    }
            
    if(!empty($rows))
        foreach($rows as $row)
        {
            echo '<tr>
                    <td>'.$this->save_data->filter_art($row->ART_NR,$this->input->post('supplier')).'</td>
                    <td>'.$row->title_brand.'</td>
                    <td><select name="analag_supplier['.str_replace(' ',':',$row->ART_NR).']">'.$analag_supplier.'</select></td>
                    <td><input type="text" name="analag_number['.str_replace(' ',':',$row->ART_NR).']" value="" /></td>
                  </tr>';
        }
    ?>
    </table>
    
    <div id="select-all" style="text-align:center">
        <input type="submit" value="Сохранить"/>
    </div>
</form>
</div>