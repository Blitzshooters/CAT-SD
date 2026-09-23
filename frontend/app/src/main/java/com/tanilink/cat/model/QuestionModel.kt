package com.tanilink.cat.model

import androidx.compose.ui.graphics.Color

data class Question(
    val id: Int,
    val prompt: String,
    val options: List<String>,
    val correctAnswerIndex: Int,
    val explanation: String,
    val subjectId: String,
    val gradeLevel: Int
)

data class ExamSubject(
    val id: String,
    val title: String,
    val iconName: String,
    val primaryColor: Color,
    val secondaryColor: Color,
    val questionCount: Int,
    val durationMinutes: Int,
    val description: String
)

data class ExamResult(
    val subjectId: String,
    val subjectTitle: String,
    val grade: Int,
    val score: Int, // 0 - 100
    val correctCount: Int,
    val wrongCount: Int,
    val unansweredCount: Int,
    val totalQuestions: Int,
    val timeSpentSeconds: Long,
    val timestamp: Long = System.currentTimeMillis(),
    val userAnswers: Map<Int, Int>, // Question Index -> Option Index
    val flaggedQuestions: Set<Int>, // Set of Question Indices
    val questions: List<Question>
)

data class UserAvatar(
    val id: String,
    val name: String,
    val iconName: String,
    val backgroundColor: Color
)

enum class ScreenState {
    HOME,
    EXAM,
    RESULT,
    REVIEW
}

enum class MainTab {
    EXAM_HOME,
    HISTORY,
    PROFILE
}

enum class AppThemeOption {
    LIGHT,
    DARK,
    SYSTEM
}

data class Quadruple<A, B, C, D>(
    val first: A,
    val second: B,
    val third: C,
    val fourth: D
)
