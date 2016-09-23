<?php
include_once("ClienteWim.php");
include('../../../mod/assignment/lib.php');
include_once('../../../config.php');

function listar_usuarios_curso($id_curso) {
	global $CFG;
	$sqlusers = "SELECT userid
				FROM {$CFG->prefix}user u, {$CFG->prefix}course_display c
				WHERE c.course = $id_curso
				AND c.userid = u.id ORDER BY u.id";

	$linhausers = get_records_sql($sqlusers);
	return $linhausers;
}

function listar_usuarios_cadastrados_wims($id_curso) {
	global $CFG;
	$sqluserswims = "SELECT id_user
				FROM {$CFG->prefix}users_wims
				WHERE id_curso = $id_curso
				ORDER BY id_user ASC";

	$linhausers = get_records_sql($sqluserswims);

	return $linhausers;
}

function objectToArray ($object) {
	$arr = array();
	//for ($i = 0; $i < count($object); $i++) {
	foreach ($object as $obj){
		$arr[] = get_object_vars($obj);
	}
	return $arr;
}

function recursiveArraySearch($needle,$haystack, $index = null)
{
	$aIt     = new RecursiveArrayIterator($haystack);
	$it    = new RecursiveIteratorIterator($aIt);

	while($it->valid())
	{
		if (((isset($index) AND ($it->key() == $index)) OR (!isset($index))) AND ($it->current() == $needle)) {
			return $aIt->key();
		}
			
		$it->next();
	}

	return false;
}


$id_curso = 55;

$linhauserscurso = objectToArray(listar_usuarios_curso($id_curso));
$linhauserswims = objectToArray(listar_usuarios_cadastrados_wims($id_curso));

echo '<pre>';
//print_r($linhauserscurso);
echo '</pre>';
echo '<pre>';
//print_r($linhauserswims);
echo '</pre>';
$i = 0;
foreach($linhauserscurso as $vetorlinhausers){
	if(!isteacher($id_curso, $vetorlinhausers['userid'])){
		if(recursiveArraySearch($vetorlinhausers['userid'],$linhauserswims)){
			/*echo $vetorlinhausers['userid'];
			 echo ' -- <br/>';*/
		}else{
			/*echo 'ops';
			 echo $vetorlinhausers['userid'];
			 echo ' -- <br/>';*/
			$i++;
		}
	}
}

echo $i.' alunos n&atilde;o cadastrado(s)';

$i = 0;

$client = new ClienteWim();
$resMessage = $client->importarSheets($id_curso);

if ($resMessage)
{
	$simplexml = new SimpleXMLElement($resMessage->str);
	if($simplexml->retornaSheet != 'false'){
		foreach ($simplexml->sheet as $sheet){
			$sqlassignment_existente	= "SELECT id FROM {$CFG->prefix}assignment WHERE name=$sheet->id AND course = $id_curso";
			$linhassignment	= get_records_sql($sqlassignment_existente);
			$assignment = current($linhassignment)->id;
			if(empty($assignment)){
					$i++;
			}
		}
	}
}else{
	echo 'erro MENSAGEM';
}
echo '<br/>';
echo $i.' sheets n&atilde;o cadastrados.';

?>