<span class="found_position">Найденно позиций (<b><?=$count_sync?></b>)</span>
<a href="#" onClick="$.post('<?=site_url('imports/reset_sync')?>',{ind:'<?=$pag_brand?>'},function(data){if(data)alert('Готово')})" class="pagination">Сбросить для применения фильтра</a>
<?if($pag_brand == 'oem'):?>
<a href="#" onClick="$.post('<?=site_url('imports/add_article')?>',{ind:'<?=$pag_brand?>'},function(data){if(data)alert('Готово')})" class="pagination">Импортировать в БД не найденные позиции</a>
<?endif;?>
<div class="pagination"><?=$pagination?></div>
<form method="post">
    <div id="codex-table">
        <table id="main-table">
            <thead>
                <tr id="header-row">
                    <th>#</th>
                    <th><?=$brand_title?></th>
                    <th>SUPPLIERS PartSell</th>
                    <th>BRANDS PartSell</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i=0;
                if(isset($elit_brands))
                    foreach($elit_brands as $row){
                        ++$i;
                ?>
                <tr>
                    <td><?=$i?>) </td>
                    <td><?=$row->title?></td>
                    <td>
                        <select name="brands[<?=$row->id?>]">
                        <option value="0"> - </option>
                        <?php
                            if(isset($partsell_brands))
                                foreach($partsell_brands as $_row){
                        ?>
                            <option value="<?=$_row->SUP_ID?>" 
                                <?=(($row->id_partsell_brands == $_row->SUP_ID)?'SELECTED="SELECTED"':'')?>
                            ><?=$_row->SUP_BRAND?></option>
                        <?php
                                }
                        ?>
                        </select>
                    </td>
                    <td>
                    <?php
                    if(isset($row->bra_id)){
                    ?>
                        <select name="bra[<?=$row->id?>]">
                        <option value="0"> - </option>
                        <?php
                            if(isset($partsell_brands1))
                                foreach($partsell_brands1 as $_row){
                        ?>
                            <option value="<?=$_row->BRA_ID?>" 
                                <?=(($row->bra_id == $_row->BRA_ID)?'SELECTED="SELECTED"':'')?>
                            ><?=$_row->BRA_BRAND?></option>
                        <?php
                                }
                        ?>
                        </select>
                    <?php
                    }
                    ?>
                    </td>
                </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <input type="submit" value="Синхронизировать бренды">
</form>
<div class="pagination"><?=$pagination?></div>