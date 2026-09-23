package com.tanilink.cat.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.tanilink.cat.data.SampleData
import com.tanilink.cat.model.AppThemeOption
import com.tanilink.cat.model.ExamResult
import com.tanilink.cat.model.ExamSubject
import com.tanilink.cat.model.MainTab
import com.tanilink.cat.model.Question
import com.tanilink.cat.model.ScreenState
import com.tanilink.cat.model.UserAvatar
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

class ExamViewModel : ViewModel() {

    private val _studentName = MutableStateFlow("Adit Pratama")
    val studentName: StateFlow<String> = _studentName.asStateFlow()

    private val _selectedGrade = MutableStateFlow(5)
    val selectedGrade: StateFlow<Int> = _selectedGrade.asStateFlow()

    private val _selectedAvatar = MutableStateFlow<UserAvatar>(SampleData.avatars[0])
    val selectedAvatar: StateFlow<UserAvatar> = _selectedAvatar.asStateFlow()

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

    private var timerJob: Job? = null

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

        val totalSeconds = subject.durationMinutes * 60L
        _remainingSeconds.value = totalSeconds
        _elapsedTimeSeconds.value = 0L

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
            }
            if (_remainingSeconds.value <= 0 && _currentScreen.value == ScreenState.EXAM) {
                submitExam()
            }
        }
    }

    fun selectAnswer(questionIndex: Int, optionIndex: Int) {
        _userAnswers.update { current ->
            current.toMutableMap().apply { put(questionIndex, optionIndex) }
        }
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
