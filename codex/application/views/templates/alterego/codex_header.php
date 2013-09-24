<script type="text/javascript" src="<?=base_url()?>codex/assets/js/additional.js"></script>

<script type="text/javascript" src="<?=base_url()?>codex/assets/js/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript" src="<?=base_url()?>codex/assets/js/ajaxupload.3.5.js"></script>
<script type="text/javascript">
    function elFinderBrowser (field_name, url, type, win) {
        var elfinder_url = '/codex/elfinder.html';    // use an absolute path!
        tinyMCE.activeEditor.windowManager.open({
            file: elfinder_url,
            title: 'elFinder 2.0',
            width: 900,  
            height: 450,
            resizable: 'yes',
            inline: 'yes',    // This parameter only has an effect if you use the inlinepopups plugin!
            popup_css: false, // Disable TinyMCE's default popup CSS
            close_previous: 'no'
        }, {
            window: win,
            input: field_name
        });
        return false;
    }


    jQuery(document).ready(function(){
        
        jQuery('.alias').keyup(function(e){
            jQuery('#alias_yes').hide();
            jQuery('#alias_no').hide();
            
            var obj = jQuery(this);
            var id = '<?=(!empty($_GET['id']))?$_GET['id']:0?>';
            jQuery.post('<?=site_url('codexcontroller/check_alias')?>',{id:id,text:obj.val(),attr:obj.attr('rel')},function(data){
                if(data == 1)
                    jQuery('#alias_yes').show();
                else
                    jQuery('#alias_no').show();
            });
            
        });
        
        if(jQuery('#import').text())
        {
            ajax_upload = new AjaxUpload(
                    '#import', 
                    {
                        action: jQuery('#import').attr('href'),
                        name: 'import',
                        autoSubmit: true,
                        submit_empty: true,
                        responseType: 'json',
                        allowedExtensions: ['csv','xls','xlsx'],
                        onChange: function(file, extension){
                            if (jQuery.inArray(extension[0], this['_settings']['allowedExtensions']) == -1)
                            {
                                alert('Допустимый формат файла: csv|xls|xlsx');
                                return false;
                            }                
                        },
                        onComplete: function(file, response)
                            {
                                if (response.success)
                                {
                                    location.reload();
                                    //jQuery('.info').hide();
                                    //jQuery('.pagesize').change();
                                }else if(response.messages)
                                {
                                    alert(response.messages);
                                }else{
                                    alert('<?=$this->lang->line('codex_no_pridvidennaya_situation')?>');
                                }
                            }
                    }
                );
        }
    });
    <? if(empty($hide_tinymce)): ?>
    
    $(document).ready(function() {
        try{
        $('textarea.editor').tinymce({
        //tinyMCE.init({
            // General options
            //mode : "textareas",
            //editor_deselector : "without_tinymce",
            theme : "advanced",
            script_url:'<?=base_url()?>codex/assets/js/tiny_mce/tiny_mce.js',
            plugins : "ibrowser,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,images",
            theme_advanced_buttons2_add : "ibrowser",
            // Theme options
            theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,images,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,
            // file_browser_callback : 'elFinderBrowser',
            // Example content CSS (should be your site CSS)
            content_css : "css/content.css",

            // Drop lists for link/image/media/template dialogs
            template_external_list_url : "lists/template_list.js",
            external_link_list_url : "lists/link_list.js",
            external_image_list_url : "lists/image_list.js",
            media_external_list_url : "lists/media_list.js",

            extended_valid_elements : "iframe[name|src|framespacing|border|frameborder|scrolling|title|height|width],object[declare|classid|codebase|data|type|codetype|archive|standby|height|width|usemap|name|tabindex|align|border|hspace|vspace]",
        
            // Replace values for the template plugin
            template_replace_values : {
                username : "Some User",
                staffid : "991234"
            }
        });
        } catch(e) {
            console.error(e.message);
        }
    });
    
    <? endif; ?>
    function selChange(obj) { 
        if(obj.value == 1){
            $.post('<?=site_url('edit_menu/get_articles')?>',function(data){
                //if(data){
                    $('#ready_link').html(data);
                    $('#ready_link').show();
                //}
            });
        }else if(obj.value == 2){
            $.post('<?=site_url('edit_menu/get_pages')?>',function(data){
                //if(data){
                    $('#ready_link').html(data);
                    $('#ready_link').show();
                //}
            });
        }else{
            $('#ready_link').hide();
        }
    } 

    function linkChange(obj) {
       $.post('<?=site_url('edit_menu/get_link')?>',{type:$('#type').val(),id:obj.value},function(data){
            $('#user_link').val(data);
       });
     }
</script>
    <!-- Le styles -->
    <style>
      body {
        padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
 <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?=site_url()?>"><?=$this->config->item('codex_site_title')?></a>
          <div class="nav-collapse">
            <?php 
                    if($user_name = $this->codexsession->userdata('user_name')){?>
            <ul class="nav" style="float:right">
                <li><a><?php echo sprintf($this->lang->line('codexadmin_logged_in_as'), $user_name)?></a></li>
                <li class="active"><a href="<?php echo base_url().'index.php'; ?>" target="_blank"><?=$this->lang->line('codex_view_website')?></a></li>
                <li class="active"><a href="<?=site_url('login/quit')?>"><?=$this->lang->line('codexadmin_logout')?></a></li>
            </ul>
             <?php } 
                ?>
          </div><!--/.nav-collapse -->
        </div>
      </div>
</div>
<!-- Breadcrumbs -->
<div>
    <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_crumbs',array('crumbs'=>$this->codexcrumbs->get(),'selected'=>$this->codexcrumbs->getSelected()));?>
</div>
<!-- end breadcrumbs --> 
<div class="container-fluid">
  <div class="row-fluid">
    <div class="span2" style="width:20%;">
        <?php echo $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_navigation.php'); ?>
      <!--Sidebar content-->
    </div>
    <div class="span10" style="width:80%;margin:0">
        <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_search_form');?>
        <div class="page-header">
            <h1 style="float:left;"><?php echo mb_ucfirst($this->page_header); ?></h1>
            
            <?php if($this->add_link) echo '
            <a class="btn btn-success"  style="float:right;margin:0" href="'.site_url($this->add_link).'"><i class="icon-plus icon-white"></i> '.$this->lang->line('codex_add_new_item').'</a> '; ?>
            
            <? if( $this->export_link ): ?>
                <a class="btn btn-success" style="float:right;margin-right:10px" href="<?=site_url($this->export_link)?>"><i class="icon-plus icon-white"></i> <?=$this->lang->line('codex_export')?></a>
            <? endif; ?>
            
            <? if( $this->import_link ): ?>
                <a class="btn btn-success" id="import" style="float:right;margin-right:10px" href="<?=site_url($this->import_link)?>"><i class="icon-plus icon-white"></i> <?=$this->lang->line('codex_import')?></a>
            <? endif; ?>
            <div style="clear:both"></div>   
        </div>
<?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_messages');?> 
