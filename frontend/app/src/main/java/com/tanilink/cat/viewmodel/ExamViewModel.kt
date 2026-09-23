package com.tanilink.cat.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.tanilink.cat.data.SampleData
import com.tanilink.cat.model.*
import com.tanilink.cat.proctoring.FaceStatus
import com.tanilink.cat.proctoring.ProctoringViolation
import com.tanilink.cat.proctoring.ViolationType
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

class ExamViewModel : ViewModel() {

    private val _studentName = MutableStateFlow("Zam Zam")
    val studentName: StateFlow<String> = _studentName.asStateFlow()

    private val _selectedGrade = MutableStateFlow(5)
    val selectedGrade: StateFlow<Int> = _selectedGrade.asStateFlow()

    private val _selectedAvatar = MutableStateFlow<UserAvatar>(SampleData.avatars[0])
    val selectedAvatar: StateFlow<UserAvatar> = _selectedAvatar.asStateFlow()

    private val _jwtToken = MutableStateFlow("")
    val jwtToken: StateFlow<String> = _jwtToken.asStateFlow()

    private val _isLoggedIn = MutableStateFlow(false)
    val isLoggedIn: StateFlow<Boolean> = _isLoggedIn.asStateFlow()

    private val _themeOption = MutableStateFlow(AppThemeOption.SYSTEM)
    val themeOption: StateFlow<AppThemeOption> = _themeOption.asStateFlow()

    private val _activeTab = MutableStateFlow(MainTab.EXAM_HOME)
    val activeTab: StateFlow<MainTab> = _activeTab.asStateFlow()

    private val _currentScreen = MutableStateFlow(ScreenState.HOME)
    val currentScreen: StateFlow<ScreenState> = _currentScreen.asStateFlow()

    private val _currentSubject = MutableStateFlow<ExamSubject?>(null)
    val currentSubject: StateFlow<ExamSubject?> = _currentSubject.asStateFlow()

    private val _questions = MutableStateFlow<List<Question>>(emptyList())
    val questions: StateFlow<List<Question>> = _questions.asStateFlow()

    private val _currentQuestionIndex = MutableStateFlow(0)
    val currentQuestionIndex: StateFlow<Int> = _currentQuestionIndex.asStateFlow()

    private val _userAnswers = MutableStateFlow<Map<Int, Int>>(emptyMap())
    val userAnswers: StateFlow<Map<Int, Int>> = _userAnswers.asStateFlow()

    private val _flaggedQuestions = MutableStateFlow<Set<Int>>(emptySet())
    val flaggedQuestions: StateFlow<Set<Int>> = _flaggedQuestions.asStateFlow()

    private val _remainingSeconds = MutableStateFlow(0L)
    val remainingSeconds: StateFlow<Long> = _remainingSeconds.asStateFlow()

    private val _elapsedTimeSeconds = MutableStateFlow(0L)
    val elapsedTimeSeconds: StateFlow<Long> = _elapsedTimeSeconds.asStateFlow()

    private val _lastExamResult = MutableStateFlow<ExamResult?>(null)
    val lastExamResult: StateFlow<ExamResult?> = _lastExamResult.asStateFlow()

    private val _examHistory = MutableStateFlow<List<ExamResult>>(emptyList())
    val examHistory: StateFlow<List<ExamResult>> = _examHistory.asStateFlow()

    // AI Proctoring States
    private val _faceStatus = MutableStateFlow(FaceStatus.OK)
    val faceStatus: StateFlow<FaceStatus> = _faceStatus.asStateFlow()

    private val _proctoringWarningText = MutableStateFlow("Fokus Terjaga")
    val proctoringWarningText: StateFlow<String> = _proctoringWarningText.asStateFlow()

    private val _proctoringLogs = MutableStateFlow<List<ProctoringViolation>>(emptyList())
    val proctoringLogs: StateFlow<List<ProctoringViolation>> = _proctoringLogs.asStateFlow()

    private val _violationCount = MutableStateFlow(0)
    val violationCount: StateFlow<Int> = _violationCount.asStateFlow()

    private var timerJob: Job? = null
    private var lastAnswerTimeMs = 0L

    fun login(name: String, avatar: UserAvatar, token: String) {
        _studentName.value = name
        _selectedAvatar.value = avatar
        _jwtToken.value = token
        _isLoggedIn.value = true
        _currentScreen.value = ScreenState.HOME
    }

    fun logout() {
        _isLoggedIn.value = false
        _jwtToken.value = ""
    }

    fun updateStudentProfile(name: String, grade: Int, avatar: UserAvatar) {
        _studentName.value = name
        _selectedGrade.value = grade
        _selectedAvatar.value = avatar
    }

    fun setThemeOption(option: AppThemeOption) {
        _themeOption.value = option
    }

    fun selectGrade(grade: Int) {
        _selectedGrade.value = grade
    }

    fun selectTab(tab: MainTab) {
        _activeTab.value = tab
    }

    fun startExam(subject: ExamSubject) {
        val loadedQuestions = SampleData.getQuestionsForSubjectAndGrade(subject.id, _selectedGrade.value)
        _currentSubject.value = subject
        _questions.value = loadedQuestions
        _currentQuestionIndex.value = 0
        _userAnswers.value = emptyMap()
        _flaggedQuestions.value = emptySet()
        _proctoringLogs.value = emptyList()
        _violationCount.value = 0
        _faceStatus.value = FaceStatus.OK
        _proctoringWarningText.value = "Fokus Terjaga"

        val totalSeconds = subject.durationMinutes * 60L
        _remainingSeconds.value = totalSeconds
        _elapsedTimeSeconds.value = 0L
        lastAnswerTimeMs = System.currentTimeMillis()

        _currentScreen.value = ScreenState.EXAM
        startTimer()
    }

    private fun startTimer() {
        timerJob?.cancel()
        timerJob = viewModelScope.launch {
            while (_remainingSeconds.value > 0 && _currentScreen.value == ScreenState.EXAM) {
                delay(1000L)
                _remainingSeconds.update { it - 1 }
                _elapsedTimeSeconds.update { it + 1 }

                // Periodic Snapshot simulation every 3 minutes
                if (_elapsedTimeSeconds.value > 0 && _elapsedTimeSeconds.value % 180L == 0L) {
                    addProctoringViolation(ViolationType.SNAPSHOT_CAPTURED, "Snapshot acak kamera depan berhasil diambil.")
                }
            }
            if (_remainingSeconds.value <= 0 && _currentScreen.value == ScreenState.EXAM) {
                submitExam()
            }
        }
    }

    fun updateFaceStatus(status: FaceStatus, warningText: String) {
        _faceStatus.value = status
        _proctoringWarningText.value = warningText
        if (status != FaceStatus.OK && _currentScreen.value == ScreenState.EXAM) {
            val type = when (status) {
                FaceStatus.NO_FACE -> ViolationType.NO_FACE
                FaceStatus.MULTIPLE_FACES -> ViolationType.MULTIPLE_FACES
                else -> ViolationType.LOOKING_AWAY
            }
            addProctoringViolation(type, warningText)
        }
    }

    fun notifyAppSwitchTab() {
        if (_currentScreen.value == ScreenState.EXAM) {
            _violationCount.update { it + 1 }
            addProctoringViolation(ViolationType.SWITCH_TAB, "Pindah aplikasi / keluar layar terdeteksi!")

            // Auto-submit if violations >= 3
            if (_violationCount.value >= 3) {
                submitExam()
            }
        }
    }

    fun selectAnswer(questionIndex: Int, optionIndex: Int) {
        val currentTime = System.currentTimeMillis()
        val durationMs = currentTime - lastAnswerTimeMs
        lastAnswerTimeMs = currentTime

        // Rapid answering detection (< 2000 ms)
        if (durationMs < 2000L && durationMs > 0L) {
            addProctoringViolation(ViolationType.RAPID_ANSWERING, "Deteksi kecepatan jawab terlalu kilat pada Soal ${questionIndex + 1} (${durationMs}ms).")
        }

        _userAnswers.update { current ->
            current.toMutableMap().apply { put(questionIndex, optionIndex) }
        }
    }

    private fun addProctoringViolation(type: ViolationType, desc: String) {
        val violation = ProctoringViolation(type = type, description = desc)
        _proctoringLogs.update { listOf(violation) + it }
    }

    fun toggleFlag(questionIndex: Int) {
        _flaggedQuestions.update { current ->
            val mutable = current.toMutableSet()
            if (mutable.contains(questionIndex)) {
                mutable.remove(questionIndex)
            } else {
                mutable.add(questionIndex)
            }
            mutable
        }
    }

    fun goToQuestion(index: Int) {
        if (index in 0 until _questions.value.size) {
            _currentQuestionIndex.value = index
        }
    }

    fun nextQuestion() {
        if (_currentQuestionIndex.value < _questions.value.size - 1) {
            _currentQuestionIndex.value += 1
        }
    }

    fun previousQuestion() {
        if (_currentQuestionIndex.value > 0) {
            _currentQuestionIndex.value -= 1
        }
    }

    fun submitExam() {
        timerJob?.cancel()
        val subject = _currentSubject.value ?: return
        val currentQuestions = _questions.value
        val answers = _userAnswers.value

        var correctCount = 0
        var wrongCount = 0
        var unansweredCount = 0

        currentQuestions.forEachIndexed { index, question ->
            val selectedOption = answers[index]
            if (selectedOption == null) {
                unansweredCount++
            } else if (selectedOption == question.correctAnswerIndex) {
                correctCount++
            } else {
                wrongCount++
            }
        }

        val totalQ = currentQuestions.size
        val score = if (totalQ > 0) ((correctCount.toDouble() / totalQ.toDouble()) * 100).toInt() else 0

        val result = ExamResult(
            subjectId = subject.id,
            subjectTitle = subject.title,
            grade = _selectedGrade.value,
            score = score,
            correctCount = correctCount,
            wrongCount = wrongCount,
            unansweredCount = unansweredCount,
            totalQuestions = totalQ,
            timeSpentSeconds = _elapsedTimeSeconds.value,
            userAnswers = answers,
            flaggedQuestions = _flaggedQuestions.value,
            questions = currentQuestions
        )

        _lastExamResult.value = result
        _examHistory.update { listOf(result) + it }
        _currentScreen.value = ScreenState.RESULT
    }

    fun openReview() {
        _currentScreen.value = ScreenState.REVIEW
    }

    fun navigateToHome() {
        timerJob?.cancel()
        _currentScreen.value = ScreenState.HOME
    }
}
