package com.tanilink.cat

import android.os.Bundle
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
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
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
                    CatAppNavigation(viewModel = viewModel)
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