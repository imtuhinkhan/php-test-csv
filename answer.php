<?php
function getQuestionForm($id,$sid){
    GLOBAL $conn;
    $question = "SELECT * FROM questions WHERE id=$id" ;
    $data = $conn->query($question);
    while ($row = mysqli_fetch_array($data)) {

        $getAnswer = "SELECT * FROM answers WHERE student_id='$sid' AND question_id=$id ";
        $answerData = $conn->query($getAnswer);
        $answer = json_encode([]);
        $sub = [];
        $remarks = [];
        while ($arow = mysqli_fetch_array($answerData)) {
            $answer = $arow['answer'];
            $sub = $arow['sub_val'];
            $remarks = $arow['remarks'];
        }
        
        if($row['question_type']=='textbox'){
            return pmakeTextQuestion($row,$answer);
        }elseif($row['question_type']=='checkbox'){
            $ans = json_decode($answer,true);
            if(isset($ans[$id])){
            return pmakeCheckboxQuestion($row,$ans[$id],$remarks);
            }
            else{
                return '-';
            }
        }elseif($row['question_type']=='radio'){
            $ans = json_decode($answer,true);
            if(isset($ans[$id])){
                return pmakeRadioQuestion($row,$ans[$id],$remarks,$sub);
            }
            else{
                return '-';
            }
        }elseif($row['question_type']=='tab-text'){
            $ans = json_decode($answer,true);
            if(isset($ans[$id])){
                return pmakeTabTextQuestion($row,$ans[$id]);
            }else{
                return '-';
            }
        }
        else{
            $ans = json_decode($answer,true);
            // var_dump($ans);
            if(isset($ans[$id])){
                return pmakeTableQuestion($row,$ans[$id]);
            }
            else{
                return '-';
            }
        }
    }
}


//question type text
function pmakeTextQuestion($row,$answer){
    if($answer!=='[]'){
        return $answer;

    }else{
        return '-';
    }

}

//question type radio

function pmakeRadioQuestion($row,$ans,$remarks,$sub){
    GLOBAL $conn;
    $qid = $row["id"];
    $options = "SELECT * FROM question_type_checkbox WHERE question_id=$qid";
    $data = $conn->query($options);
    $html = '';
  
    while ($option = mysqli_fetch_array($data)) {
        if($ans==$option['id']){
            $html .= $option['answer_option'].',' ;

        }
        if($option['has_input']==1 && $remarks!=NULL){
            $html .= $option['input_label'].'-';
            $html .=$remarks;
        }

        if($option['has_sub_option']==1){ //added a option allow input 
            
            $id = $option['id'];
            $otherOptions = "SELECT * FROM others_options WHERE option_id=$id";
            $oop = $conn->query($otherOptions);
            if($ans==$option['id']){
                $html .= $option['sub_option_label'].'-';
                while ($op = mysqli_fetch_array($oop)) {
                    if($sub==$op['id']){
                    $html.=$op['answer_option'].',';
                    }
                }
            }
        }

    }

    return $html;
}

//question type checkbox
function pmakeCheckboxQuestion($row,$answer,$remarks){
    GLOBAL $conn;
    $qid = $row["id"];
    $options = "SELECT * FROM question_type_checkbox WHERE question_id=$qid";
    $data = $conn->query($options);

    $html = '';
    while ($option = mysqli_fetch_array($data)) {
        if(in_array($option['id'],$answer)){
            $html .= $option['answer_option'] . '-' ;
        }

        if($option['has_input']==1 && $remarks!=null){ //added a option allow input 
            $html.=$remarks;
        }
    }
    return $html;
}

//question type table
function pmakeTableQuestion($row,$answer){
    GLOBAL $conn;
    $qid = $row["id"];


    $t_options = "SELECT * FROM question_type_table WHERE question_id=$qid";
    $t_data = $conn->query($t_options);
    $t_data2 = $conn->query($t_options);
    while ($data = mysqli_fetch_array($t_data)) {
        $table_data[] = $data;
    }

    $q_options = "SELECT * FROM question_type_checkbox WHERE question_id=$qid";
    $q_data = $conn->query($q_options);

    $html = '';

    while ($table_details = mysqli_fetch_array($q_data)) {
    $html .= $table_details['answer_option'].'-';

    foreach($table_data as $input){
        if(array_key_exists($table_details['id'],$answer) && $answer[$table_details['id']]==($table_details['id'].'-'.$input["id"]) ){
            $html .= $input['answer_option'] ;
        }
    }
    }

    return $html;
}

//question type table with input
function pmakeTabTextQuestion($row,$answer){
    GLOBAL $conn;
    $qid = $row["id"];
    $t_options = "SELECT * FROM question_type_table WHERE question_id=$qid";
    $t_data = $conn->query($t_options);
    $t_data2 = $conn->query($t_options);
    while ($data = mysqli_fetch_array($t_data)) {
        $table_data[] = $data;
    }

    $q_options = "SELECT * FROM question_type_checkbox WHERE question_id=$qid";
    $q_data = $conn->query($q_options);

    $html = '';
   
    while ($table_details = mysqli_fetch_array($q_data)) {
    $html .= $table_details['answer_option'].'';
    foreach($table_data as $input){
        for ($i=1; $i <=10 ; $i++) { 
            if(array_key_exists($table_details["id"],$answer) && $answer[$table_details["id"]]==$i){
                $html .= '-'.$i.',';

            }
        }

    }
    }
    return $html;
}

