<?php
require_once('../../../config.php');
require_once('../scripts/functions.php');
require_once('form_cadastro_turma.php');
require_once('ClienteWim.php');

global $CFG, $DB;

$courseid = required_param('course', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
	print_error('invalidcourse'.$courseid, 'block_wims', $courseid);
}

require_login($courseid);

$info_turma = pegar_info_classe_wims($courseid);
if (!empty($info_turma)) {
    $referencia = $info_turma->ref_classe_wims;
    
    if (!empty($referencia)) {
    	print_error('Error');
    }
}

$settingsnode = $PAGE->settingsnav->add(get_string('pluginname', 'block_wims'));
$editurl = new moodle_url('/blocks/wims/phppages/cadastro_turma.php', 
		array('course' => $courseid));
$editnode = $settingsnode->add(get_string('register_class', 'block_wims'), 
		$editurl);
$editnode->make_active();

$PAGE->set_url('/blocks/wims/phppages/cadastro_turma.php', 
		array('course' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('register_class', 'block_wims'));

$cadastro_turma = new form_cadastro_turma($courseid);

if($cadastro_turma->is_cancelled()) {
	// Cancelled forms redirect to the course main page.
	$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
	redirect($courseurl);
} else if ($cadastro_turma->get_data()) {
	// We need to add code to appropriately act on and store the submitted data
	// but for now we will just redirect back to the course main page.
	
	//$day = explode("/",$_POST["dataexpiracao"]);
	$_POST["dataexpiracao"] = '20/07/2017';//$day[2].$day[1].$day[0];	
	$_POST['senhaprofessor'] = gera_senha(8);
	$_POST['senhaclasse'] = gera_senha(8);
	
	$session_name = 'wims'.$USER->id;
	$_SESSION["$session_name"]["senhaprofessor"] = $_POST['senhaprofessor'];
	$_SESSION["$session_name"]["senhaclasse"] = $_POST['senhaclasse'];
	$_SESSION["$session_name"]["emailprofessor"] = $_POST['emailprofessor'];

	/*try {
	$client = new ClienteWim();
	} catch (Exception $e) {
		if ($e instanceof WSFault) {
                printf("Soap Fault: %s\n", $e->Reason);
        } else {
                printf("Message = %s\n",$e->getMessage());
        }
		
	}*/
	/*$resMessage = $client->cadastrarTurma($_POST);

	if ($resMessage) {
	    $simplexml = new SimpleXMLElement($resMessage->str);

        if($simplexml->turmacadastrada == 'true'){
	        redirect("$CFG->wwwroot/blocks/wim/phppages/confirma_turma.php?".
	        		"course=$id_curso&session=$simplexml->session", 
	        		get_string('msgsucesso', 'block_wim'));
			//print_footer( $course );
        } /*else {
        	print_error(get_string('msgerro_not_ws', 'block_wims'));
            redirect("$CFG->wwwroot/course/view.php?id=$id_curso", 
            		get_string('msgerro_not_ws', 'block_wim'));*/
        //}
    /*} //else {
        redirect("$CFG->wwwroot/course/view.php?id=$id_curso", 
        		get_string('msgerro_not_ws', 'block_wim'));
    //}
    print_error(get_string('msgerro_not_ws', 'block_wims'));*/
	$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
	redirect($courseurl);
} else {
	// form didn't validate or this is the first display
	//$site = get_site();
	echo $OUTPUT->header();
	$cadastro_turma->display();
	echo $OUTPUT->footer();
}
?>
