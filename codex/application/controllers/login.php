<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
include('codexcontroller.php');
class Login extends codexController{
    var $form = array();
    var $template;
    function Login(){
        codexController::codexController();
        
        $this->load->vars(array('asset_folder' => $this->config->item('codex_asset_folder')));
        $this->codexvalidation->set_error_delimiters('<div class="error-message">', '</div>'); 

        $this->form = array(
                    'username'=>array('class'=>'TextBox','attributes'=>array('class'=>'text'), 'label'=>$this->lang->line('codexlogin_username')),
                    'password'=>array('class'=>'Password','attributes'=>array('class'=>'text'), 'label'=>$this->lang->line('codexlogin_password')),
                );
        $this->codexforms->setup($this->form);
        
    }

    function index(){
        $data['form_action'] = 'login/validate';
        $data['form_html'] = $this->codexforms->getHTML();
        $data['messages'] = $this->codexmessages->get();
        
        $data['errors'] = array('username'=>$this->codexsession->flashdata('username'),'password'=>$this->codexsession->flashdata('password'));
        
        $data['button'] = $this->lang->line('codexlogin_login');
        $this->template                 = (isset($_COOKIE['codex_template']))? $_COOKIE['codex_template'] : $this->config->item("codex_template");

        $this->codextemplates->clearHTML();
        $this->codextemplates->docType('html5');
        $this->codextemplates->rawHeaderHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">');
        
        //$this->codextemplates->css('template-css',$this->config->item('codex_asset_folder').$this->template.'/css/codex_'.$this->template.'.css');
        $files = get_files('./codex/assets/'.$this->template.'/css/');
        if(!empty($files))
        {
            foreach($files as $k=>$file)
                $this->codextemplates->css('template-css-'.$k,$this->config->item('codex_asset_folder').$this->template.'/css/'.$file);
        }
        //
        $files = get_files('./codex/assets/'.$this->template.'/js/');
        if(!empty($files))
        {
            foreach($files as $k=>$file)
                $this->codextemplates->js('js-'.$k,$this->config->item('codex_asset_folder').$this->template.'/js/'.$file);
        }
        $this->codextemplates->loadView('templates/'.$this->config->item('codex_template').'/codex_login_view',$data);
        $this->codextemplates->setTitle($this->config->item('codex_site_title').' - '.$this->lang->line('codexlogin_login'));
        $this->codextemplates->printHTML();
    }
    
    function quit(){
        $redirect_to = $this->codexsession->userdata('login_success_redirect');
        $this->codexsession->sess_destroy();
        redirect(strtolower($redirect_to));
    }
    
    function validate(){
        if(empty($_POST))
        {
            $this->index();
            exit;
        }
        $rules['username'] = "trim|htmlspecialchars|required"; 
        $rules['password'] = "trim|required|sha1";
        $this->codexvalidation->set_rules($rules);
        if($this->codexvalidation->run() == FALSE){
                $validation_errors = $this->codexmessages->get('form');
                $errors = array();
                
                foreach($validation_errors as $validation_error){
                    $parts = explode('|',$validation_error);
                    $errors[$parts[0]] = $parts[1];
                    
                    $this->codexsession->set_flashdata($parts[0],$parts[1]);
                }

                $this->codexforms->setValidationErrors($errors);
                
                
            $this->codexforms->populateWithArrayInfo($_POST);
            $this->index();
        }
        else{
            if($this->codexlogin->check($this->input->post('username'),$this->input->post('password'))){
                $this->event->trigger('userLoginSuccess',array($this->input->post('username'),$this->input->post('username')));
                redirect($this->codexsession->userdata('login_success_redirect'));
            }
            $this->codexmessages->add('failure',$this->lang->line('codexlogin_invalid'),"",true);
            $this->index();
        }
    }
    
    function auth()
    {
        if($this->uri->segment(1) != 'login'){
            if(!$this->isLoggedIn()){
                $this->index();
            }
        }
    }
    
    function setPreDefinedAccessLevels(){ $this->codexlogin->setPreDefinedAccessLevels(); }
    function setConfig($config){ $this->index(); }
    function add(){ $this->index(); }
    function edit(){ $this->index(); }
    function execute_add(){ $this->index(); }
    function execute_edit(){ $this->index(); }
    function delete(){ $this->index(); }
    function delete_confirm($id){ $this->index(); }
    function manage (){ $this->index(); }
    function pluginCallback (){ $this->index(); }
}
?>
