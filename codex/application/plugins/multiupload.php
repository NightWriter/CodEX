<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Multiupload extends codexForms
{
    var $CI, $_params = array(); // параметры вывода плагина
    
    function Multiupload($name,$params) {
        $this->CI = &get_instance();;
        $this->_params = $params; // заполнение параметров значениями из yml-файла
        codexForms::initiate($name,$params);
    }

    function prepForDisplay($value){
        return $value;
    }

    
	function getHTML()
	{
        $CI = &get_instance();
        $CI->codextemplates->jsFromAssets('qq','fileuploader.js');
        $CI->codextemplates->cssFromAssets('qq-fileuploader','fineuploader.css');
        $params = $this->_params;
        $params_array = $this->_params['params'];
        /**
        *  Element - элемент выбора файлов
        *  action - путь к обработчику загрузки
        *  allowedExtensions - допустимые расширения файла. По-умолчанию - допустимы все. 
        *  Multiple - Возможность множественной загрузки
        *  sizeLimit - верхний лимит размера файла.
        */
        $html = "";
        $html.= "
            <script>
            jQuery(document).ready(function()
            {
                var uploader = new qq.FileUploader({
                        element: document.getElementById('upload_link'),
                        action: '".(!empty($params_array['action'])?$params_array['action']:'/backend.php?c=codexcontroller&m=ajax_file_upload')."',
                        allowedExtensions: ".(!empty($params_array['allowed_extensions'])?"[".$params_array['allowed_extensions']."]":"[]").",
                        multiple: ".(!empty($params_array['multiple']) && $params_array['multiple'] && !$this->CI->codexadmin->active_id?'true':'false').", 
                        sizeLimit: ".(!empty($params['size_limit'])?$params['size_limit']:0).",
                        onComplete: function(id, fileName, responseJSON){
            ";
        // если открыт режим редактирования или разрешена только одна запись - после загрузки новой фотографии удаляем все ранее загруженные
        if($this->CI->codexadmin->active_id || empty($params_array['multiple']))
        {
            $html.= "
                            $('.files_place').empty();
            
            ";
        }    
        $html.= "
                            
                            $('.files_place').append('<input class=\"added_field\" type=\"hidden\" name=\"".$this->element_name."[]\" value=\"'+responseJSON.results[0].name+'\"/>');    
                        },
                        showMessage: function(message){ alert(message); }
                    });
            });
            </script>
        ";
        $html.= '<input type="hidden" name="multiupload" value="1"/>';
        $html.= "<a class=\"upload_link\" id=\"upload_link\">Выбрать файлы</a>";
        $html.= "<div class='files_place'>";
        $html.= '<input type="hidden" class="added_field" name="'.$this->element_name.'[]" '.$this->getAttributes($this->attributes).'" value="'.$this->value.'"/>';
        if($this->CI->codexadmin->active_id)
            $html.= 'Uploaded files: '.$this->value;
        $html.= "</div>";

		return $html;
	}
}

?>
