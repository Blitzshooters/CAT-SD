package com.tanilink.cat.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.tanilink.cat.model.ExamResult
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ResultScreen(
    result: ExamResult,
    studentName: String,
    onOpenReview: () -> Unit,
    onBackToHome: () -> Unit
) {
    val starCount = when {
        result.score >= 80 -> 3
        result.score >= 60 -> 2
        else -> 1
    }

    val badgeTitle = when (starCount) {
        3 -> "Luar Biasa! 🎉"
        2 -> "Bagus Sekali! 💪"
        else -> "Tetap Semangat! ⭐"
    }

    val badgeSubtitle = when (starCount) {
        3 -> "Kamu berhasil menjawab 20 soal dengan sangat memuaskan!"
        2 -> "Hasil yang bagus! Terus tingkatkan belajar kamu ya!"
        else -> "Jangan menyerah, mari pelajari kembali pembahasan soalnya."
    }

    val minutes = result.timeSpentSeconds / 60
    val seconds = result.timeSpentSeconds % 60
    val formattedDuration = String.format(Locale.getDefault(), "%d m %d s", minutes, seconds)

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Hasil Ujian CAT SD", fontWeight = FontWeight.Bold) },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.surface,
                    titleContentColor = MaterialTheme.colorScheme.onSurface
                )
            )
        },
        containerColor = MaterialTheme.colorScheme.background
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .verticalScroll(rememberScrollState())
                .padding(16.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            Card(
                shape = RoundedCornerShape(24.dp),
                colors = CardDefaults.cardColors(containerColor = Color.Transparent),
                modifier = Modifier
                    .fillMaxWidth()
                    .shadow(8.dp, RoundedCornerShape(24.dp))
            ) {
                Box(
                    modifier = Modifier
                        .background(
                            Brush.verticalGradient(
                                colors = listOf(Color(0xFF4F46E5), Color(0xFF6366F1))
                            )
                        )
                        .padding(24.dp),
                    contentAlignment = Alignment.Center
                ) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(
                            text = result.subjectTitle,
                            color = Color.White.copy(alpha = 0.85f),
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Medium
                        )

                        Spacer(modifier = Modifier.height(8.dp))

                        Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            for (i in 1..3) {
                                Icon(
                                    imageVector = if (i <= starCount) Icons.Default.Star else Icons.Default.StarBorder,
                                    contentDescription = null,
                                    tint = if (i <= starCount) Color(0xFFFFD700) else Color.White.copy(alpha = 0.4f),
                                    modifier = Modifier.size(36.dp)
                                )
                            }
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        Surface(
                            shape = CircleShape,
                            color = Color.White,
                            modifier = Modifier.size(110.dp)
                        ) {
                            Box(contentAlignment = Alignment.Center) {
                                Column(horizontalAlignment = Alignment.CenterHorizontally) {
                                    Text(
                                        text = "${result.score}",
                                        fontSize = 38.sp,
                                        fontWeight = FontWeight.Black,
                                        color = Color(0xFF4F46E5)
                                    )
                                    Text(
                                        text = "SKOR AKHIR",
                                        fontSize = 10.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = Color(0xFF64748B)
                                    )
                                }
                            }
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        Text(
                            text = badgeTitle,
                            color = Color.White,
                            fontSize = 22.sp,
                            fontWeight = FontWeight.Bold
                        )

                        Spacer(modifier = Modifier.height(4.dp))

                        Text(
                            text = badgeSubtitle,
                            color = Color.White.copy(alpha = 0.9f),
                            fontSize = 13.sp,
                            lineHeight = 18.sp,
                            modifier = Modifier.padding(horizontal = 16.dp)
                        )
                    }
                }
            }

            Text(
                text = "Ringkasan Pengerjaan Ujian",
                fontSize = 16.sp,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onSurface,
                modifier = Modifier.align(Alignment.Start)
            )

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                StatCard(
                    modifier = Modifier.weight(1f),
                    title = "Jawaban Benar",
                    value = "${result.correctCount} Soal",
                    icon = Icons.Default.CheckCircle,
                    accentColor = Color(0xFF16A34A),
                    bgColor = Color(0xFFDCFCE7)
                )

                StatCard(
                    modifier = Modifier.weight(1f),
                    title = "Jawaban Salah",
                    value = "${result.wrongCount} Soal",
                    icon = Icons.Default.Cancel,
                    accentColor = Color(0xFFDC2626),
                    bgColor = Color(0xFFFEE2E2)
                )
            }

            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                StatCard(
                    modifier = Modifier.weight(1f),
                    title = "Kosong / Ragu",
                    value = "${result.unansweredCount} Soal",
                    icon = Icons.Default.RemoveCircleOutline,
                    accentColor = Color(0xFFD97706),
                    bgColor = Color(0xFFFEF3C7)
                )

                StatCard(
                    modifier = Modifier.weight(1f),
                    title = "Durasi Waktu",
                    value = formattedDuration,
                    icon = Icons.Default.Timer,
                    accentColor = Color(0xFF2563EB),
                    bgColor = Color(0xFFDBEAFE)
                )
            }

            Spacer(modifier = Modifier.height(8.dp))

            Button(
                onClick = onOpenReview,
                colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primary),
                shape = RoundedCornerShape(16.dp),
                modifier = Modifier
                    .fillMaxWidth()
                    .height(52.dp)
            ) {
                Icon(Icons.Default.MenuBook, contentDescription = null, tint = Color.White)
                Spacer(modifier = Modifier.width(8.dp))
                Text("Lihat Pembahasan 20 Soal", fontWeight = FontWeight.Bold, fontSize = 15.sp, color = Color.White)
            }

            OutlinedButton(
                onClick = onBackToHome,
                shape = RoundedCornerShape(16.dp),
                modifier = Modifier
                    .fillMaxWidth()
                    .height(52.dp)
            ) {
                Icon(Icons.Default.Home, contentDescription = null)
                Spacer(modifier = Modifier.width(8.dp))
                Text("Kembali ke Beranda", fontWeight = FontWeight.SemiBold, fontSize = 15.sp)
            }

            Spacer(modifier = Modifier.height(24.dp))
        }
    }
}

@Composable
fun StatCard(
    modifier: Modifier = Modifier,
    title: String,
    value: String,
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    accentColor: Color,
    bgColor: Color
) {
    Card(
        modifier = modifier,
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Row(
            modifier = Modifier.padding(14.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Surface(
                shape = CircleShape,
                color = bgColor,
                modifier = Modifier.size(40.dp)
            ) {
                Box(contentAlignment = Alignment.Center) {
                    Icon(imageVector = icon, contentDescription = null, tint = accentColor, modifier = Modifier.size(20.dp))
                }
            }

            Spacer(modifier = Modifier.width(10.dp))

            Column {
                Text(text = title, fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
                Text(text = value, fontSize = 14.sp, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.onSurface)
            }
        }
    }
}
