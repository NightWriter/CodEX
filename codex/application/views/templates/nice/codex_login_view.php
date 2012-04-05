        <div class="codex-login-form">
            <div id="messages">
                <?php $this->codextemplates->loadInlineView('templates/'.$this->template.'/codex_messages');?>
            </div>
            <?php 
            	if ($this->codexadmin->primary_key)
                	echo form_open('login/validate','',array($this->codexadmin->primary_key=>$this->input->post($this->codexadmin->primary_key)));
            	else
                	echo form_open('login/validate');
                    echo $form_html; ?>

                    <input type="submit" name="submit" value="Login">

                    <?php echo '<div class="clear"></div>';
                echo form_close();
            ?>
        </div>
        <div class="clear"></div>
