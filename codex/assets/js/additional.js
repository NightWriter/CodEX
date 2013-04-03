var $default_max_mileage = 100000;

jQuery(document).ready(function(){
    
    // обновляем нажатие на Чекбокс 
    jQuery('body').on('click','.checkbox_updated',function(){
        var url = jQuery(this).attr('href');
        var primary_key = jQuery(this).data('primary_key');
        var primary_value = jQuery(this).data('primary_value');
        var value = jQuery(this).data('value');
        var field = jQuery(this).data('field');
        var table = jQuery(this).data('table');
        var $this = this;
        
        jQuery.post(url,{primary_key:primary_key,primary_value:primary_value,value:value,field:field,table:table},function(response){
            if(response.success)
            {
                jQuery($this).data('value',response.value);
                jQuery('#'+field+primary_value).attr('src',response.src);
            }
        },'json');
        return false;
    });
    // устанавливаем value по умолчанию
    jQuery('input').each(function(){
        if(jQuery(this).val() == 0 && jQuery(this).attr('def-value') != '')
            jQuery(this).val(jQuery(this).attr('def-value'));
    });
});