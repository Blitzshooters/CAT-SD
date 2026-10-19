package com.tanilink.cat.data

import androidx.compose.ui.graphics.Color
import com.tanilink.cat.model.ExamSubject
import com.tanilink.cat.model.UserAvatar

object SampleData {

    val avatars = listOf(
        UserAvatar("av1", "Kelinci Ceria", "Pets", Color(0xFFFCE4EC)),
        UserAvatar("av2", "Beruang Pintar", "EmojiEmotions", Color(0xFFFFF8E1)),
        UserAvatar("av3", "Robot Cerdas", "SmartToy", Color(0xFFE1F5FE)),
        UserAvatar("av4", "Astronot Cilik", "RocketLaunch", Color(0xFFA5B4FC)),
        UserAvatar("av5", "Juara Super", "SportsEsports", Color(0xFFDCFCE7))
    )

    val subjects = listOf(
        ExamSubject(
            id = "mtk_sd",
            title = "Matematika",
            iconName = "Calculate",
            primaryColor = Color(0xFFFF6F00), // Vibrant Orange
            secondaryColor = Color(0xFFFFF3E0),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal hitungan angka, pecahan, dan bangun datar!"
        ),
        ExamSubject(
            id = "ipa_sd",
            title = "IPA (Sains)",
            iconName = "Science",
            primaryColor = Color(0xFF2E7D32), // Vibrant Green
            secondaryColor = Color(0xFFE8F5E9),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal mahluk hidup, alam, dan sains ceria."
        ),
        ExamSubject(
            id = "bind_sd",
            title = "Bahasa Indonesia",
            iconName = "MenuBook",
            primaryColor = Color(0xFF1565C0), // Bright Blue
            secondaryColor = Color(0xFFE3F2FD),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal bacaan, ide pokok, dan kata baku."
        ),
        ExamSubject(
            id = "pkn_sd",
            title = "Pancasila & PKn",
            iconName = "Security",
            primaryColor = Color(0xFFC62828), // Bold Red
            secondaryColor = Color(0xFFFFEBEE),
            questionCount = 20,
            durationMinutes = 20,
            description = "Latihan 20 soal nilai Pancasila, hak, dan kewajiban."
        )
    )
}
