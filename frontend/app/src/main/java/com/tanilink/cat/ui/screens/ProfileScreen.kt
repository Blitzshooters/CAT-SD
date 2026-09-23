package com.tanilink.cat.ui.screens

import androidx.compose.animation.animateColorAsState
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.tanilink.cat.data.SampleData
import com.tanilink.cat.model.AppThemeOption
import com.tanilink.cat.model.ExamResult
import com.tanilink.cat.model.UserAvatar

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ProfileScreen(
    studentName: String,
    selectedGrade: Int,
    selectedAvatar: UserAvatar,
    themeOption: AppThemeOption,
    examHistory: List<ExamResult>,
    onUpdateProfile: (String, Int, UserAvatar) -> Unit,
    onSelectTheme: (AppThemeOption) -> Unit,
    onLogout: () -> Unit = {}
) {
    var nameInput by remember(studentName) { mutableStateOf(studentName) }
    var tempGrade by remember(selectedGrade) { mutableStateOf(selectedGrade) }
    var tempAvatar by remember(selectedAvatar) { mutableStateOf(selectedAvatar) }
    var isSavedShow by remember { mutableStateOf(false) }

    val totalExams = examHistory.size
    val avgScore = if (totalExams > 0) examHistory.map { it.score }.average().toInt() else 0
    val totalStars = examHistory.sumOf { res ->
        val stars: Int = when {
            res.score >= 80 -> 3
            res.score >= 60 -> 2
            else -> 1
        }
        stars
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Profil Pengguna SD", fontWeight = FontWeight.Bold) },
                actions = {
                    IconButton(onClick = onLogout) {
                        Icon(Icons.Default.Logout, contentDescription = "Keluar Akun", tint = Color(0xFFDC2626))
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
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding)
                .padding(horizontal = 16.dp),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            item { Spacer(modifier = Modifier.height(4.dp)) }

            // Avatar & Name Card Banner
            item {
                Card(
                    shape = RoundedCornerShape(24.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(
                        modifier = Modifier
                            .padding(20.dp)
                            .fillMaxWidth(),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        Surface(
                            shape = CircleShape,
                            color = tempAvatar.backgroundColor,
                            modifier = Modifier
                                .size(90.dp)
                                .shadow(4.dp, CircleShape)
                        ) {
                            Box(contentAlignment = Alignment.Center) {
                                Icon(
                                    imageVector = getAvatarIcon(tempAvatar.iconName),
                                    contentDescription = null,
                                    tint = Color(0xFF4F46E5),
                                    modifier = Modifier.size(48.dp)
                                )
                            }
                        }

                        Spacer(modifier = Modifier.height(12.dp))

                        Text(
                            text = tempAvatar.name,
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.primary
                        )

                        Spacer(modifier = Modifier.height(16.dp))

                        // Input Nama Siswa
                        OutlinedTextField(
                            value = nameInput,
                            onValueChange = { nameInput = it },
                            label = { Text("Nama Lengkap Siswa") },
                            leadingIcon = { Icon(Icons.Default.Person, contentDescription = null) },
                            singleLine = true,
                            shape = RoundedCornerShape(16.dp),
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }
            }

            // Segmented Theme Toggle Card (Sangat Rapi & Elegan)
            item {
                Card(
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.Palette, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
                            Spacer(modifier = Modifier.width(8.dp))
                            Column {
                                Text(
                                    text = "Tema Tampilan Aplikasi",
                                    fontSize = 15.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = MaterialTheme.colorScheme.onSurface
                                )
                                Text(
                                    text = "Sesuaikan kenyamanan mata kamu saat belajar",
                                    fontSize = 11.sp,
                                    color = MaterialTheme.colorScheme.onSurfaceVariant
                                )
                            }
                        }

                        Spacer(modifier = Modifier.height(14.dp))

                        // Segmented Control Pill Container
                        Surface(
                            shape = RoundedCornerShape(16.dp),
                            color = MaterialTheme.colorScheme.surfaceVariant,
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Row(
                                modifier = Modifier
                                    .padding(4.dp)
                                    .fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(4.dp)
                            ) {
                                SegmentedThemeOption(
                                    modifier = Modifier.weight(1f),
                                    title = "Terang",
                                    icon = Icons.Default.LightMode,
                                    isSelected = themeOption == AppThemeOption.LIGHT,
                                    onClick = { onSelectTheme(AppThemeOption.LIGHT) }
                                )

                                SegmentedThemeOption(
                                    modifier = Modifier.weight(1f),
                                    title = "Gelap",
                                    icon = Icons.Default.DarkMode,
                                    isSelected = themeOption == AppThemeOption.DARK,
                                    onClick = { onSelectTheme(AppThemeOption.DARK) }
                                )

                                SegmentedThemeOption(
                                    modifier = Modifier.weight(1f),
                                    title = "Sistem",
                                    icon = Icons.Default.SettingsSuggest,
                                    isSelected = themeOption == AppThemeOption.SYSTEM,
                                    onClick = { onSelectTheme(AppThemeOption.SYSTEM) }
                                )
                            }
                        }
                    }
                }
            }

            // Avatar Selector Grid
            item {
                Card(
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = "Pilih Karakter Avatar Favorit:",
                            fontSize = 15.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Spacer(modifier = Modifier.height(12.dp))

                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween
                        ) {
                            SampleData.avatars.forEach { av ->
                                val isSelected = tempAvatar.id == av.id
                                Surface(
                                    shape = CircleShape,
                                    color = av.backgroundColor,
                                    border = if (isSelected) androidx.compose.foundation.BorderStroke(3.dp, MaterialTheme.colorScheme.primary) else null,
                                    modifier = Modifier
                                        .size(54.dp)
                                        .clickable { tempAvatar = av }
                                ) {
                                    Box(contentAlignment = Alignment.Center) {
                                        Icon(
                                            imageVector = getAvatarIcon(av.iconName),
                                            contentDescription = av.name,
                                            tint = if (isSelected) MaterialTheme.colorScheme.primary else Color(0xFF64748B),
                                            modifier = Modifier.size(28.dp)
                                        )
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Grade Selector (Kelas 1 s/d 6 SD)
            item {
                Card(
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = "Tingkat Kelas SD Aktif:",
                            fontSize = 15.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Spacer(modifier = Modifier.height(12.dp))

                        val grades = listOf(1, 2, 3, 4, 5, 6)
                        Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(8.dp)
                            ) {
                                grades.take(3).forEach { g ->
                                    val isSelected = tempGrade == g
                                    FilterChip(
                                        selected = isSelected,
                                        onClick = { tempGrade = g },
                                        label = { Text("Kelas $g SD", fontWeight = FontWeight.Bold) },
                                        colors = FilterChipDefaults.filterChipColors(
                                            selectedContainerColor = Color(0xFFFFC107),
                                            selectedLabelColor = Color(0xFF1E293B)
                                        ),
                                        shape = RoundedCornerShape(12.dp),
                                        modifier = Modifier.weight(1f)
                                    )
                                }
                            }
                            Row(
                                modifier = Modifier.fillMaxWidth(),
                                horizontalArrangement = Arrangement.spacedBy(8.dp)
                            ) {
                                grades.drop(3).forEach { g ->
                                    val isSelected = tempGrade == g
                                    FilterChip(
                                        selected = isSelected,
                                        onClick = { tempGrade = g },
                                        label = { Text("Kelas $g SD", fontWeight = FontWeight.Bold) },
                                        colors = FilterChipDefaults.filterChipColors(
                                            selectedContainerColor = Color(0xFFFFC107),
                                            selectedLabelColor = Color(0xFF1E293B)
                                        ),
                                        shape = RoundedCornerShape(12.dp),
                                        modifier = Modifier.weight(1f)
                                    )
                                }
                            }
                        }
                    }
                }
            }

            // Stat Badges
            item {
                Card(
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = "Pencapaian Siswa",
                            fontSize = 15.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface
                        )
                        Spacer(modifier = Modifier.height(12.dp))

                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween
                        ) {
                            ProfileStatItem(title = "Total Ujian", value = "$totalExams", icon = Icons.Default.Quiz, color = Color(0xFF2563EB))
                            ProfileStatItem(title = "Rata-rata", value = "$avgScore", icon = Icons.Default.Equalizer, color = Color(0xFF16A34A))
                            ProfileStatItem(title = "Bintang", value = "$totalStars ⭐", icon = Icons.Default.Star, color = Color(0xFFD97706))
                        }
                    }
                }
            }

            // Save Button
            item {
                Button(
                    onClick = {
                        onUpdateProfile(nameInput, tempGrade, tempAvatar)
                        isSavedShow = true
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primary),
                    shape = RoundedCornerShape(16.dp),
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(52.dp)
                ) {
                    Icon(Icons.Default.Save, contentDescription = null, tint = Color.White)
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("Simpan Perubahan Profil", fontWeight = FontWeight.Bold, fontSize = 15.sp, color = Color.White)
                }
            }

            if (isSavedShow) {
                item {
                    Surface(
                        color = Color(0xFFDCFCE7),
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Row(
                            modifier = Modifier.padding(14.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Icon(Icons.Default.CheckCircle, contentDescription = null, tint = Color(0xFF166534))
                            Spacer(modifier = Modifier.width(10.dp))
                            Text(
                                text = "Profil siswa berhasil diperbarui!",
                                color = Color(0xFF166534),
                                fontWeight = FontWeight.Bold,
                                fontSize = 13.sp
                            )
                        }
                    }
                }
            }

            // Logout Button
            item {
                OutlinedButton(
                    onClick = onLogout,
                    colors = ButtonDefaults.outlinedButtonColors(contentColor = Color(0xFFDC2626)),
                    border = androidx.compose.foundation.BorderStroke(1.dp, Color(0xFFFCA5A5)),
                    shape = RoundedCornerShape(16.dp),
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(52.dp)
                ) {
                    Icon(Icons.Default.Logout, contentDescription = null, tint = Color(0xFFDC2626))
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("Keluar dari Akun (Logout)", fontWeight = FontWeight.Bold, fontSize = 15.sp)
                }
            }

            item { Spacer(modifier = Modifier.height(24.dp)) }
        }
    }
}

@Composable
fun SegmentedThemeOption(
    modifier: Modifier = Modifier,
    title: String,
    icon: ImageVector,
    isSelected: Boolean,
    onClick: () -> Unit
) {
    val bgColor by animateColorAsState(
        targetValue = if (isSelected) MaterialTheme.colorScheme.primary else Color.Transparent,
        label = "segmentedBg"
    )
    val contentColor by animateColorAsState(
        targetValue = if (isSelected) Color.White else MaterialTheme.colorScheme.onSurfaceVariant,
        label = "segmentedColor"
    )

    Surface(
        modifier = modifier
            .height(42.dp)
            .clip(RoundedCornerShape(12.dp))
            .clickable { onClick() },
        color = bgColor,
        shape = RoundedCornerShape(12.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxSize(),
            horizontalArrangement = Arrangement.Center,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(imageVector = icon, contentDescription = null, tint = contentColor, modifier = Modifier.size(16.dp))
            Spacer(modifier = Modifier.width(6.dp))
            Text(
                text = title,
                fontSize = 13.sp,
                fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Medium,
                color = contentColor
            )
        }
    }
}

@Composable
fun ProfileStatItem(title: String, value: String, icon: ImageVector, color: Color) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Surface(
            shape = CircleShape,
            color = color.copy(alpha = 0.1f),
            modifier = Modifier.size(44.dp)
        ) {
            Box(contentAlignment = Alignment.Center) {
                Icon(imageVector = icon, contentDescription = null, tint = color, modifier = Modifier.size(22.dp))
            }
        }
        Spacer(modifier = Modifier.height(6.dp))
        Text(text = value, fontSize = 15.sp, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.onSurface)
        Text(text = title, fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
    }
}

fun getAvatarIcon(iconName: String): ImageVector {
    return when (iconName) {
        "Pets" -> Icons.Default.Pets
        "EmojiEmotions" -> Icons.Default.EmojiEmotions
        "SmartToy" -> Icons.Default.SmartToy
        "RocketLaunch" -> Icons.Default.RocketLaunch
        else -> Icons.Default.SportsEsports
    }
}
