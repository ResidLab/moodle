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
	$resMessage = $client->importarSheets($id_curso);

	if ($resMessage)
	{
		$simplexml = new SimpleXMLElement($resMessage->str);
		if($simplexml->retornaSheet != 'false'){
			foreach ($simplexml->sheet as $sheet){
				$assignment_id = $_GET['assignment'];

				$sqlassignment_existente	= "SELECT id FROM {$CFG->prefix}assignment_sheetwims  WHERE id_sheet = $sheet->id AND id_curso = $id_curso";
				$linhassignment	= get_records_sql($sqlassignment_existente);
				$assignment = current($linhassignment)->id;
				if(empty($assignment)){
					$datatarefacomeco = $sheet->dataexp;
					$hrcomecotarefa = '23:00';

					$data_nova_comeco = explode("/", $datatarefacomeco);
					$hora_nova_comeco = explode(":", $hrcomecotarefa);
					if($data_nova_comeco[0] < 10) $data_nova_comeco[0] = '0'.$data_nova_comeco[0];
					$data_nova_comeco[1] = array_meses($data_nova_comeco[1]);
					$data_expiracao = mktime($hora_nova_comeco[0], $hora_nova_comeco[1], 0, $data_nova_comeco[1], $data_nova_comeco[0], $data_nova_comeco[2]);


					$today = getdate(time());

					$sec = $today["seconds"];
					$min = $today["minutes"];
					$hour = $today["hours"];
					$mday = $today["mday"];
					$mon = $today["mon"];
					$year = $today["year"];

					$data_timestamp_comeco = mktime($hour, $min, $sec, $mon, $mday, $year);

					$assignmentwims = new Object();
					$assignmentwims->course = $id_curso;
					$assignmentwims->name = $sheet->title;
					$assignmentwims->description = $sheet->title;
					$assignmentwims->assignmenttype = "wims";
					$assignmentwims->resubmit = 1;
					$assignmentwims->var1 = 0;
					$assignmentwims->maxbytes = 1048576;
					$assignmentwims->timeavailable = $data_timestamp_comeco;
					$assignmentwims->timedue = $data_expiracao;
					$assignmentwims->grade = 100;
					$assignmentwims->timemodified = time();
					$assignmentwims->id = insert_record("assignment", $assignmentwims);

					//pega o id da tarefa na tabela modules
					$sqlassignment = "SELECT id FROM {$CFG->prefix}modules WHERE name='assignment'";
					$linhaassignment = get_records_sql($sqlassignment);
					$id_assignmentwims = current($linhaassignment)->id;

					//adiciona modulo na tabela course_module
					add_mod_course($id_curso,$id_assignmentwims,$assignmentwims->id);


					$sqlassign = "SELECT * FROM {$CFG->prefix}assignment WHERE id=27";
					$linhasqlassign = get_records_sql($sqlassign);
					assignment_grade_item_update(current($linhasqlassign));

					$novo = new Object();
					$novo->course = $id_curso;
					$novo->module = $id_module;

					$novo->instance  = $id_module_instance;
					//echo '$novo->instance:'. $novo->instance;
					//echo 'section: '.$section.'<br/>';
					$sqlsection = "SELECT id FROM {$CFG->prefix}course_sections WHERE section=$section-1 AND course=$id_curso";
					//echo $sqlsequencia.'</br>';
					$linhasection = get_records_sql($sqlsection);
					$idsection = current($linhasection)->id;

					$novo->section = $idsection;
					$novo->visible  = 1;
					$novo->visibleold = 1;

					$novo->added = time();
					$novo->id = insert_record("course_modules", $novo);

					$associar_sheet_tarefa = new Object();
					$associar_sheet_tarefa->id_curso = $id_curso;
					$associar_sheet_tarefa->id_assignment = $assignmentwims->id;
					$associar_sheet_tarefa->id_sheet  = $sheet->id;

					insert_record("assignment_sheetwims", $associar_sheet_tarefa);
				}
			}
			redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgsucesso', 'block_wim'));
		}
		else{
			redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
		}
	}
	else{
		redirect("$CFG->wwwroot/course/view.php?id=$id_curso", get_string('msgerro_not_ws', 'block_wim'));
	}

	print_footer( $course );

}
?>
