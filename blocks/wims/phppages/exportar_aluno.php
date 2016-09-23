<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
global $CFG, $USER;
include_once('../../../config.php');
require_once("ClienteWim.php");
$id_curso = $_GET['course'];

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}
$navlinks[ 0 ] = array('name' => $course->fullname,'link' => "$CFG->wwwroot/course/view.php?id=$id_curso",'type' => 'misc');
$navlinks[ 1 ] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

$navigation	= build_navigation($navlinks);

print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );


if(empty($_GET['usuario'])){
	$context = get_context_instance(CONTEXT_COURSE, $id_curso);

	$sqlusers = "SELECT * FROM {$CFG->prefix}role_assignments r, {$CFG->prefix}user u
				WHERE r.contextid = $context->id
				AND u.id = r.userid
				AND r.roleid =5
				AND u.id NOT IN (SELECT id_user FROM `mdl_users_wims` WHERE id_curso = $id_curso)
				ORDER BY u.id ";

	$table->head  = array('Imagem','Nome'.'/'.'Sobrenome','A&ccedil;&atilde;o');

	$table->align = array("center","left","center");

	$linhausers = get_records_sql($sqlusers);
	if(isteacher($id_curso,$USER->id,true) && !empty($linhausers)){

		foreach($linhausers as $vetorlinhausers){
			$userid = $vetorlinhausers->userid ;
			$userfirstname = $vetorlinhausers->firstname ;
			$userlastname = $vetorlinhausers->lastname ;
			$userpic = $vetorlinhausers-> picture ;
			$imagem = print_user_picture($userid, $id_curso, $userpic, false, true);

			$link = '<a style="text-decoration:none; color:000;" href="'.$CFG->wwwroot.'/blocks/wim/phppages/exportar_aluno.php?course='.$id_curso.'&usuario='.$userid.'"><img src="'.$CFG->wwwroot.'/blocks/wim/icons/associar_aluno.png"/></a>';

			$table->data[] = 'hr';

			$table->data[] = array ($imagem,$userfirstname .' '.$userlastname, $link);

		}
		print_table($table);
	}else{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", 'Nenhum Usu&aacute;rio Para Exportar.');
		print_footer($course);
	}
}else{
	$client = new ClienteWim();
	$resMessage = $client->exportarAluno($id_curso,$_GET['usuario']);
	if ($resMessage)
	{
		$simplexml = new SimpleXMLElement($resMessage->str);
		if($simplexml->cadastroSucesso == 'true'){
			redirect("$CFG->wwwroot/blocks/wim/phppages/exportar_aluno.php?course=$id_curso", 'Usu&aacute;rio Exportado Com Sucesso !');
			print_footer( $course );
		}else{
			redirect("$CFG->wwwroot/blocks/wim/phppages/exportar_aluno.php?course=$id_curso", 'Usu&aacute;rio Nao Exportado Com Sucesso !');
		}
	}

}
print_footer($course);

?>
