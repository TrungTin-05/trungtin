<?php
// Thêm bài học mới
function themBaiHoc($tieu_de, $noi_dung, $lop) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO lessons (title, content, grade) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $tieu_de, $noi_dung, $lop);
    $stmt->execute();
    return $stmt->insert_id;
}

// Thêm bài tập mới
function themBaiTap($id_lesson, $ten_bai_tap) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO bai_tap (id_lesson, ten_bai_tap) VALUES (?, ?)");
    $stmt->bind_param("is", $id_lesson, $ten_bai_tap);
    $stmt->execute();
    return $stmt->insert_id;
}

// Thêm câu hỏi và đáp án
function themCauHoiVaDapAn($id_bai_tap, $noi_dung_cau_hoi, $dap_an_dung, $dap_an_sai) {
    global $conn;
    $conn->begin_transaction();

    try {
        // Thêm câu hỏi
        $stmt_cau_hoi = $conn->prepare("INSERT INTO cau_hoi (id_bai_tap, noi_dung) VALUES (?, ?)");
        $stmt_cau_hoi->bind_param("is", $id_bai_tap, $noi_dung_cau_hoi);
        $stmt_cau_hoi->execute();
        $id_cau_hoi = $stmt_cau_hoi->insert_id;

        // Thêm đáp án đúng
        $stmt_dap_an_dung = $conn->prepare("INSERT INTO dap_an (id_cau_hoi, noi_dung, la_dap_an_dung) VALUES (?, ?, ?)");
        $la_dung = 1;
        $stmt_dap_an_dung->bind_param("isi", $id_cau_hoi, $dap_an_dung, $la_dung);
        $stmt_dap_an_dung->execute();

        // Thêm đáp án sai
        foreach ($dap_an_sai as $dap_an) {
            $stmt_dap_an_sai = $conn->prepare("INSERT INTO dap_an (id_cau_hoi, noi_dung, la_dap_an_dung) VALUES (?, ?, ?)");
            $la_sai = 0;
            $stmt_dap_an_sai->bind_param("isi", $id_cau_hoi, $dap_an, $la_sai);
            $stmt_dap_an_sai->execute();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}
?>