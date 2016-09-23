<?php
global $CFG;

require_once("{$CFG->libdir}/formslib.php");

/**
 * This class build the form with the elements to register a new class in
 * Moodle from WIMS.
 * 
 * @author Domingos Savio
 *
 */
class form_cadastro_turma extends moodleform {
	/** @var Course ID */
	protected $courseid;
	
	public function __construct($course='0') {
		$this->courseid = $course;
		parent::__construct();
	}
	public function get_class_record() {
		global $DB;
		
		$result = new stdClass();
		//$result->id = 0;
		$result->nome_instituicao = '';
		$result->nome_classe = '';
		$result->nivel_turma = '';
		$result->nome_professor = '';
		$result->sobrenome_professor = '';
		$result->email_professor = '';
		$result->data_expiracao_classe = '';
		$result->numero_max_alunos = '';
		
		//pegar_info_classe_wims
		$dadosconfig = $DB->get_record('config_wim', array('id_curso' => 
				$this->courseid));
		
		if (!$dadosconfig) {
			return $result;
		}
		
		return $dadosconfig;		
	}

    function definition() {
		
    	$class = $this->get_class_record();
    	
		// Header
		$mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('register_class', 
        		'block_wims'));
        
        // Course ID
        $mform->addElement('hidden', 'course', $this->courseid);
        $mform->setType('course', PARAM_INT);
        
        // Institution name field
        $attributes = array('value' => $class->nome_instituicao,
        		            'title' => get_string('configinput0', 
        		            		'block_wims'),
        		            'size' => '50', 
        		            'maxlength' => '50');
		$mform->addElement('text','nomeinstituicao', get_string('configinput0',
				'block_wims'), $attributes);
		$mform->setType('nomeinstituicao', PARAM_TEXT);
		$mform->addRule('nomeinstituicao', null, 'required', null, 'client');

		// Class name field
		$attributes = array('value' => $class->nome_classe,
				'title' => 'Nome de sua classe no ambiente WIMS.',
				'size' => '30',
				'maxlength' => '30');
		$mform->addElement('text', 'nomeclasse', get_string('configinput1',
				'block_wims'), $attributes);
		$mform->setType('nomeclasse', PARAM_TEXT);
		$mform->addRule('nomeclasse', null, 'required', null, 'client');
		
		// Level class field
		$CLASS_LEVELS = array('E1' => 'E1');
		
		for ($i=2;$i<=6;$i++) {
			$CLASS_LEVELS += array('E'.$i => 'E'.$i);
		}
		for ($i=1;$i<=6;$i++) {
			$CLASS_LEVELS += array('H'.$i => 'H'.$i);
		}
		for ($i=1;$i<=5;$i++) {
			$CLASS_LEVELS += array('U'.$i => 'U'.$i);		
		}
		
		$CLASS_LEVELS += array('G' => 'G', 'R' => 'R');
		
		$attributes = array(
				'title' => 'Selecione o n&iacute;vel de sua turma.');
		$select = $mform->addElement('select', 'nivelturma', 
				get_string('configinput2', 'block_wims'), $CLASS_LEVELS, 
				$attributes);
		$mform->setType('nivelturma', PARAM_TEXT);
		$mform->addRule('nivelturma', null, 'required', null, 'client');
		$select->setSelected($class->nivel_turma);

		// Professor name field
		$attributes = array('value' => $class->nome_professor,
				'title' => 'Nome do professor referente a classe no '.
				                 'ambiente WIMS.',
				'size' => '15',
				'maxlength' => '15');
		$mform->addElement('text','nomeprofessor', get_string('configinput3', 
				'block_wims'), $attributes);
		$mform->setType('nomeprofessor', PARAM_TEXT);
		$mform->addRule('nomeprofessor', null, 'required', null, 'client');
				
		// Professor last name field
		$attributes = array('value' => $class->sobrenome_professor,
				'title' => 'Sobrenome do professor referente a classe '.
				                 'no ambiente WIMS.',
				'size' => '15',
				'maxlength' => '15');
		$mform->addElement('text','sobrenomeprofessor', 
				get_string('configinput4', 'block_wims'), $attributes);
		$mform->setType('sobrenomeprofessor', PARAM_TEXT);
		$mform->addRule('sobrenomeprofessor', null, 'required', null, 
				'client');

		// Professor email field
		$attributes = array('value' => $class->email_professor,
				'title' => 'Email para onde ser&aacute; enviado o'.
				                 ' c&oacute;digo para seu acesso a turma no '.
				                 'ambiente WIMS.',
				'size' => '50',
				'maxlength' => '50');
		$mform->addElement('text','emailprofessor', 
				get_string('configinput5', 'block_wims'), $attributes);
		$mform->setType('emailprofessor', PARAM_EMAIL);
		$mform->addRule('emailprofessor', null, 'required', null, 
				'client');

		// Expiration date field
		$attributes = array(
				'title' => 'Data onde a classe no ambiente WIMS '.
				                 'ir&aacute; expirar.');
		$date = $mform->addElement('date_selector','dataexpiracao', 
				get_string('configinput6', 'block_wims'), $attributes);
		$mform->setType('dataexpiracao', PARAM_TEXT);
		$mform->addRule('dataexpiracao', null, 'required', null, 'client');
		$date->setValue($class->data_expiracao_classe);

		// Professor email field
		$attributes = array('value' => $class->numero_max_alunos,
				'title' => 'N&uacute;mero m&aacute;ximo de alunos em '.
				                 'uma classe no ambiente WIMS.',
				'size' => '5',
				'maxlength' => '15');
		$mform->addElement('text','numeromaxalunos', get_string('configinput7',
				'block_wims'), $attributes);
		$mform->setType('numeromaxalunos', PARAM_INT);
		$mform->addRule('numeromaxalunos', null, 'required', null, 'client');
		
		$this->add_action_buttons();
    }
}
