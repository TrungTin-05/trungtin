// Chuyển đổi giữa các phần nội dung chính
const navLinks = document.querySelectorAll('.nav-link');
const mainSections = document.querySelectorAll('.main-section');
let isLoggedIn = false; // Biến để theo dõi trạng thái đăng nhập
let userRole = ''; // Biến để theo dõi vai trò người dùng

// Lấy các phần tử của modal mới
const modalOverlay = document.getElementById('modal-overlay');
const modalMessage = document.getElementById('modal-message');
const modalOkBtn = document.getElementById('modal-ok-btn');
const modalLoginBtn = document.getElementById('modal-login-btn');
const modalCancelBtn = document.getElementById('modal-cancel-btn');

const standardModalButtons = document.querySelector('.modal-buttons-ok');
const loginRequiredModalButtons = document.querySelector('.modal-buttons-login-cancel');

// Hàm hiển thị modal
function showModal(message, isLoginRequired = false) {
    modalMessage.textContent = message;
    modalOverlay.style.display = 'flex';
    if (isLoginRequired) {
        if (standardModalButtons) standardModalButtons.style.display = 'none';
        if (loginRequiredModalButtons) loginRequiredModalButtons.style.display = 'flex';
    } else {
        if (standardModalButtons) standardModalButtons.style.display = 'block';
        if (loginRequiredModalButtons) loginRequiredModalButtons.style.display = 'none';
    }
}

// Hàm lưu kết quả bài làm vào cơ sở dữ liệu
function saveResult(lessonName, correctAnswers, totalQuestions) {
  // Tạo đối tượng FormData để gửi dữ liệu POST
  const formData = new FormData();
  formData.append('lesson_name', lessonName);
  formData.append('correct_answers', correctAnswers);
  formData.append('total_questions', totalQuestions);

  // Gửi dữ liệu đến save_result.php
  fetch('save_result.php', {
    method: 'POST',
    body: formData,
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Kết quả đã được lưu thành công.');
      // Hiển thị thông báo cho người dùng
      showModal('Bạn đã hoàn thành bài học. Kết quả của bạn đã được lưu.');
    } else {
      console.error('Lỗi khi lưu kết quả:', data.message);
      showModal('Lỗi: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Lỗi mạng hoặc server:', error);
    showModal('Đã xảy ra lỗi khi kết nối đến máy chủ.');
  });
}

// Ẩn modal khi nhấp vào nút OK
modalOkBtn.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
});

// Xử lý sự kiện cho nút "Đăng nhập" và "Hủy"
modalLoginBtn.addEventListener('click', () => {
  modalOverlay.style.display = 'none';
  openPopup(false); // Mở popup đăng nhập
});

modalCancelBtn.addEventListener('click', () => {
  modalOverlay.style.display = 'none';
});

// Lấy các phần tử liên kết đến khu vực giáo viên
const teacherLink = document.querySelector('.nav-link.require-teacher');

// --- HÀM MỚI: TẢI BÀI KIỂM TRA ---
function loadQuiz(lessonId) {
    const quizContainer = document.getElementById('quiz-container');
    if (!quizContainer) {
        console.error("Không tìm thấy #quiz-container.");
        return;
    }

    // Xóa nội dung cũ
    quizContainer.innerHTML = '<h2>Đang tải câu hỏi...</h2>';

    fetch(`get_questions.php?lesson_id=${lessonId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Lỗi khi tải câu hỏi.');
            }
            return response.json();
        })
        .then(questions => {
            if (questions.length === 0) {
                quizContainer.innerHTML = '<h2>Không có câu hỏi nào cho bài học này.</h2>';
                return;
            }

            // Xóa nội dung tải... và tạo form mới
            quizContainer.innerHTML = `
                <h2>Bài kiểm tra</h2>
                <form id="quiz-form">
                </form>
                <button type="submit" id="submit-quiz-btn">Nộp bài</button>
            `;

            const quizForm = document.getElementById('quiz-form');
            questions.forEach((q, index) => {
                const questionElement = document.createElement('div');
                questionElement.classList.add('question-item');
                let optionsHtml = '';
                // Giả định q.options là một chuỗi JSON, cần parse nó
                try {
                    const options = [q.option_a, q.option_b, q.option_c, q.option_d]; 
                    options.forEach((option, i) => {
                        optionsHtml += `
                            <label>
                                <input type="radio" name="question-${q.id}" value="${i}">
                                ${option}
                            </label>
                        `;
                    });
                } catch (e) {
                    console.error("Không thể parse options:", e);
                    optionsHtml = '<p>Lỗi hiển thị tùy chọn.</p>';
                }

                questionElement.innerHTML = `
                    <p><strong>Câu hỏi ${index + 1}:</strong> ${q.question_text}</p>
                    <div class="options">
                        ${optionsHtml}
                    </div>
                `;
                quizForm.appendChild(questionElement);
            });
            
            // Lắng nghe sự kiện nộp bài sau khi form đã được tạo
            document.getElementById('submit-quiz-btn').addEventListener('click', (e) => {
                e.preventDefault(); // Ngăn chặn hành vi mặc định của nút submit
                submitQuiz(lessonId);
            });

        })
        .catch(error => {
            console.error('Lỗi khi tải bài kiểm tra:', error);
            quizContainer.innerHTML = '<h2>Lỗi: Không thể tải bài kiểm tra. Vui lòng thử lại sau.</h2>';
        });
}

// --- HÀM MỚI: NỘP BÀI KIỂM TRA ---
function submitQuiz(lessonId) {
    const quizForm = document.getElementById('quiz-form');
    const answers = {};

    // Thu thập câu trả lời của người dùng
    const radioButtons = quizForm.querySelectorAll('input[type="radio"]:checked');
    radioButtons.forEach(radio => {
        // Lấy questionId từ tên (name) của radio button
        const questionId = radio.name.split('-')[1];
        answers[questionId] = radio.value;
    });

    const formData = new FormData();
    formData.append('lesson_id', lessonId);
    formData.append('answers', JSON.stringify(answers));

    fetch('submit_quiz.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showModal(`Bạn đã hoàn thành bài kiểm tra! Kết quả: ${data.correct_answers}/${data.total_questions} câu trả lời đúng.`);
        } else {
            showModal('Lỗi khi nộp bài kiểm tra: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Lỗi mạng hoặc server khi nộp bài:', error);
        showModal('Đã xảy ra lỗi khi kết nối đến máy chủ.');
    });
}

// --- CẬP NHẬT HÀM navLinks ---
navLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const sectionId = link.dataset.section;

    // Các phần này không yêu cầu đăng nhập
    if (sectionId === 'home') {
        mainSections.forEach(section => {
          section.classList.remove('active');
        });
        document.getElementById(sectionId).classList.add('active');
        return;
    }
    
    // Bổ sung logic cho mục Bài học và Kiểm tra
    if (sectionId === 'lessons') {
        if (!isLoggedIn) {
            showModal('Vui lòng đăng nhập để truy cập phần này.', true);
            return;
        }
        // Bạn có thể cần một hàm để hiển thị danh sách bài học và cho phép người dùng chọn.
        // Tạm thời, ta có thể gọi loadQuiz với một lessonId mẫu để kiểm tra.
        // Ví dụ:
        // loadQuiz(1); 
    }
    
    // Tương tự cho mục "Kiểm tra"
    if (sectionId === 'quizzes') {
        if (!isLoggedIn) {
            showModal('Vui lòng đăng nhập để truy cập phần này.', true);
            return;
        }
        // Gọi hàm để tải bài kiểm tra
        // Ví dụ:
        // loadQuiz(1);
    }


    // Kiểm tra trạng thái đăng nhập cho các phần còn lại
    if (!isLoggedIn) {
        showModal('Vui lòng đăng nhập để truy cập phần này.', true);
        return;
    }

    // Kiểm tra vai trò cho khu vực giáo viên
    if (sectionId === 'teacher-area' && userRole !== 'Giáo viên') {
      showModal('Bạn không có quyền truy cập vào khu vực này.', false);
      return;
    }
    
    // Nếu đã đăng nhập và có quyền, cho phép chuyển trang
    mainSections.forEach(section => {
      section.classList.remove('active');
    });

    const targetSection = document.getElementById(sectionId);
    // Xử lý đặc biệt cho iframe của giáo viên
    if (sectionId === 'teacher-area') {
      const teacherIframe = document.getElementById('teacher-area-iframe');
      if (teacherIframe) {
        teacherIframe.classList.add('active');
      }
    } else if (targetSection) {
      targetSection.classList.add('active');
    }
  });
});

// --- JavaScript cho Popup Form ---
const loginBtn = document.getElementById('login-btn');
const registerBtn = document.getElementById('register-btn');
const popupOverlay = document.getElementById('popupOverlay');
const registerToggleBtn = document.getElementById('register-toggle');
const loginToggleBtn = document.getElementById('login-toggle');
const formTitle = document.getElementById('form-title');
const authForm = document.getElementById('auth-form');
const submitBtn = document.getElementById('submit-btn');

const userRoleSelect = document.getElementById('user-role-select');
const fullNameField = document.getElementById('full-name-field');
const confirmPasswordField = document.getElementById('confirm-password-field');

const fullNameInput = document.getElementById('full-name');
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm-password');

const errorMsg = document.getElementById('error-msg');
let isRegisterMode = true;

// Các phần tử UI mới
const authSection = document.querySelector('.auth-buttons'); 
const userInfoSection = document.getElementById('user-info-section');
const userDisplayName = document.getElementById('user-display-name');
const logoutBtn = document.getElementById('logout-btn');
const cancelPopupBtn = document.getElementById('cancel-popup-btn'); // Lấy phần tử nút "Hủy"

// Mở popup và chuyển sang chế độ tương ứng
function openPopup(isReg) {
    isRegisterMode = isReg;
    popupOverlay.style.display = 'flex';
    if (isRegisterMode) {
      formTitle.textContent = 'Đăng ký';
      registerToggleBtn.classList.add('active');
      loginToggleBtn.classList.remove('active');
      fullNameField.style.display = 'block';
      confirmPasswordField.style.display = 'block';
      submitBtn.textContent = 'Đăng ký'; // Cập nhật nhãn nút
      // Hiển thị dropdown chọn vai trò
      if (userRoleSelect) userRoleSelect.style.display = 'block';
    } else {
      formTitle.textContent = 'Đăng nhập';
      loginToggleBtn.classList.add('active');
      registerToggleBtn.classList.remove('active');
      fullNameField.style.display = 'none';
      confirmPasswordField.style.display = 'none';
      submitBtn.textContent = 'Đăng nhập'; // Cập nhật nhãn nút
      // Ẩn dropdown chọn vai trò
      if (userRoleSelect) userRoleSelect.style.display = 'none';
    }
    // Xóa dữ liệu cũ
    authForm.reset();
    errorMsg.textContent = '';
}

loginBtn.addEventListener('click', () => openPopup(false));
registerBtn.addEventListener('click', () => openPopup(true));
document.getElementById('close-popup-btn').addEventListener('click', () => {
    popupOverlay.style.display = 'none';
});

// Thêm sự kiện click cho nút "Hủy"
cancelPopupBtn.addEventListener('click', () => {
    popupOverlay.style.display = 'none';
});

// Chuyển đổi giữa Đăng ký và Đăng nhập trong popup
registerToggleBtn.addEventListener('click', () => openPopup(true));
loginToggleBtn.addEventListener('click', () => openPopup(false));

// Xử lý submit form
authForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = usernameInput.value;
    const password = passwordInput.value;
    const fullName = fullNameInput.value;
    const confirmPassword = confirmPasswordInput.value;
    let url = isRegisterMode ? 'register.php' : 'login.php';
    let formData = new FormData();

    if (isRegisterMode) {
        if (password !== confirmPassword) {
            errorMsg.textContent = 'Mật khẩu nhập lại không khớp!';
            return;
        }
        const selectedRole = userRoleSelect.value;
        formData.append('username', username);
        formData.append('password', password);
        formData.append('full_name', fullName);
        formData.append('user_role', selectedRole);
    } else {
        formData.append('username', username);
        formData.append('password', password);
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            popupOverlay.style.display = 'none';
            // Hiển thị thông báo thành công
            showModal(result.message);
            
            // Cập nhật biến trạng thái và vai trò sau khi đăng nhập/đăng ký thành công
            isLoggedIn = true; 
            // Cập nhật vai trò người dùng từ phản hồi server
            userRole = result.role; 
            userDisplayName.textContent = `Chào, ${result.full_name || fullName}!`;
            
            // Cập nhật giao diện trực tiếp
            authSection.style.display = 'none';
            userInfoSection.style.display = 'flex';
            
            // Cập nhật hiển thị link cho giáo viên
            if (userRole === 'Giáo viên') {
              teacherLink.style.display = 'block';
            }
        } else {
            errorMsg.textContent = result.message || 'Có lỗi xảy ra.';
        }
    } catch (error) {
        errorMsg.textContent = 'Lỗi kết nối server.';
    }
});


// Kiểm tra trạng thái đăng nhập khi tải trang
function checkLoginStatus() {
  fetch('check_login.php')
    .then(response => response.json())
    .then(data => {
      isLoggedIn = data.loggedin; // Cập nhật biến trạng thái
      userRole = data.role; // Cập nhật vai trò người dùng từ server
      if (data.loggedin) {
        // Đã đăng nhập, hiển thị thông tin người dùng
        authSection.style.display = 'none';
        userInfoSection.style.display = 'flex';
        userDisplayName.textContent = `Chào, ${data.full_name}!`;
        const hosoIframe = document.getElementById('hoso');
        if (hosoIframe) {
            hosoIframe.contentWindow.postMessage({ type: 'updateName', name: data.full_name }, '*');
        }
        // Hiển thị link giáo viên nếu vai trò là giáo viên
        if (userRole === 'Giáo viên') {
          teacherLink.style.display = 'block';
        } else {
          teacherLink.style.display = 'none';
        }
      } else {
        // Chưa đăng nhập, hiển thị nút đăng nhập/đăng ký
        authSection.style.display = 'flex';
        userInfoSection.style.display = 'none';
        // Ẩn link giáo viên nếu chưa đăng nhập
        teacherLink.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Lỗi khi kiểm tra trạng thái đăng nhập:', error);
    });
}

// Xử lý sự kiện nút đăng xuất
logoutBtn.addEventListener('click', () => {
  fetch('logout.php')
    .then(response => response.json())
    .then(result => {
        // Hiển thị thông báo đăng xuất
        showModal(result.message);
        // Cập nhật giao diện trực tiếp sau khi đăng xuất
        authSection.style.display = 'flex';
        userInfoSection.style.display = 'none';
        isLoggedIn = false; // Đặt lại trạng thái đăng nhập
        userRole = ''; // Đặt lại vai trò người dùng
        teacherLink.style.display = 'none'; // Ẩn link giáo viên

        // Thêm đoạn code này để tải lại trang về trang chủ
        window.location.href = 'index.html'; // Chuyển hướng về trang chủ
    });
});

// Lắng nghe tin nhắn từ iframe
window.addEventListener('message', (event) => {
    if (event.data.type === 'requestName') {
        checkLoginStatus(); // Gửi lại tên người dùng khi iframe yêu cầu
    }
});

// Hiển thị phần "Trang chủ" khi tải trang
const homeSection = document.getElementById('home');
if (homeSection) {
    homeSection.classList.add('active');
}

// Kiểm tra trạng thái đăng nhập lần đầu
checkLoginStatus();
