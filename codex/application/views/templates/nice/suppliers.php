<div id="codex-table">
<?php
if(!empty($message))
    echo $message;
?>
<form method="post" action='<?=site_url('suppliers/upload_file')?>' enctype="multipart/form-data">
    Место загрузки на сервере ./upload/import<br />
    Допустимые фалы (assortment_wirh_cross-list.xls | autoteam.xls | _waluta.dbf | stany.dbf | cennik.dbf)<br />
    <input type="file" name="file">
    <br />
    <input type="submit" name="submit" value="Загрузить файл" class="but"/>
</form>
<?php
if(is_array($import_files)){
    if(sizeof($import_files) > 0)
        echo 'Загруженные файлы: ';
    $s = '';
    foreach($import_files as $key=>$file)
        $s .= $file . ', ';
    echo rtrim($s,', ');
}
?><br /><br />
<?php
if(isset($statistics))
{
    echo 'Загрузка данных:<br />';
    foreach($statistics as $row)
        echo $row->name.': '.(($row->value == -1)?'Готово':$row->value).'<br />';
}
?>        
<!--<form method="post" action='<?=site_url('suppliers/upload')?>'>
   <select name="supplier_id">
   <option value="1"> AutoTeam </option>
   <option value="4"> Excel TOKO </option>
   <option value="2"> Fota </option>
   <option value="3"> Грабить с сайта </option>
   <input type="submit" name="submit" value="Импорт" class="but"/>
</form>-->
</div>
<div class="clear"></div>
