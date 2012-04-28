<script type="text/javascript" src="<?=base_url()?>codex/assets/js/ajaxupload.3.5.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
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
            
            <? if( $this->import_link ): ?>
                <a class="btn btn-success" id="import" style="float:right;margin-right:10px" href="<?=site_url($this->import_link)?>"><i class="icon-plus icon-white"></i> <?=$this->lang->line('codex_import')?></a>
            <? endif; ?>
            <div style="clear:both"></div>   
        </div>
<?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_messages');?>
