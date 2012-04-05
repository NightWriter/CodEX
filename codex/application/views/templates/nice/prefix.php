<script type="text/javascript">
function change_brand()
{
    $('#prefix_content').html('<img src="<?=base_url()?>codex/images/loadingAnimation.gif" align=center>');
    var id_brand = $('#brand').val();
    $.post('<?=site_url('imports/prefix_'.$prefix)?>',{id_brand:id_brand},function(data){
        $('#prefix_content').html(data);
    });
}
function save_prefix()
{
    var id_brand = $('#brand').val();
    var options = {
        data: {'id_brand':id_brand},
        dataType:'script',
        success: function(responseText) {
            alert(responseText);
          }
    };
    $('#prefix_form').ajaxSubmit(options);
}
</script>
<form method=post id="prefix_form" action="<?=site_url('imports/save_prefix_'.$prefix)?>">
Список брендов
<select id="brand" onchange="change_brand()">
<option value="0">-</option>
<?php
if(!empty($brands))
    foreach($brands as $brand)
        echo '<option value="'.$brand->id.'">'.$brand->title.'</option>';
?>
</select>
<div id="prefix_content">
</div>
</form>