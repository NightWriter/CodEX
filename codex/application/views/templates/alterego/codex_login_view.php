<div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">CodEX Adminpanel | <?=$this->lang->line('codex_login')?></a>
          <div class="nav-collapse">
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container" style="width:600px; margin-top: 60px;">
        <form method="post" action="<?=site_url('login/validate')?>" class="form-horizontal">
        <fieldset>
            <div class="control-group <? if(!empty($errors['username'])): ?>error<? endif ?>">
                <label class="control-label" for="username"><?=$this->lang->line('codex_username')?></label>
                <div class="controls">
                    <input type="text" name="username" id="username" /> 
                    <? if(!empty($errors['username'])): ?>
                        <span class="help-inline"><?=$errors['username']?></span>
                    <? endif ?>
                </div>
            </div>
            <div class="control-group <? if(!empty($errors['password'])): ?>error<? endif ?>">
                <label class="control-label" for="password"><?=$this->lang->line('codex_password')?></label>
                <div class="controls">
                    <input type="password" name="password" id="password" /> 
                    <? if(!empty($errors['password'])): ?>
                        <span class="help-inline"><?=$errors['password']?></span>
                    <? endif ?>
                </div>
            </div>
        </fieldset> 
        <div class="form-actions" style="padding-left:200px;background-color:#fff">
            <button type="submit" name="submit" class="btn btn-primary"><?=$this->lang->line('codex_login_adminpanel')?></button>    
        </div>
        </form>
    </div> <!-- /container -->