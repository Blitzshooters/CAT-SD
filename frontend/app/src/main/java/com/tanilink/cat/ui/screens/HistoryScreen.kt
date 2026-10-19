package com.tanilink.cat.ui.screens

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.tanilink.cat.model.ExamResult
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HistoryScreen(
    examHistory: List<ExamResult>,
    isAdmin: Boolean = false,
    onOpenReview: (ExamResult) -> Unit,
    onDeleteResult: ((ExamResult) -> Unit)? = null,
    onClearAllHistory: (() -> Unit)? = null
) {
    var showClearConfirmDialog by remember { mutableStateOf(false) }

    val filteredHistory = examHistory

    val totalExams = examHistory.size
    val avgScore = if (totalExams > 0) examHistory.map { it.score }.average().toInt() else 0
    val maxScore = if (totalExams > 0) examHistory.maxOf { it.score } else 0

    if (showClearConfirmDialog) {
        AlertDialog(
            onDismissRequest = { showClearConfirmDialog = false },
            icon = { Icon(Icons.Default.DeleteSweep, contentDescription = null, tint = Color(0xFFDC2626)) },
            title = { Text("Hapus Semua Riwayat Ujian?", fontWeight = FontWeight.Bold) },
            text = { Text("Tindakan ini hanya dapat dilakukan oleh Admin. Seluruh riwayat hasil ujian dari database akan dihapus permanen.") },
            confirmButton = {
                Button(
                    onClick = {
                        onClearAllHistory?.invoke()
                        showClearConfirmDialog = false
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = Color(0xFFDC2626))
                ) {
                    Text("Hapus Semua", fontWeight = FontWeight.Bold, color = Color.White)
                }
            },
            dismissButton = {
                OutlinedButton(onClick = { showClearConfirmDialog = false }) {
                    Text("Batal")
                }
            }
        )
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Riwayat Hasil Ujian", fontWeight = FontWeight.Bold) },
                actions = {
                    if (isAdmin && examHistory.isNotEmpty()) {
                        IconButton(onClick = { showClearConfirmDialog = true }) {
                            Icon(Icons.Default.DeleteSweep, contentDescription = "Hapus Semua Riwayat", tint = Color(0xFFDC2626))
                        }
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.surface,
                    titleContentColor = MaterialTheme.colorScheme.onSurface
                )
            )
        },
        containerColor = MaterialTheme.colorScheme.background
    ) { innerPadding ->
        if (examHistory.isEmpty()) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(innerPadding),
                contentAlignment = Alignment.Center
            ) {
                Card(
                    shape = RoundedCornerShape(24.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    modifier = Modifier
                        .padding(24.dp)
                        .fillMaxWidth()
                ) {
                    Column(
                        modifier = Modifier
                            .padding(24.dp)
                            .fillMaxWidth(),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Surface(
                            shape = CircleShape,
                            color = MaterialTheme.colorScheme.primaryContainer,
                            modifier = Modifier.size(72.dp)
                        ) {
                            Box(contentAlignment = Alignment.Center) {
                                Icon(
                                    imageVector = Icons.Default.Assignment,
                                    contentDescription = null,
                                    tint = MaterialTheme.colorScheme.primary,
                                    modifier = Modifier.size(36.dp)
                                )
                            }
                        }
                        Spacer(modifier = Modifier.height(16.dp))
                        Text(
                            text = "Belum Ada Riwayat Ujian",
                            fontSize = 18.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Spacer(modifier = Modifier.height(6.dp))
                        Text(
                            text = "Yuk mulai latihan ujian pertama kamu di menu Beranda!",
                            fontSize = 13.sp,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            modifier = Modifier.padding(horizontal = 16.dp)
                        )
                    }
                }
            }
        } else {
            LazyColumn(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(innerPadding)
                    .padding(horizontal = 16.dp),
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                item { Spacer(modifier = Modifier.height(4.dp)) }

                // Performance Header Card Dashboard
                item {
                    Card(
                        shape = RoundedCornerShape(24.dp),
                        colors = CardDefaults.cardColors(containerColor = Color.Transparent),
                        modifier = Modifier
                            .fillMaxWidth()
                            .shadow(6.dp, RoundedCornerShape(24.dp))
                    ) {
                        Box(
                            modifier = Modifier
                                .background(
                                    Brush.horizontalGradient(
                                        colors = listOf(Color(0xFF4F46E5), Color(0xFF7C3AED))
                                    )
                                )
                                .padding(20.dp)
                        ) {
                            Column {
                                Row(
                                    verticalAlignment = Alignment.CenterVertically,
                                    horizontalArrangement = Arrangement.SpaceBetween,
                                    modifier = Modifier.fillMaxWidth()
                                ) {
                                    Column {
                                        Text(
                                            text = "Ringkasan Performa Belajar",
                                            color = Color.White.copy(alpha = 0.85f),
                                            fontSize = 12.sp,
                                            fontWeight = FontWeight.Medium
                                        )
                                        Text(
                                            text = "$totalExams Ujian Selesai",
                                            color = Color.White,
                                            fontSize = 20.sp,
                                            fontWeight = FontWeight.Bold
                                        )
                                    }

                                    Surface(
                                        shape = CircleShape,
                                        color = Color.White.copy(alpha = 0.2f),
                                        modifier = Modifier.size(46.dp)
                                    ) {
                                        Box(contentAlignment = Alignment.Center) {
                                            Icon(
                                                imageVector = Icons.Default.EmojiEvents,
                                                contentDescription = null,
                                                tint = Color(0xFFFFD700),
                                                modifier = Modifier.size(26.dp)
                                            )
                                        }
                                    }
                                }

                                Spacer(modifier = Modifier.height(16.dp))
                                HorizontalDivider(color = Color.White.copy(alpha = 0.2f))
                                Spacer(modifier = Modifier.height(14.dp))

                                Row(
                                    modifier = Modifier.fillMaxWidth(),
                                    horizontalArrangement = Arrangement.SpaceBetween
                                ) {
                                    HeaderStatItem(title = "Rata-Rata Nilai", value = "$avgScore", icon = Icons.Default.Equalizer)
                                    HeaderStatItem(title = "Nilai Tertinggi", value = "$maxScore", icon = Icons.Default.TrendingUp)
                                    HeaderStatItem(title = "Total Soal", value = "${totalExams * 20}", icon = Icons.Default.Quiz)
                                }
                            }
                        }
                    }
                }



                item {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = "Daftar Riwayat Ujian",
                            fontSize = 16.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Text(
                            text = "${filteredHistory.size} Hasil",
                            fontSize = 12.sp,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )
                    }
                }

                // List of History Cards
                items(filteredHistory) { result ->
                    // Derive color palette from subjectId hash (consistent per subject)
                    val colorIndex = kotlin.math.abs(result.subjectId.hashCode()) % 4
                    val subjectColor = when (colorIndex) {
                        0 -> Color(0xFF4F46E5)
                        1 -> Color(0xFF0284C7)
                        2 -> Color(0xFF16A34A)
                        else -> Color(0xFFDC2626)
                    }
                    val subjectSecondary = when (colorIndex) {
                        0 -> Color(0xFFEEF2FF)
                        1 -> Color(0xFFE0F2FE)
                        2 -> Color(0xFFDCFCE7)
                        else -> Color(0xFFFEE2E2)
                    }

                    val icon = when {
                        result.subjectTitle.contains("Matematika", ignoreCase = true) -> Icons.Default.Calculate
                        result.subjectTitle.contains("IPA", ignoreCase = true) ||
                        result.subjectTitle.contains("Sains", ignoreCase = true) -> Icons.Default.Science
                        result.subjectTitle.contains("Bahasa", ignoreCase = true) ||
                        result.subjectTitle.contains("IPS", ignoreCase = true) -> Icons.Default.MenuBook
                        else -> Icons.Default.School
                    }

                    val starCount = when {
                        result.score >= 80 -> 3
                        result.score >= 60 -> 2
                        else -> 1
                    }

                    val dateFormat = SimpleDateFormat("EEEE, dd MMM yyyy • HH:mm", Locale("id", "ID"))
                    val formattedDate = dateFormat.format(Date(result.timestamp))

                    val minutes = result.timeSpentSeconds / 60
                    val seconds = result.timeSpentSeconds % 60
                    val timeStr = "${minutes}m ${seconds}s"

                    Card(
                        shape = RoundedCornerShape(16.dp),
                        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                        elevation = CardDefaults.cardElevation(defaultElevation = 1.5.dp),
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable { onOpenReview(result) }
                    ) {
                        Column(modifier = Modifier.padding(14.dp)) {
                            // Card Top Header
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                Surface(
                                    shape = RoundedCornerShape(12.dp),
                                    color = subjectSecondary,
                                    modifier = Modifier.size(40.dp)
                                ) {
                                    Box(contentAlignment = Alignment.Center) {
                                        Icon(
                                            imageVector = icon,
                                            contentDescription = null,
                                            tint = subjectColor,
                                            modifier = Modifier.size(22.dp)
                                        )
                                    }
                                }

                                Spacer(modifier = Modifier.width(10.dp))

                                Column(modifier = Modifier.weight(1f)) {
                                    Row(verticalAlignment = Alignment.CenterVertically) {
                                        Text(
                                            text = result.subjectTitle,
                                            fontSize = 15.sp,
                                            fontWeight = FontWeight.Bold,
                                            color = MaterialTheme.colorScheme.onSurface
                                        )
                                        Spacer(modifier = Modifier.width(6.dp))
                                        Surface(
                                            shape = RoundedCornerShape(6.dp),
                                            color = MaterialTheme.colorScheme.surfaceVariant
                                        ) {
                                            Text(
                                                text = "Kelas ${result.grade} SD",
                                                modifier = Modifier.padding(horizontal = 6.dp, vertical = 2.dp),
                                                fontSize = 10.sp,
                                                fontWeight = FontWeight.Bold,
                                                color = MaterialTheme.colorScheme.onSurfaceVariant
                                            )
                                        }
                                    }
                                    Spacer(modifier = Modifier.height(2.dp))
                                    Text(
                                        text = formattedDate,
                                        fontSize = 11.sp,
                                        color = MaterialTheme.colorScheme.onSurfaceVariant
                                    )
                                }

                                // Score Pill + Star Rating
                                Column(horizontalAlignment = Alignment.End) {
                                    Surface(
                                        shape = RoundedCornerShape(10.dp),
                                        color = if (result.score >= 70) Color(0xFFDCFCE7) else Color(0xFFFEE2E2)
                                    ) {
                                        Text(
                                            text = "${result.score}",
                                            modifier = Modifier.padding(horizontal = 10.dp, vertical = 3.dp),
                                            fontSize = 15.sp,
                                            fontWeight = FontWeight.Black,
                                            color = if (result.score >= 70) Color(0xFF166534) else Color(0xFF991B1B)
                                        )
                                    }

                                    Spacer(modifier = Modifier.height(2.dp))

                                    // Stars
                                    Row {
                                        for (i in 1..3) {
                                            Icon(
                                                imageVector = if (i <= starCount) Icons.Default.Star else Icons.Default.StarBorder,
                                                contentDescription = null,
                                                tint = if (i <= starCount) Color(0xFFFFD700) else Color(0xFFCBD5E1),
                                                modifier = Modifier.size(13.dp)
                                            )
                                        }
                                    }
                                }
                            }

                            Spacer(modifier = Modifier.height(10.dp))
                            HorizontalDivider(color = MaterialTheme.colorScheme.outlineVariant.copy(alpha = 0.4f))
                            Spacer(modifier = Modifier.height(10.dp))

                            // 1. Stats Breakdown Grid: Benar, Salah, Kosong, Waktu
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(6.dp),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                StatChip(
                                    label = "Benar",
                                    value = "${result.correctCount}",
                                    color = Color(0xFF16A34A),
                                    bgColor = Color(0xFFDCFCE7),
                                    modifier = Modifier.weight(1f)
                                )
                                StatChip(
                                    label = "Salah",
                                    value = "${result.wrongCount}",
                                    color = Color(0xFFDC2626),
                                    bgColor = Color(0xFFFEE2E2),
                                    modifier = Modifier.weight(1f)
                                )
                                StatChip(
                                    label = "Kosong",
                                    value = "${result.unansweredCount}",
                                    color = Color(0xFFD97706),
                                    bgColor = Color(0xFFFEF3C7),
                                    modifier = Modifier.weight(1f)
                                )
                                StatChip(
                                    label = "Waktu",
                                    value = timeStr,
                                    color = Color(0xFF2563EB),
                                    bgColor = Color(0xFFEFF6FF),
                                    modifier = Modifier.weight(1.15f)
                                )
                            }

                            Spacer(modifier = Modifier.height(10.dp))

                            // 2. Action Buttons: Tombol Sampah (Admin) & Tombol Pembahasan
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(8.dp),
                                verticalAlignment = Alignment.CenterVertically
                            ) {
                                if (isAdmin && onDeleteResult != null) {
                                    Surface(
                                        shape = RoundedCornerShape(10.dp),
                                        color = Color(0xFFFEE2E2),
                                        border = BorderStroke(1.dp, Color(0xFFFECACA)),
                                        modifier = Modifier
                                            .size(36.dp)
                                            .clickable { onDeleteResult(result) }
                                    ) {
                                        Box(contentAlignment = Alignment.Center) {
                                            Icon(
                                                imageVector = Icons.Default.Delete,
                                                contentDescription = "Hapus Riwayat Ujian",
                                                tint = Color(0xFFDC2626),
                                                modifier = Modifier.size(18.dp)
                                            )
                                        }
                                    }
                                }

                                FilledTonalButton(
                                    onClick = { onOpenReview(result) },
                                    shape = RoundedCornerShape(10.dp),
                                    colors = ButtonDefaults.filledTonalButtonColors(
                                        containerColor = MaterialTheme.colorScheme.primaryContainer,
                                        contentColor = MaterialTheme.colorScheme.onPrimaryContainer
                                    ),
                                    contentPadding = PaddingValues(horizontal = 14.dp, vertical = 0.dp),
                                    modifier = Modifier
                                        .weight(1f)
                                        .height(36.dp)
                                ) {
                                    Icon(
                                        imageVector = Icons.Default.MenuBook,
                                        contentDescription = null,
                                        modifier = Modifier.size(15.dp)
                                    )
                                    Spacer(modifier = Modifier.width(6.dp))
                                    Text(
                                        text = "Lihat Pembahasan",
                                        fontSize = 12.sp,
                                        fontWeight = FontWeight.Bold
                                    )
                                    Spacer(modifier = Modifier.weight(1f))
                                    Icon(
                                        imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                                        contentDescription = null,
                                        modifier = Modifier.size(14.dp)
                                    )
                                }
                            }
                        }
                    }
                }

                item { Spacer(modifier = Modifier.height(24.dp)) }
            }
        }
    }
}

@Composable
fun HeaderStatItem(title: String, value: String, icon: ImageVector) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Icon(imageVector = icon, contentDescription = null, tint = Color.White.copy(alpha = 0.85f), modifier = Modifier.size(18.dp))
        Spacer(modifier = Modifier.width(6.dp))
        Column {
            Text(text = value, fontSize = 16.sp, fontWeight = FontWeight.Bold, color = Color.White)
            Text(text = title, fontSize = 10.sp, color = Color.White.copy(alpha = 0.8f))
        }
    }
}

@Composable
fun StatChip(
    label: String,
    value: String,
    color: Color,
    bgColor: Color = color.copy(alpha = 0.1f),
    modifier: Modifier = Modifier
) {
    Surface(
        shape = RoundedCornerShape(8.dp),
        color = bgColor,
        modifier = modifier
    ) {
        Column(
            modifier = Modifier.padding(vertical = 5.dp, horizontal = 4.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            Text(
                text = value,
                fontSize = 12.sp,
                fontWeight = FontWeight.Black,
                color = color,
                maxLines = 1
            )
            Spacer(modifier = Modifier.height(1.dp))
            Text(
                text = label,
                fontSize = 9.sp,
                fontWeight = FontWeight.SemiBold,
                color = color.copy(alpha = 0.85f),
                maxLines = 1
            )
        }
    }
}
