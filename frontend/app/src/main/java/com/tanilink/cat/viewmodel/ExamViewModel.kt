package com.tanilink.cat.viewmodel

import android.app.Application
import android.content.Context
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.tanilink.cat.data.CatApiClient
import com.tanilink.cat.data.ExamDatabaseHelper
import com.tanilink.cat.data.SampleData
import androidx.compose.ui.graphics.Color
import com.tanilink.cat.model.*
import com.tanilink.cat.proctoring.FaceStatus
import com.tanilink.cat.proctoring.ProctoringViolation
import com.tanilink.cat.proctoring.ViolationType
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.Job
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

class ExamViewModel(application: Application) : AndroidViewModel(application) {

    private val dbHelper = ExamDatabaseHelper(application)
    private val prefs = application.getSharedPreferences("cat_session_prefs", Context.MODE_PRIVATE)

    private val _studentName = MutableStateFlow("Zam Zam")
    val studentName: StateFlow<String> = _studentName.asStateFlow()

    private val _nomorInduk = MutableStateFlow("")
    val nomorInduk: StateFlow<String> = _nomorInduk.asStateFlow()

    private val _selectedGrade = MutableStateFlow(5)
    val selectedGrade: StateFlow<Int> = _selectedGrade.asStateFlow()

    private val _selectedAvatar = MutableStateFlow<UserAvatar>(SampleData.avatars[0])
    val selectedAvatar: StateFlow<UserAvatar> = _selectedAvatar.asStateFlow()

    private val _customAvatarUrl = MutableStateFlow("")
    val customAvatarUrl: StateFlow<String> = _customAvatarUrl.asStateFlow()

    private val _jwtToken = MutableStateFlow("")
    val jwtToken: StateFlow<String> = _jwtToken.asStateFlow()

    private val _isLoggedIn = MutableStateFlow(false)
    val isLoggedIn: StateFlow<Boolean> = _isLoggedIn.asStateFlow()

    private val _isAdmin = MutableStateFlow(false)
    val isAdmin: StateFlow<Boolean> = _isAdmin.asStateFlow()

    private val _themeOption = MutableStateFlow(AppThemeOption.LIGHT)
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

    private val _isExamLocked = MutableStateFlow(false)
    val isExamLocked: StateFlow<Boolean> = _isExamLocked.asStateFlow()

    private val _lockReason = MutableStateFlow("")
    val lockReason: StateFlow<String> = _lockReason.asStateFlow()

    private val _remedyError = MutableStateFlow<String?>(null)
    val remedyError: StateFlow<String?> = _remedyError.asStateFlow()

    private val _isVerifyingRemedy = MutableStateFlow(false)
    val isVerifyingRemedy: StateFlow<Boolean> = _isVerifyingRemedy.asStateFlow()

    private val _backendSubjects = MutableStateFlow<List<ExamSubject>>(emptyList())
    val backendSubjects: StateFlow<List<ExamSubject>> = _backendSubjects.asStateFlow()

    private var timerJob: Job? = null
    private var lastAnswerTimeMs = 0L

    init {
        val savedLoggedIn = prefs.getBoolean("is_logged_in", false)
        if (savedLoggedIn) {
            val savedName = prefs.getString("student_name", "Zam Zam") ?: "Zam Zam"
            val savedIsAdmin = prefs.getBoolean("is_admin", false)
            val savedToken = prefs.getString("jwt_token", "") ?: ""
            val savedGrade = prefs.getInt("selected_grade", 5)
            val savedAvatarId = prefs.getString("avatar_id", "av1") ?: "av1"
            val savedNomorInduk = prefs.getString("nomor_induk", "") ?: ""
            val savedCustomAvatarUrl = prefs.getString("custom_avatar_url", "") ?: ""
            val restoredAvatar = if (savedAvatarId == "custom" && savedCustomAvatarUrl.isNotBlank()) {
                customAvatarUser()
            } else {
                SampleData.avatars.find { it.id == savedAvatarId } ?: SampleData.avatars[0]
            }

            _studentName.value = savedName
            _nomorInduk.value = savedNomorInduk
            _isAdmin.value = savedIsAdmin
            _jwtToken.value = savedToken
            _selectedGrade.value = savedGrade
            _selectedAvatar.value = restoredAvatar
            _customAvatarUrl.value = if (savedAvatarId == "custom") savedCustomAvatarUrl else ""
            _isLoggedIn.value = true
        }
        loadHistoryFromDatabase()
        fetchSubjectsFromBackend(_selectedGrade.value)
    }

    fun fetchSubjectsFromBackend(grade: Int = _selectedGrade.value) {
        viewModelScope.launch {
            val remoteSubjects = CatApiClient.getSubjects(grade)
            if (!remoteSubjects.isNullOrEmpty()) {
                _backendSubjects.value = remoteSubjects
            } else {
                _backendSubjects.value = SampleData.subjects.map { it.copy(gradeLevel = grade) }
            }
        }
    }

    fun loadHistoryFromDatabase() {
        viewModelScope.launch(Dispatchers.IO) {
            val savedHistory = if (_isAdmin.value) {
                dbHelper.getAllExamResults()
            } else {
                dbHelper.getExamResultsForStudent(_studentName.value)
            }
            _examHistory.value = savedHistory
        }
    }

    fun login(
        name: String,
        avatar: UserAvatar,
        token: String,
        isAdminUser: Boolean = false,
        username: String = "",
        nomorInduk: String = "",
        serverAvatar: String = ""
    ) {
        val (resolvedAvatar, customUrl) = resolveAvatar(serverAvatar, avatar)

        _studentName.value = name
        _nomorInduk.value = nomorInduk
        _selectedAvatar.value = resolvedAvatar
        _customAvatarUrl.value = customUrl
        _jwtToken.value = token
        _isAdmin.value = isAdminUser
        _isLoggedIn.value = true
        _currentScreen.value = ScreenState.HOME

        prefs.edit()
            .putBoolean("is_logged_in", true)
            .putString("student_name", name)
            .putString("nomor_induk", nomorInduk)
            .putBoolean("is_admin", isAdminUser)
            .putString("jwt_token", token)
            .putString("avatar_id", resolvedAvatar.id)
            .putString("custom_avatar_url", customUrl)
            .putString("username", username)
            .apply()

        loadHistoryFromDatabase()
    }

    fun logout() {
        _isLoggedIn.value = false
        _isAdmin.value = false
        _jwtToken.value = ""
        _nomorInduk.value = ""
        _customAvatarUrl.value = ""

        prefs.edit()
            .putBoolean("is_logged_in", false)
            .remove("jwt_token")
            .remove("nomor_induk")
            .remove("custom_avatar_url")
            .apply()
    }

    fun deleteExamResult(result: ExamResult) {
        if (!_isAdmin.value) return // Only Admin can delete history
        viewModelScope.launch(Dispatchers.IO) {
            dbHelper.deleteExamResultByTimestamp(result.timestamp)
            _examHistory.update { current -> current.filter { it.timestamp != result.timestamp } }
        }
    }

    fun clearAllHistory() {
        if (!_isAdmin.value) return // Only Admin can clear history
        viewModelScope.launch(Dispatchers.IO) {
            dbHelper.deleteAllExamResults()
            _examHistory.value = emptyList()
        }
    }

    fun updateStudentProfile(name: String, grade: Int, avatar: UserAvatar) {
        _studentName.value = name
        _selectedGrade.value = grade
        _selectedAvatar.value = avatar

        val username = prefs.getString("username", "") ?: ""
        prefs.edit()
            .putString("student_name", name)
            .putInt("selected_grade", grade)
            .putString("avatar_id", avatar.id)
            .apply()

        fetchSubjectsFromBackend(grade)

        // Sync profil ke backend MySQL
        if (username.isNotEmpty()) {
            viewModelScope.launch {
                val avatarValue = if (avatar.id == "custom" && _customAvatarUrl.value.isNotBlank()) {
                    _customAvatarUrl.value
                } else {
                    avatar.id
                }
                CatApiClient.updateProfile(
                    username = username,
                    name = name,
                    avatarId = avatarValue,
                    grade = grade
                )
            }
        }
    }

    fun changePassword(currentPass: String, newPass: String, onResult: (Boolean, String) -> Unit) {
        val username = prefs.getString("username", "") ?: ""
        if (username.isEmpty()) {
            onResult(false, "Sesi login tidak valid. Silakan login ulang.")
            return
        }
        viewModelScope.launch {
            val (success, msg) = CatApiClient.changePassword(username, currentPass, newPass)
            onResult(success, msg)
        }
    }

    fun uploadCustomAvatar(base64Data: String, onResult: (Boolean, String) -> Unit) {
        val username = prefs.getString("username", "") ?: ""
        if (username.isEmpty()) {
            onResult(false, "Sesi login tidak valid.")
            return
        }
        viewModelScope.launch {
            val (uploadedUrl, errorMessage) = CatApiClient.uploadAvatarBase64(username, base64Data)
            if (uploadedUrl != null) {
                _selectedAvatar.value = customAvatarUser()
                _customAvatarUrl.value = uploadedUrl
                prefs.edit()
                    .putString("avatar_id", "custom")
                    .putString("custom_avatar_url", uploadedUrl)
                    .apply()
                onResult(true, "Foto profil berhasil diunggah!")
            } else {
                onResult(false, errorMessage.ifBlank { "Gagal mengunggah foto profil ke server." })
            }
        }
    }

    private fun customAvatarUser() = UserAvatar(
        id = "custom",
        name = "Foto Profil Saya",
        iconName = "Person",
        backgroundColor = Color(0xFF818CF8)
    )

    private fun resolveAvatar(serverAvatar: String, fallbackAvatar: UserAvatar): Pair<UserAvatar, String> {
        if (serverAvatar.isBlank()) {
            return Pair(fallbackAvatar, "")
        }
        if (serverAvatar.startsWith("http") ||
            serverAvatar.startsWith("data:") ||
            serverAvatar.startsWith("/storage")
        ) {
            return Pair(customAvatarUser(), serverAvatar)
        }

        val presetMapping = mapOf(
            "rabbit" to "av1",
            "bear" to "av2",
            "robot" to "av3",
            "astronaut" to "av4",
            "champion" to "av5"
        )
        val avatarId = presetMapping[serverAvatar] ?: serverAvatar
        val presetAvatar = SampleData.avatars.find { it.id == avatarId } ?: fallbackAvatar
        return Pair(presetAvatar, "")
    }

    fun setThemeOption(option: AppThemeOption) {
        _themeOption.value = option
    }

    fun selectGrade(grade: Int) {
        _selectedGrade.value = grade
        prefs.edit().putInt("selected_grade", grade).apply()
        fetchSubjectsFromBackend(grade)
    }

    fun selectTab(tab: MainTab) {
        _activeTab.value = tab
    }

    fun startExam(subject: ExamSubject) {
        _currentSubject.value = subject
        _currentQuestionIndex.value = 0
        _userAnswers.value = emptyMap()
        _flaggedQuestions.value = emptySet()
        _proctoringLogs.value = emptyList()
        _violationCount.value = 0
        _isExamLocked.value = false
        _lockReason.value = ""
        _remedyError.value = null
        _faceStatus.value = FaceStatus.OK
        _proctoringWarningText.value = "Fokus Terjaga"

        // Load local fallback first
        val fallback = SampleData.getQuestionsForSubjectAndGrade(subject.id, _selectedGrade.value)
        _questions.value = fallback

        val totalSeconds = subject.durationMinutes * 60L
        _remainingSeconds.value = totalSeconds
        _elapsedTimeSeconds.value = 0L
        lastAnswerTimeMs = System.currentTimeMillis()

        _currentScreen.value = ScreenState.EXAM
        startTimer()

        // Fetch latest questions from backend API if connected
        viewModelScope.launch {
            val remoteQuestions = CatApiClient.getQuestions(subject.id, _selectedGrade.value)
            if (!remoteQuestions.isNullOrEmpty()) {
                _questions.value = remoteQuestions
            }
        }
    }

    private fun startTimer() {
        timerJob?.cancel()
        timerJob = viewModelScope.launch {
            while (_remainingSeconds.value > 0 && _currentScreen.value == ScreenState.EXAM) {
                delay(1000L)
                if (!_isExamLocked.value) {
                    _remainingSeconds.update { it - 1 }
                    _elapsedTimeSeconds.update { it + 1 }

                    // Periodic Snapshot simulation every 3 minutes
                    if (_elapsedTimeSeconds.value > 0 && _elapsedTimeSeconds.value % 180L == 0L) {
                        addProctoringViolation(ViolationType.SNAPSHOT_CAPTURED, "Snapshot acak kamera depan berhasil diambil.")
                    }
                }
            }
            if (_remainingSeconds.value <= 0 && _currentScreen.value == ScreenState.EXAM && !_isExamLocked.value) {
                submitExam()
            }
        }
    }

    private fun pauseTimer() {
        timerJob?.cancel()
    }

    private fun resumeTimer() {
        startTimer()
    }

    fun verifyAndResetRemedyCode(code: String) {
        val subject = _currentSubject.value ?: return
        val trimmed = code.trim()
        if (trimmed.isEmpty()) {
            _remedyError.value = "Masukkan kode remedi terlebih dahulu!"
            return
        }

        _isVerifyingRemedy.value = true
        _remedyError.value = null

        viewModelScope.launch {
            val localMatch = trimmed.equals(subject.remedyCode.trim(), ignoreCase = true)
            val remoteMatch = CatApiClient.verifyRemedyCode(subject.id, trimmed)

            if (localMatch || remoteMatch) {
                _violationCount.value = 0
                _isExamLocked.value = false
                _lockReason.value = ""
                _remedyError.value = null
                addProctoringViolation(ViolationType.SNAPSHOT_CAPTURED, "Ujian dibuka kembali & pelanggaran direset oleh pengawas dengan kode '$trimmed'.")
                resumeTimer()
            } else {
                _remedyError.value = "Kode remedi salah! Silakan tanyakan kode yang benar kepada pengawas."
            }
            _isVerifyingRemedy.value = false
        }
    }

    private var lastFaceViolationTimeMs = 0L

    fun updateFaceStatus(status: FaceStatus, warningText: String) {
        _faceStatus.value = status
        _proctoringWarningText.value = warningText

        if (_isExamLocked.value) return // Don't record violations while locked

        if (status != FaceStatus.OK && _currentScreen.value == ScreenState.EXAM) {
            val currentTime = System.currentTimeMillis()
            val type = when (status) {
                FaceStatus.NO_FACE -> ViolationType.NO_FACE
                FaceStatus.MULTIPLE_FACES -> ViolationType.MULTIPLE_FACES
                else -> ViolationType.LOOKING_AWAY
            }
            addProctoringViolation(type, warningText)

            // Sanction: Increment violation count every 3s of persistent violation
            if (currentTime - lastFaceViolationTimeMs >= 3000L) {
                lastFaceViolationTimeMs = currentTime
                _violationCount.update { it + 1 }

                // Check maximum violation limit
                val maxAllowed = _currentSubject.value?.maxViolations ?: 3
                if (_violationCount.value >= maxAllowed) {
                    pauseTimer()
                    _isExamLocked.value = true
                    _lockReason.value = "Pelanggaran mencapai batas maksimal (${_violationCount.value}/$maxAllowed)!"
                }
            }
        }
    }

    fun notifyAppSwitchTab() {
        if (_isExamLocked.value) return

        if (_currentScreen.value == ScreenState.EXAM) {
            _violationCount.update { it + 1 }
            addProctoringViolation(ViolationType.SWITCH_TAB, "Pindah aplikasi / keluar layar terdeteksi!")

            val maxAllowed = _currentSubject.value?.maxViolations ?: 3
            if (_violationCount.value >= maxAllowed) {
                pauseTimer()
                _isExamLocked.value = true
                _lockReason.value = "Pindah aplikasi terdeteksi! Pelanggaran mencapai batas ($maxAllowed kali)."
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

        // Save permanently to SQLite Database & Backend MySQL
        viewModelScope.launch(Dispatchers.IO) {
            dbHelper.saveExamResult(_studentName.value, result)
            CatApiClient.submitExamResult(
                subjectId = subject.id,
                grade = _selectedGrade.value,
                score = score,
                correctCount = correctCount,
                wrongCount = wrongCount,
                unansweredCount = unansweredCount,
                totalQuestions = totalQ,
                timeSpentSeconds = _elapsedTimeSeconds.value,
                studentName = _studentName.value
            )
        }

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
