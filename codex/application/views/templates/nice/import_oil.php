<?=((!empty($import_count)?'Импортированно: '.$import_count.'<br />':''))?>
<form method="post" enctype="multipart/form-data">
Файл каталога <input type="file" name="file">
<input type="submit" value="Импорт">
</form>