<?php
//require_once('../scripts/functions.php');
//require_once('../../../config.php');

//http://www.argnet.tk/
class ClienteWim extends WSClient
{
	public $url = 'http://200.129.43.130/serviceWims/ServidorWim.php';
	#public $url = 'http://200.129.43.165/serviceWims/ServidorWim.php';
	#public $url = 'http://192.168.250.241/serviceWims/ServidorWim.php';

	public function __construct(){
		global $CFG;
		$my_cert = ws_get_cert_from_file("../keys/alice_cert.cert");
		$rec_cert = ws_get_cert_from_file($CFG->dirroot.'/blocks/wims/keys/bob_cert.cert');
		$pvt_key = ws_get_key_from_file($CFG->dirroot.'/blocks/wims/keys/alice_key.pem');
		$policy_xml = file_get_contents($CFG->dirroot.'/blocks/wims/keys/policy.xml');
		$policy = new WSPolicy($policy_xml);
		$sec_token = new WSSecurityToken(array("privateKey" => $pvt_key,
                                           "receiverCertificate" => $rec_cert));
		if (!empty($sec_token) and !empty($policy)) {
		parent::__construct(array(
				  				  "securityToken" => $sec_token, 
                                  "useSOAP" => 1.2,
                                  "policy" => $policy,
                                  "useWSA" => true,
                                  "to" => $this->url));
		}
	}

	public function cadastrarTurma($post){
		$nome_instituicao = $post["nomeinstituicao"];
		$nome_classe = $post["nomeclasse"];
		$nivel_turma = $post["nivelturma"];
		$nome_professor = $post["nomeprofessor"];
		$sobrenome_professor = $post["sobrenomeprofessor"];
		$email_professor = $post["emailprofessor"];
		$senha_professor = $post["senhaprofessor"];
		$senha_classe = $post["senhaclasse"];
		$data_expiracao_classe = $post["dataexpiracao"];
		$numero_max_alunos	= $post["numeromaxalunos"];

		$reqPayloadString=<<<XML
     <ns1:cadastrarTurma xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <instituicao>$nome_instituicao</instituicao>
		     <nomeclasse>$nome_classe</nomeclasse>
		     <level>$nivel_turma</level>
		     <nomeprofessor>$nome_professor</nomeprofessor>
		     <sobrenomeprofessor>$sobrenome_professor</sobrenomeprofessor>
		     <email>$email_professor</email>
		     <senhaprofessor>$senha_professor</senhaprofessor>
		     <senhaclasse>$senha_classe</senhaclasse>
		     <dataexp>$data_expiracao_classe</dataexp>
		     <limite>$numero_max_alunos</limite>
     </ns1:cadastrarTurma>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/cadastrarTurma"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}

	}

	function confirmaCadastroTurma($post){
		$codigo_acesso = $post["codigo_acesso"];
		$session = $post["session"];

		$reqPayloadString=<<<XML
     <ns1:confirmaCadastroTurma xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <codigoacesso>$codigo_acesso</codigoacesso>
			 <session>$session</session>
     </ns1:confirmaCadastroTurma>
XML;
		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/confirmaCadastroTurma"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function acessaProfessorTurma($id_curso){
		global $CFG;
		//$id_curso	= $_GET['course'];

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$reqPayloadString=<<<XML
     <ns1:loginProfessor xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <referencia>$referencia</referencia>
			 <senha>$senha_professor</senha>
     </ns1:loginProfessor>
XML;
		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/loginProfessor"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}


	function exportarAluno($id_curso,$id_usuario){
		global $CFG;

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$sqlusers = "SELECT * FROM {$CFG->prefix}user WHERE id = $id_usuario";
		$linhausers = get_records_sql($sqlusers);


		$userfirstname = current($linhausers)->firstname;
		$userlastname = current($linhausers)->lastname;

		$count = strlen(current($linhausers)->id);

		$numero_letras = 5 - $count;
		$userlogin = current($linhausers)->id;

		for($ni = 0; $ni < $numero_letras; $ni++){
			$userlogin = '0'.$userlogin;
		}

		$senha_wims = gera_senha(8);

		$reqPayloadString = <<<XML
      <ns1:cadastraAlunoUnicoInterface xmlns:ns1="http://php.axis2.org/samples">
       <referencia>$referencia</referencia>
	   <senhaprof>$senha_professor</senhaprof>
	   <senha>$senha_wims</senha>
	   <login>$userlogin</login>
	   <nome>$userfirstname</nome>
	   <sobrenome>$userlastname</sobrenome>
	  </ns1:cadastraAlunoUnicoInterface>
	   
XML;

		echo '<pre>';
		print_r($reqPayloadString);
		echo '</pre>';
		
		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/cadastraAlunoUnicoInterface"));
			$resMessage = $this->request($reqMessage);
			if ($resMessage)
			{
				$simplexml = new SimpleXMLElement($resMessage->str);
				if($simplexml->cadastroSucesso == 'true'){
					$aluno = new Object();
					$aluno->id_user = $id_usuario;
					$aluno->login_wims = $userlogin;
					$aluno->senha_wims = $senha_wims;
					$aluno->id_curso = $id_curso;
					insert_record("users_wims", $aluno);
				}
				return $resMessage;
			}
		}
		catch (Exception $e) {
			//delete_records('users_wims', 'id_curso' , $id_curso);
			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function exportarUsuarios($id_curso){
		global $CFG;

		$context = get_context_instance(CONTEXT_COURSE, $id_curso);

		$linhausers = listar_usuarios_curso($context->id,'*');

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$reqPayloadString = <<<XML
      <ns1:cadastroAlunos xmlns:ns1="http://php.axis2.org/samples">
       <referenciaturma>$referencia</referenciaturma>
	   <senha>$senha_professor</senha>
XML;
		foreach($linhausers as $vetorlinhausers){

			$userfirstname = $vetorlinhausers->firstname;
			$userlastname = $vetorlinhausers->lastname;
			$useremail = $vetorlinhausers->email;

			$count = strlen($vetorlinhausers->userid);

			$numero_letras = 5 - $count;
			$userlogin = $vetorlinhausers->userid;

			for($ni = 0; $ni < $numero_letras; $ni++){
				$userlogin = '0'.$userlogin;
			}

			$user = pegar_info_aluno($id_curso, $vetorlinhausers->userid);

			if(empty(current($user)->senha_wims)){
				$aluno = new Object();
				$aluno->id_user = $vetorlinhausers->userid;
				$aluno->login_wims = $userlogin;
				$aluno->senha_wims = gera_senha(8);
				$aluno->id_curso = $id_curso;
				insert_record("users_wims", $aluno);
			}else{
				$aluno->senha_wims = current($user)->senha_wims;
			}

			$reqPayloadString .= <<<XML
      <aluno>
      <login>$userlogin</login>
      <nome>$userfirstname</nome>
      <sobrenome>$userlastname</sobrenome>
      <email>$useremail</email>
      <senha>$aluno->senha_wims</senha>
      </aluno>
XML;

		}
		$reqPayloadString .= <<<XML
      </ns1:cadastroAlunos>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/cadastroAlunos"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {
			delete_records('users_wims', 'id_curso' , $id_curso);
			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function importarNotas($id_curso){
		global $CFG;

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$reqPayloadString = <<<XML
      <ns1:acessoNota xmlns:ns1="http://php.axis2.org/samples">
       <referenciaturma>$referencia</referenciaturma>
	   <senha>$senha_professor</senha>
      </ns1:acessoNota>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/acessoNota"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {
			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function importarSheets($id_curso){
		global $CFG;
		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$reqPayloadString=<<<XML
     <ns1:retornaSheet xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <referencia>$referencia</referencia>
			 <senha>$senha_professor</senha>
     </ns1:retornaSheet>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/retornaSheet"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function recuperaQuestoesSheet($id_curso,$id_assignment){
		global $CFG;
		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$sqlreferencia_sheet_wims	= "SELECT id_sheet FROM {$CFG->prefix}assignment_sheetwims WHERE id_curso=$id_curso AND id_assignment=$id_assignment";
		$linhareferenciasheet	= get_records_sql($sqlreferencia_sheet_wims);
		$referencia_sheet		= current($linhareferenciasheet)->id_sheet;

		$reqPayloadString=<<<XML
     <ns1:acessoQuestoesSheetProfMoodle xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <referencia>$referencia</referencia>
			 <senha_prof>$senha_professor</senha_prof>
			 <sheet>$referencia_sheet</sheet>
     </ns1:acessoQuestoesSheetProfMoodle>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/acessoQuestoesSheetProfMoodle"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}

	}

	function acessaSheetProfessor($id_curso,$id_assignment){
		global $CFG;
		//$id_curso	= $_GET['course'];

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;
		$senha_professor = current($info_turma)->senha_professor;

		$sqlreferencia_sheet_wims	= "SELECT id_sheet FROM {$CFG->prefix}assignment_sheetwims WHERE id_curso=$id_curso AND id_assignment=$id_assignment";
		$linhareferenciasheet	= get_records_sql($sqlreferencia_sheet_wims);
		$referencia_sheet		= current($linhareferenciasheet)->id_sheet;

		$reqPayloadString=<<<XML
     <ns1:acessoSheetProfMoodle xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <referencia>$referencia</referencia>
			 <senha_prof>$senha_professor</senha_prof>
			 <sheet>$referencia_sheet</sheet>
     </ns1:acessoSheetProfMoodle>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/acessoSheetProfMoodle"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function acessaSheetAluno($id_curso,$id_assignment,$id_aluno){
		global $CFG;
		//$id_curso	= $_GET['course'];

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;

		$sqlreferencia_sheet_wims	= "SELECT id_sheet FROM {$CFG->prefix}assignment_sheetwims WHERE id_curso=$id_curso AND id_assignment=$id_assignment";
		$linhareferenciasheet	= get_records_sql($sqlreferencia_sheet_wims);
		$referencia_sheet		= current($linhareferenciasheet)->id_sheet;

		$user = pegar_info_aluno($id_curso, $id_aluno);
		$login_aluno = current($user)->login_wims;
		$senha_aluno = current($user)->senha_wims;

		$reqPayloadString=<<<XML
     <ns1:acessoSheetAlunoMoodle xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <referencia>$referencia</referencia>
			 <login_aluno>$login_aluno</login_aluno>
			 <senha_aluno>$senha_aluno</senha_aluno>
			 <sheet>$referencia_sheet</sheet>
     </ns1:acessoSheetAlunoMoodle>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/acessoSheetAlunoMoodle"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}

	function recuperaQuestoesSheetProfessor($id_curso,$id_assignment){
		global $CFG;
		//$id_curso	= $_GET['course'];

		$info_turma = pegar_info_classe_wims($id_curso);
		$referencia = current($info_turma)->ref_classe_wims;

		$sqlreferencia_sheet_wims	= "SELECT id_sheet FROM {$CFG->prefix}assignment_sheetwims WHERE id_curso=$id_curso AND id_assignment=$id_assignment";
		$linhareferenciasheet	= get_records_sql($sqlreferencia_sheet_wims);
		$referencia_sheet		= current($linhareferenciasheet)->id_sheet;

		$user = pegar_info_aluno($id_curso, $id_aluno);
		$login_aluno = current($user)->login_wims;
		$senha_aluno = current($user)->senha_wims;

		$reqPayloadString=<<<XML
     <ns1:acessoSheetAlunoMoodle xmlns:ns1="http://wso2.org/wsfphp/samples">
     		 <referencia>$referencia</referencia>
			 <login_aluno>$login_aluno</login_aluno>
			 <senha_aluno>$senha_aluno</senha_aluno>
			 <sheet>$referencia_sheet</sheet>
     </ns1:acessoSheetAlunoMoodle>
XML;

		try {
			$reqMessage = new WSMessage($reqPayloadString,
			array("to" => $this->url,"action" => "http://wso2.org/wsfphp/samples/acessoSheetAlunoMoodle"));
			// Send request and capture response
			$resMessage = $this->request($reqMessage);
			return $resMessage;
		}
		catch (Exception $e) {

			if ($e instanceof WSFault) {
				printf("Soap Fault: %s\n", $e->Reason);
			} else {
				printf("Message = %s\n",$e->getMessage());
			}
		}
	}


}
?>
