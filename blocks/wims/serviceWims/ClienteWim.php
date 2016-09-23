<?php
class ClienteWim extends WSClient
{
	public function __construct(){
		$my_cert = ws_get_cert_from_file("keys/alice_cert.cert");
		$my_key = ws_get_key_from_file("keys/alice_key.pem");
		$rec_cert = ws_get_cert_from_file("keys/bob_cert.cert");
		$policy_xml = file_get_contents("keys/policy.xml");
		$policy = new WSPolicy($policy_xml);
		$sec_token = new WSSecurityToken(array("privateKey" => $my_key,
                                           "certificate" => $my_cert,
                                           "receiverCertificate" => $rec_cert));
		parent::__construct(array(
				  				  "securityToken" => $sec_token, 
                                  "useSOAP" => 1.2,
                                  "policy" => $policy,
                                  "useWSA" => true,
                                  "to" => 'http://localhost/serviceWims/ServidorWim.php'));
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
			array("to" => "http://localhost/serviceWims/ServidorWim.php",
                                      "action" => "http://wso2.org/wsfphp/samples/cadastrarTurma"));
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


$client = new ClienteWim();

$post = array();
$post["nomeinstituicao"] = 'UFC' ;
$post["nomeclasse"] = 'CLASSE';
$post["nivelturma"] = 'U1';
$post["nomeprofessor"] = 'ALLYSON';
$post["sobrenomeprofessor"] = 'BONETTI';
$post["emailprofessor"] = 'danieldgt@gmail.com';
$post["senhaprofessor"] = 'SENHA';
$post["senhaclasse"] = 'SENHA';
$post["dataexpiracao"] = '2010/09/08';
$post["numeromaxalunos"] = '100';

$resMessage = $client->cadastrarTurma($post);

if ($resMessage)
{
	echo urldecode($resMessage->str);

}
?>
