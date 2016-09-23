<?php
global $CFG;
//10.1.0.144
include_once('../scripts/functions.php');
include_once('../../../config.php');
include('../../../mod/assignment/lib.php');
require_once("ClienteWim.php");
$id_curso	= $_GET['course'];

$info_turma = pegar_info_classe_wims($id_curso);

$referencia = current($info_turma)->ref_classe_wims;

if( !( $COURSE = get_record('course', 'id', $id_curso ) ) ) {
	error( 'Invalid course id' );

}else if( ! $USER->id ) {
	error('Invalid user');

}else if (empty($referencia)) {
	error(' Invalid Access! ');
}
else {
	//get_student_courses

	$navlinks[ 0 ] = array('name' => get_string('blockname','block_wim'),'link' => "#",'type' => 'misc');

	$navigation	= build_navigation($navlinks);

	print_header_simple($course->fullname.': '.get_string('blockname','block_wim'), ': '.get_string('blockname','block_wim'),$navigation,'','',true,'','' );
	echo '<br/><br/>';
	$client = new ClienteWim();
	$resMessage = $client->importarNotas($id_curso);

	if ($resMessage){
		$simplexml = new SimpleXMLElement($resMessage->str);


		/*echo '<pre>';
		 print_r($simplexml);
		 echo '</pre>';
		 exit;*/

		$sqlsheets	= "SELECT id_sheet FROM {$CFG->prefix}assignment_sheetwims  WHERE id_curso = $id_curso ORDER BY id_sheet ASC";
		$linhasqlsheets	= get_records_sql($sqlsheets);

		foreach ($simplexml->alunos as $aluno){
			$sqlaluno	= "SELECT id_user FROM {$CFG->prefix}users_wims  WHERE login_wims = $aluno->login";
			$linhaaluno	= get_records_sql($sqlaluno);
			$id_aluno = current($linhaaluno)->id_user;

			foreach ($linhasqlsheets as $sheets_id){
				$sheet_id = $sheets_id->id_sheet;

				$sqlsheet	= "SELECT id_assignment FROM {$CFG->prefix}assignment_sheetwims  WHERE id_curso = $id_curso AND id_sheet=$sheet_id";
				$linhasheet	= get_records_sql($sqlsheet);
				$id_tarefa = current($linhasheet)->id_assignment;

				$sqlcounttarefa	= "SELECT id FROM {$CFG->prefix}assignment_submissions  WHERE userid = $id_aluno AND assignment= $id_tarefa";
				$linhatotal	= get_records_sql($sqlcounttarefa);
				$id_submission = current($linhatotal)->id;

				if(empty($id_submission)){

					foreach ($aluno->notas as $notas){
						$sheet_id = 'sheet'.$sheet_id;

						$nota = $notas->$sheet_id.'';

						$a = new Object();
						$a->assignment = $id_tarefa;
						$a->userid = $id_aluno;
						$a->timecreated = 0;
						$a->timemodified = 0;
						$a->numfiles = 0;
						$a->data1 = '';
						$a->data2 = '';
						$a->grade = $nota*10;
						$a->format = 1;
						$a->teacher = $USER->id;
						$a->timemarked = time();
						$a->mailed = 0;

						insert_record("assignment_submissions",$a);
					}
				}
				else{
					foreach ($aluno->notas as $notas){
						$sheet_id = 'sheet'.$sheet_id;

						$nota = $notas->$sheet_id.'';

						$a = new Object();
						$a->id = $id_submission;
						$a->grade = $nota*10;
						update_record("assignment_submissions",$a);
					}
				}
				$sqla = "SELECT * FROM {$CFG->prefix}assignment WHERE id=$id_tarefa";
				$linhaassig = get_records_sql($sqla);
				$a = (Object) current($linhaassig);

				assignment_update_grades($a);
			}
		}
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgsucesso', 'block_wim'));
	}
	else{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
	}


	print_footer( $course );

}
?>