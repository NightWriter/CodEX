var $default_max_mileage = 100000;

jQuery(document).ready(function(){
    
    //
    jQuery('.vehicle_form').submit(function(){
       jQuery('#template_maintenance').remove();
    });
    // при выборе бренда подтягуем модели
    jQuery('#brand_id').change(function(){
        var url = jQuery(this).data('load_url');
        var data = jQuery(this).closest('form').serialize();
        
        jQuery('#model_id').hide();
        jQuery('#loading_model').show();
        // если на странице есть поле выбора модели - очистим его
        if(jQuery('select[name=vehicle_id]').length)
            jQuery('select[name=vehicle_id]').html('');
        jQuery.post(url,data,function(response){
            
            jQuery('#loading_model').hide();
            
            
            jQuery('#model_id').html(response);
            jQuery('#model_id').show();
        });
    });
    
    // а при изменении модели - список модификаций. Но только если есть поле для их выбора
    jQuery('#model_id').change(function()
    {
            if(jQuery('select[name=vehicle_id]').length)
            {
                var url = jQuery(this).data('load_url');
                var data = jQuery(this).closest('form').serialize();
                
                jQuery.post(url,data,function(response)
                {
                    jQuery('select[name=vehicle_id]').html(response);
                });
            }
        
    });
    
    // при выборе класса покажем их категории
    jQuery('#class_id').change(function(){
        var url = jQuery(this).data('load_url');
        var data = jQuery(this).closest('form').serialize();
        
        jQuery('#category_id').hide();
        jQuery('#loading_category').show();
        // если на странице есть поле выбора модели - очистим его
        jQuery.post(url,data,function(response){
            
            jQuery('#loading_category').hide();
            
            
            jQuery('#category_id').html(response);
            jQuery('#category_id').show();
        });
    });
    
    // при выборе ТО для машины, формируем доп. поля
    jQuery('#maintenance').change(function(){
        
        if(jQuery(this).val() == 0)
        {
            jQuery('#block_maintenance').hide();
            return;
        }
        mileage = $default_max_mileage;
        var def_mileage = jQuery(this).find(':selected').data('mileage');
        if(def_mileage != 0)
            mileage = def_mileage;
        
        cnt = (Math.ceil($default_max_mileage/mileage));
        
       // var template = jQuery('#template_maintenance').find('input').clone();
        jQuery('#block_maintenance').html('');
        for(i=1;i<=cnt;i++)
        {
            var template = jQuery('#template_maintenance').find('div').clone();
            
            mileage_value = i*def_mileage;
            if(mileage_value > $default_max_mileage)
                mileage_value = $default_max_mileage;
                
            jQuery(template).find('input.mileage').val(mileage_value);
            jQuery(template).find('span.num').text(i);
            
            jQuery('#block_maintenance').append(template);
        }
        jQuery('#block_maintenance').show();
    });
    // устанавливаем value по умолчанию
    jQuery('input').each(function(){
        if(jQuery(this).val() == 0 && jQuery(this).attr('def-value') != '')
            jQuery(this).val(jQuery(this).attr('def-value'));
    });
});