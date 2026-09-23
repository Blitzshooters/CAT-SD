package com.tanilink.cat

import android.os.Bundle
import android.view.WindowManager
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.animation.Crossfade
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalLifecycleOwner
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.LifecycleEventObserver
import androidx.lifecycle.viewmodel.compose.viewModel
import com.tanilink.cat.model.AppThemeOption
import com.tanilink.cat.model.MainTab
import com.tanilink.cat.model.ScreenState
import com.tanilink.cat.ui.screens.*
import com.tanilink.cat.ui.theme.CATTheme
import com.tanilink.cat.viewmodel.ExamViewModel

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            val viewModel: ExamViewModel = viewModel()
            val themeOption by viewModel.themeOption.collectAsState()
            val currentScreen by viewModel.currentScreen.collectAsState()
            val isLoggedIn by viewModel.isLoggedIn.collectAsState()

            // Dynamic FLAG_SECURE for Anti-Screenshot & Screen Capture during Exam
            DisposableEffect(currentScreen) {
                if (currentScreen == ScreenState.EXAM) {
                    window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
                } else {
                    window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                }
                onDispose {
                    window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                }
            }

            val isDarkTheme = when (themeOption) {
                AppThemeOption.LIGHT -> false
                AppThemeOption.DARK -> true
                AppThemeOption.SYSTEM -> isSystemInDarkTheme()
            }

            CATTheme(darkTheme = isDarkTheme) {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    if (!isLoggedIn) {
                        LoginScreen(
                            onLoginSuccess = { name, avatar, token ->
                                viewModel.login(name, avatar, token)
                            }
                        )
                    } else {
                        CatAppNavigation(viewModel = viewModel)
                    }
                }
            }
        }
    }
}


@Composable
fun CatAppNavigation(viewModel: ExamViewModel) {
    val currentScreen by viewModel.currentScreen.collectAsState()
    val activeTab by viewModel.activeTab.collectAsState()
    val studentName by viewModel.studentName.collectAsState()
    val selectedGrade by viewModel.selectedGrade.collectAsState()
    val selectedAvatar by viewModel.selectedAvatar.collectAsState()
    val themeOption by viewModel.themeOption.collectAsState()
    val currentSubject by viewModel.currentSubject.collectAsState()
    val questions by viewModel.questions.collectAsState()
    val currentIndex by viewModel.currentQuestionIndex.collectAsState()
    val userAnswers by viewModel.userAnswers.collectAsState()
    val flaggedQuestions by viewModel.flaggedQuestions.collectAsState()
    val remainingSeconds by viewModel.remainingSeconds.collectAsState()
    val lastResult by viewModel.lastExamResult.collectAsState()
    val examHistory by viewModel.examHistory.collectAsState()
    val faceStatus by viewModel.faceStatus.collectAsState()
    val warningText by viewModel.proctoringWarningText.collectAsState()
    val proctoringLogs by viewModel.proctoringLogs.collectAsState()
    val violationCount by viewModel.violationCount.collectAsState()

    // Switch-Tab Lifecycle Observer
    val lifecycleOwner = LocalLifecycleOwner.current
    DisposableEffect(currentScreen, lifecycleOwner) {
        val observer = LifecycleEventObserver { _, event ->
            if (event == Lifecycle.Event.ON_PAUSE && currentScreen == ScreenState.EXAM) {
                viewModel.notifyAppSwitchTab()
            }
        }
        lifecycleOwner.lifecycle.addObserver(observer)
        onDispose {
            lifecycleOwner.lifecycle.removeObserver(observer)
        }
    }

    if (currentScreen == ScreenState.HOME) {
        Scaffold(
            bottomBar = {
                NavigationBar(
                    containerColor = MaterialTheme.colorScheme.surface,
                    tonalElevation = 8.dp
                ) {
                    NavigationBarItem(
                        selected = activeTab == MainTab.EXAM_HOME,
                        onClick = { viewModel.selectTab(MainTab.EXAM_HOME) },
                        icon = { Icon(Icons.Default.School, contentDescription = "Ujian") },
                        label = { Text("Beranda", fontWeight = FontWeight.Bold) },
                        colors = NavigationBarItemDefaults.colors(
                            selectedIconColor = MaterialTheme.colorScheme.primary,
                            selectedTextColor = MaterialTheme.colorScheme.primary,
                            indicatorColor = MaterialTheme.colorScheme.primaryContainer
                        )
                    )
                    NavigationBarItem(
                        selected = activeTab == MainTab.HISTORY,
                        onClick = { viewModel.selectTab(MainTab.HISTORY) },
                        icon = { Icon(Icons.Default.History, contentDescription = "Riwayat") },
                        label = { Text("Riwayat", fontWeight = FontWeight.Bold) },
                        colors = NavigationBarItemDefaults.colors(
                            selectedIconColor = MaterialTheme.colorScheme.primary,
                            selectedTextColor = MaterialTheme.colorScheme.primary,
                            indicatorColor = MaterialTheme.colorScheme.primaryContainer
                        )
                    )
                    NavigationBarItem(
                        selected = activeTab == MainTab.PROFILE,
                        onClick = { viewModel.selectTab(MainTab.PROFILE) },
                        icon = { Icon(Icons.Default.Person, contentDescription = "Profil") },
                        label = { Text("Profil", fontWeight = FontWeight.Bold) },
                        colors = NavigationBarItemDefaults.colors(
                            selectedIconColor = MaterialTheme.colorScheme.primary,
                            selectedTextColor = MaterialTheme.colorScheme.primary,
                            indicatorColor = MaterialTheme.colorScheme.primaryContainer
                        )
                    )
                }
            }
        ) { innerPadding ->
            Surface(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(innerPadding),
                color = MaterialTheme.colorScheme.background
            ) {
                Crossfade(targetState = activeTab, label = "tabTransition") { tab ->
                    when (tab) {
                        MainTab.EXAM_HOME -> {
                            HomeScreen(
                                studentName = studentName,
                                selectedGrade = selectedGrade,
                                selectedAvatar = selectedAvatar,
                                examHistory = examHistory,
                                onSelectGrade = { grade ->
                                    viewModel.selectGrade(grade)
                                },
                                onStartExam = { subject ->
                                    viewModel.startExam(subject)
                                },
                                onGoToProfile = {
                                    viewModel.selectTab(MainTab.PROFILE)
                                }
                            )
                        }

                        MainTab.HISTORY -> {
                            HistoryScreen(
                                examHistory = examHistory,
                                onOpenReview = { result ->
                                    viewModel.openReview()
                                }
                            )
                        }

                        MainTab.PROFILE -> {
                            ProfileScreen(
                                studentName = studentName,
                                selectedGrade = selectedGrade,
                                selectedAvatar = selectedAvatar,
                                themeOption = themeOption,
                                examHistory = examHistory,
                                onUpdateProfile = { name, grade, avatar ->
                                    viewModel.updateStudentProfile(name, grade, avatar)
                                },
                                onSelectTheme = { option ->
                                    viewModel.setThemeOption(option)
                                }
                            )
                        }
                    }
                }
            }
        }
    } else {
        Crossfade(targetState = currentScreen, label = "screenTransition") { screen ->
            when (screen) {
                ScreenState.EXAM -> {
                    currentSubject?.let { subject ->
                        ExamScreen(
                            subject = subject,
                            questions = questions,
                            currentIndex = currentIndex,
                            userAnswers = userAnswers,
                            flaggedQuestions = flaggedQuestions,
                            remainingSeconds = remainingSeconds,
                            faceStatus = faceStatus,
                            proctoringWarningText = warningText,
                            proctoringLogs = proctoringLogs,
                            violationCount = violationCount,
                            onFaceStatusChanged = { status, desc ->
                                viewModel.updateFaceStatus(status, desc)
                            },
                            onSelectAnswer = { qIdx, optIdx ->
                                viewModel.selectAnswer(qIdx, optIdx)
                            },
                            onToggleFlag = { qIdx ->
                                viewModel.toggleFlag(qIdx)
                            },
                            onGoToQuestion = { index ->
                                viewModel.goToQuestion(index)
                            },
                            onNextQuestion = {
                                viewModel.nextQuestion()
                            },
                            onPreviousQuestion = {
                                viewModel.previousQuestion()
                            },
                            onSubmitExam = {
                                viewModel.submitExam()
                            },
                            onCancelExam = {
                                viewModel.navigateToHome()
                            }
                        )
                    }
                }

                ScreenState.RESULT -> {
                    lastResult?.let { result ->
                        ResultScreen(
                            result = result,
                            studentName = studentName,
                            onOpenReview = {
                                viewModel.openReview()
                            },
                            onBackToHome = {
                                viewModel.navigateToHome()
                            }
                        )
                    }
                }

                ScreenState.REVIEW -> {
                    lastResult?.let { result ->
                        ReviewScreen(
                            result = result,
                            onBackToHome = {
                                viewModel.navigateToHome()
                            }
                        )
                    }
                }

                else -> {}
            }
        }
    }
}