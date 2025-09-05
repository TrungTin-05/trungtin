<?php
// Tệp này chứa các hàm để đọc (Read) dữ liệu từ cơ sở dữ liệu

// Lấy tất cả bài học, nhóm theo lớp
function layBaiHocTheoLop() {
    global $conn;
    $sql = "SELECT * FROM lessons ORDER BY grade ASC, id_lessons ASC";
    $result = $conn->query($sql);
    
    $lessonsByGrade = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $grade = $row['grade'];
            // Nếu lớp chưa tồn tại trong mảng, tạo một mảng rỗng cho lớp đó
            if (!isset($lessonsByGrade[$grade])) {
                $lessonsByGrade[$grade] = [];
            }
            // Thêm bài học vào mảng của lớp tương ứng
            $lessonsByGrade[$grade][] = $row;
        }
    }
    return $lessonsByGrade;
}

// Lấy nội dung chi tiết của một bài học
function layChiTietBaiHoc($id_bai_hoc) {
    global $conn;
    $id_bai_hoc = $conn->real_escape_string($id_bai_hoc);
    $sql = "SELECT * FROM lessons WHERE id_lessons = '$id_bai_hoc' LIMIT 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// Lấy các bài tập liên quan đến một bài học
function layBaiTapTheoBaiHoc($id_bai_hoc) {
    global $conn;
    $id_bai_hoc = $conn->real_escape_string($id_bai_hoc);
    $sql = "SELECT * FROM bai_tap WHERE id_lessons = '$id_bai_hoc' ORDER BY id_bai_tap ASC";
    $result = $conn->query($sql);
    
    $baiTaps = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $baiTaps[] = $row;
        }
    }
    return $baiTaps;
}

// Lấy tất cả câu hỏi và đáp án của một bài tập
function layCauHoiVaDapAnTheoBaiTap($id_bai_tap) {
    global $conn;
    $id_bai_tap = $conn->real_escape_string($id_bai_tap);
    
    // Truy vấn các câu hỏi
    $sql_questions = "SELECT * FROM cau_hoi WHERE id_bai_tap = '$id_bai_tap' ORDER BY id_cau_hoi ASC";
    $result_questions = $conn->query($sql_questions);
    
    $questions = [];
    if ($result_questions->num_rows > 0) {
        while($q_row = $result_questions->fetch_assoc()) {
            $id_cau_hoi = $q_row['id_cau_hoi'];
            
            // Truy vấn các đáp án cho từng câu hỏi
            $sql_answers = "SELECT * FROM dap_an WHERE id_cau_hoi = '$id_cau_hoi' ORDER BY id_dap_an ASC";
            $result_answers = $conn->query($sql_answers);
            
            $answers = [];
            if ($result_answers->num_rows > 0) {
                while($a_row = $result_answers->fetch_assoc()) {
                    $answers[] = $a_row;
                }
            }
            
            // Thêm đáp án vào câu hỏi
            $q_row['answers'] = $answers;
            $questions[] = $q_row;
        }
    }
    return $questions;
}

?>
