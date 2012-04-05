<link type="text/css" rel="stylesheet" href="<?=base_url()?>codex/assets/css/themes/base/ui.all.css">
<script type="text/javascript" src="<?=base_url()?>codex/assets/js/ui.core.js"></script>
<script type="text/javascript" src="<?=base_url()?>codex/assets/js/ui.accordion.js"></script>

<style type="text/css">
.popup_window {
    position: absolute;
    margin: 155px 0px 0px 119px;
    z-index: 999999;
    display: none;
    width: 265px;
}

.popup_blue_title {
    color: #1b919f;
    font-size: 13px;
    padding: 17px 0 0 0;
}

.popup_container {
    padding: 21px 5px 0;
}

.top_left {
    padding: 0 0 0 27px;
    height: 25px;
    background: url(../images/window/top_left.png) no-repeat left top;
}

.top_right {
    padding: 0 27px 0 0;
    height: 25px;
    background: url(../images/window/top_right.png) no-repeat right top;
}

.top_center {
    height: 25px;
    background: url(../images/window/top_center.png) repeat-x right top;
    position: relative;
}
/*bottom*/
.bottom_left {
    padding: 0 0 0 27px;
    height: 26px;
    background: url(../images/window/bottom_left.png) no-repeat left bottom;
}

.bottom_right {
    padding: 0 27px 0 0;
    height: 26px;
    background: url(../images/window/bottom_right.png) no-repeat right bottom;
}

.bottom_center {
    height: 26px;
    background: url(../images/window/bottom_center.png) repeat-x right bottom;
}
/*middle*/
.middle_left {
    padding: 0 0 0 6px;
    background: url(../images/window/middle_left.png) repeat-y left bottom;
}

.middle_right {
    padding: 0 8px 0 0;
    height: 100%;
    background: url(../images/window/middle_right.png) repeat-y right bottom;
}

.middle_center {
    height: 100%;
    background: #fff;
}

.close_btn {
    position: absolute;
    cursor: pointer;
    top: 12px;
    right: -9px;
    background: url(../images/window/close_btn.gif) no-repeat right top;
    height: 23px;
    padding: 2px 30px 0 0;
    color: #b9b9b9;
    font-size: 11px;
    font-family: Tahoma;
}
.alias_img{
    width: 21px;
    margin-bottom: -4px;
}
</style>
<script type="text/javascript" src="<?=base_url()?>codex/assets/js/jquery.form.js"></script>
<script type="text/javascript" src="<?=base_url()?>codex/assets/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    //
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
    });
    tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "advanced",
        plugins : "ibrowser,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
        theme_advanced_buttons2_add : "ibrowser",
        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
        content_css : "css/content.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js",

        // Replace values for the template plugin
        template_replace_values : {
            username : "Some User",
            staffid : "991234"
        }
    });

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
    
    function showPopupWindow(id){
        $('#'+id).show();
    }
    
    function hidePopupWindow(id){
        $('#'+id).hide();
    }
    
    $(document).ready(function(){
        $('#add_user_id').click(function(){
            var separator = '';
            var users = $('#users_to').val();
            if(users != '')
                separator = ',';
            $('#users_to').val(users+separator+$('#users_id').val());
            
            
            $('#users_id').hide();
            $('#add_user_id').attr('disabled','disabled');
        });
        
        $('#users_id').hide();
        $('#search_user').keyup(function(){
            $.post('<?=site_url('subscrible/ajax_search')?>',{value:$('#search_user').val()},function(data){
                if(data){
                    $('#users_id').html(data);
                    $('#users_id').show();
                    $('#add_user_id').removeAttr('disabled');
                }
            });
        });
    });
    
//--> 
</script>
        
                 
        <div class="container">

            <div id="header">
                <h1><?php echo codexAnchor(site_url(),$this->config->item('codex_site_title')); ?></h1>
                <?php 
                    if($user_name = $this->codexsession->userdata('user_name')){?>
                        <div id="login-nav">
                            <?php echo sprintf($this->lang->line('codexadmin_logged_in_as'), $user_name).'<br>'.codexAnchor("login/quit",$this->lang->line('codexadmin_logout')); ?>
                        </div>
                    <?php } 
                ?>
                <div class="clear"></div>
            </div>

            <div id="sub-header">
                <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_crumbs',array('crumbs'=>$this->codexcrumbs->get(),'selected'=>$this->codexcrumbs->getSelected()));?>
                <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_search_form');?>
                <div class="clear"></div>
            </div>

            <div id="content">
        
                <div id="main-nav">
                    <?php echo $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_navigation.php'); ?>
                </div>

                <div id="body">
                <div class="popup_window" id="window_for_subscrible" style="display:none">
            <div class="top_left">
                <div class="top_right">
                    <div class="top_center">
                        <div class="popup_blue_title">Поиск</div>
                        <div class="close_btn" onclick="hidePopupWindow('window_for_subscrible')">
                        </div>
                    </div>
                </div>
            </div>
            <div class="middle_left">
                <div class="middle_right">
                    <div class="middle_center">
                        <div class="popup_container">
                          <form>
                            <table cellpadding="0" cellspacing="0" width="100%" class="currency_table">
                                <tr>
                                    <td colspan="2">
                                        <input type="text" id="search_user" style="width:85%" value="ID | Логин | E-Mail | ФИО" onblur="if(this.value == '') this.value = 'ID | Логин | E-Mail | ФИО'" onfocus="this.value = ''" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                    <select id="users_id" style="display:none">
                                        
                                    </select>
                                    <input type="button" value="Добавить" id="add_user_id" disabled="disabled" /></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="sep_td">&nbsp;</td>
                                </tr>
                            </table>
                          </form>  
                        </div>
                    </div>
                </div>
            </div>
            <div class="bottom_left">
                <div class="bottom_right">
                    <div class="bottom_center">
                        
                    </div>
                </div>
            </div>
        </div>
                    <div class="title">
                        <h1><?php echo mb_ucfirst($this->page_header); ?></h1>
                        <div id="add-new">
                            <?php if($this->add_link) echo codexAnchor($this->add_link,'Добавить запись'); ?>
                        </div>    
                    </div>
                    

                    <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_messages');?>
