<div id="codex-table">
<form method=post>
    <input type="text" name="number" value="<?=@$number?>" />
    <input type="submit" value="Показать информацию">
    <table id="main-table">
          <thead>
              <tr id="header-row">
                <th>Номер</th>
                <th>Бренд</th>
                <th>Склад</th>
                <th>Источник</th>
                <th>Цена</th>
                <th>Кол-во</th>
              </tr>
          </thead>
          <?php
          if(!empty($info))
            foreach($info as $row){
                echo '<tr>
                        <td>'.$row->ART_NR.'</td>
                        <td>'.(empty($row->SUP_BRAND)?$row->BRA_BRAND:$row->SUP_BRAND).'</td>
                        <td>'.$row->warehouse_title.' (ID: '.$row->id_sclad.')</td>
                        <td>'.$row->distributor_title.'</td>
                        <td>'.round($row->PRICE).'</td>
                        <td>'.$row->COUNT.'</td>
                    </tr>';
            }
          ?>
    </table>
</form>
</div>