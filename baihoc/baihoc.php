<?php
// Bao gồm tệp kết nối cơ sở dữ liệu và các hàm đọc dữ liệu
require_once '../database/config.php';
require_once '../database/read_data.php';

$lesson = null;
$baiTaps = [];
$questionsByExercise = [];

// Kiểm tra xem có ID bài học trên URL không
if (isset($_GET['id'])) {
    $id_bai_hoc = $_GET['id'];
    
    // Lấy nội dung bài học
    $lesson = layChiTietBaiHoc($id_bai_hoc);
    
    if ($lesson) {
        // Lấy các bài tập liên quan
        $baiTaps = layBaiTapTheoBaiHoc($id_bai_hoc);
        
        // Lấy câu hỏi và đáp án cho từng bài tập
        foreach ($baiTaps as $bai_tap) {
            $id_bai_tap = $bai_tap['id_bai_tap'];
            $questionsByExercise[$id_bai_tap] = layCauHoiVaDapAnTheoBaiTap($id_bai_tap);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Bài học</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
    </style>
</head>
<body class="p-6 md:p-10">

<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6 md:p-8">
    
    <button onclick="window.history.back()" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200 mb-6 font-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H16a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Quay lại
    </button>
    
    <?php if ($lesson): ?>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 text-center"><?php echo htmlspecialchars($lesson['title']); ?></h1>
        <p class="text-gray-600 mb-6 text-center">Lớp: <?php echo htmlspecialchars($lesson['grade']); ?></p>

        <div class="prose max-w-none text-gray-800 leading-relaxed mb-8">
            <?php echo $lesson['content']; ?>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mt-10 mb-6 border-b-2 border-gray-200 pb-2">Bài tập</h2>

        <?php if ($baiTaps): ?>
            <?php foreach ($baiTaps as $bai_tap): ?>
                <div class="bg-blue-50 rounded-lg p-6 mb-8 shadow-sm">
                    <h3 class="text-xl font-bold text-blue-800 mb-4"><?php echo htmlspecialchars($bai_tap['ten_bai_tap']); ?></h3>
                    
                    <?php if (isset($questionsByExercise[$bai_tap['id_bai_tap']]) && !empty($questionsByExercise[$bai_tap['id_bai_tap']])): ?>
                        <?php foreach ($questionsByExercise[$bai_tap['id_bai_tap']] as $key => $question): ?>
                            <div class="bg-white rounded-lg p-4 mb-4 shadow-md">
                                <p class="font-semibold text-gray-700 mb-3">
                                    <span class="text-blue-500 font-bold mr-2">Câu <?php echo $key + 1; ?>:</span>
                                    <?php echo htmlspecialchars($question['noi_dung']); ?>
                                </p>
                                <div class="space-y-2">
                                    <?php foreach ($question['answers'] as $answer): ?>
                                        <label class="flex items-center text-gray-600 cursor-pointer hover:bg-gray-100 rounded-md p-2 transition-colors duration-200">
                                            <input type="radio" 
                                                   name="q_<?php echo $question['id_cau_hoi']; ?>" 
                                                   value="<?php echo htmlspecialchars($answer['noi_dung']); ?>" 
                                                   class="form-radio text-blue-600 h-4 w-4">
                                            <span class="ml-2"><?php echo htmlspecialchars($answer['noi_dung']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <button class="check-answer-btn mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-200" 
                                        data-correct-answer="<?php 
                                            foreach ($question['answers'] as $answer) {
                                                if ($answer['la_dap_an_dung']) {
                                                    echo htmlspecialchars($answer['noi_dung']);
                                                    break;
                                                }
                                            }
                                        ?>">
                                    Kiểm tra
                                </button>
                                <div class="feedback mt-3 font-semibold hidden"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500">Bài tập này chưa có câu hỏi nào.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-500">Bài học này chưa có bài tập nào.</p>
        <?php endif; ?>

    <?php else: ?>
        <p class="text-center text-red-500 text-xl font-semibold">Bài học không tồn tại.</p>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkButtons = document.querySelectorAll('.check-answer-btn');

        checkButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                const button = event.target;
                const questionContainer = button.closest('.bg-white');
                const feedbackDiv = questionContainer.querySelector('.feedback');
                
                // Lấy tên của nhóm radio button
                const radioGroupName = questionContainer.querySelector('input[type="radio"]').name;
                
                // Lấy đáp án đã chọn
                const selectedAnswerInput = document.querySelector(`input[name="${radioGroupName}"]:checked`);
                
                if (!selectedAnswerInput) {
                    feedbackDiv.textContent = 'Vui lòng chọn một đáp án.';
                    feedbackDiv.classList.remove('text-green-600', 'text-red-600');
                    feedbackDiv.classList.add('text-yellow-600', 'block');
                    return;
                }
                
                const selectedAnswer = selectedAnswerInput.value;
                const correctAnswer = button.dataset.correctAnswer;

                // Ẩn/hiện và cập nhật feedback
                feedbackDiv.classList.remove('text-yellow-600', 'hidden');
                feedbackDiv.classList.add('block');
                
                if (selectedAnswer === correctAnswer) {
                    feedbackDiv.textContent = 'Chính xác! 🎉';
                    feedbackDiv.classList.remove('text-red-600');
                    feedbackDiv.classList.add('text-green-600');
                } else {
                    feedbackDiv.innerHTML = `Câu trả lời sai. Đáp án đúng là: <span class="font-bold">${correctAnswer}</span>`;
                    feedbackDiv.classList.remove('text-green-600');
                    feedbackDiv.classList.add('text-red-600');
                }

                // Vô hiệu hóa các radio button và nút sau khi kiểm tra
                const radioInputs = questionContainer.querySelectorAll('input[type="radio"]');
                radioInputs.forEach(radio => radio.disabled = true);
                button.disabled = true;
                button.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                button.classList.add('bg-gray-400', 'cursor-not-allowed');
            });
        });
    });
</script>

</body>
</html>