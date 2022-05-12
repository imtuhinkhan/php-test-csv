<?php
require('database.php');
require('answer.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// header('Content-Disposition: attachment; filename="filename.csv";');

function array_to_csv_download($array, $filename = "export.csv") {
    GLOBAL $conn;
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://output', 'w'); 
    // loop over the input array
    foreach ($array as $line) { 
        // generate csv lines from the inner arrays
        fputcsv($f, $line); 
    }

    // reset the file pointer to the start of the file
    // tell the browser it's going to be a csv file
    header('Content-Type: text/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}


$question_text = "SELECT * FROM questions" ;
$data = $conn->query($question_text);
$allQuestion = ['Student ID'];
while ($row = mysqli_fetch_array($data)) {
    $allQuestion[] = strip_tags($row['title']);
    $allQuestionIdDetails[] = $row;
}

//get student 
$array = [$allQuestion];


$students = "SELECT * FROM student_details" ;
$std = $conn->query($students);

while ($student = mysqli_fetch_array($std)) {
    // echo '-------------'.$student['student_id'].'------------<br>';

    $answer = [];
    array_push($answer,strip_tags($student['student_id']));
    foreach($allQuestionIdDetails as $ques) {
        $ans = getQuestionForm($ques['id'], $student['student_id']);
        // echo $ans .'<br><Br>';
        array_push($answer, substr($ans, 0, 2));
    }

    // '<pre>'.print_r($answer).'</pre>';

    // echo '-------------------------<br>';
    array_push($array,$answer);
}


// array_unshift($array, $allQuestion);

array_to_csv_download($array,
    "numbers.csv"
  );