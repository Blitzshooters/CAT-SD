package com.tanilink.cat.ui.screens

import androidx.compose.animation.animateColorAsState
import androidx.compose.foundation.BorderStroke
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
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.tanilink.cat.data.CatApiClient
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
    customAvatarUrl: String = "",
    themeOption: AppThemeOption,
    examHistory: List<ExamResult>,
    isAdmin: Boolean = false,
    nomorInduk: String = "",
    onUpdateProfile: (String, Int, UserAvatar) -> Unit,
    onChangePassword: (String, String, (Boolean, String) -> Unit) -> Unit = { _, _, _ -> },
    onUploadPhoto: (android.graphics.Bitmap, (Boolean, String) -> Unit) -> Unit = { _, _ -> },
    onSelectTheme: (AppThemeOption) -> Unit,
    onLogout: () -> Unit = {}
) {
    val context = androidx.compose.ui.platform.LocalContext.current
    var nameInput by remember(studentName) { mutableStateOf(studentName) }
    var tempGrade by remember(selectedGrade) { mutableStateOf(selectedGrade) }
    var tempAvatar by remember(selectedAvatar) { mutableStateOf(selectedAvatar) }
    var customPhotoBitmap by remember { mutableStateOf<android.graphics.Bitmap?>(null) }
    var rawPhotoBitmap by remember { mutableStateOf<android.graphics.Bitmap?>(null) }
    var showCropDialog by remember { mutableStateOf(false) }
    var photoScale by remember { mutableStateOf(1.0f) }
    var photoOffsetX by remember { mutableStateOf(0f) }
    var photoOffsetY by remember { mutableStateOf(0f) }

    var isUploadingPhoto by remember { mutableStateOf(false) }
    var photoUploadMsg by remember { mutableStateOf<String?>(null) }
    var isSavedShow by remember { mutableStateOf(false) }

    LaunchedEffect(customAvatarUrl) {
        if (customAvatarUrl.isNotBlank()) {
            val bitmap = CatApiClient.loadBitmap(customAvatarUrl)
            if (bitmap != null) {
                customPhotoBitmap = bitmap
            }
        }
    }

    // Photo Picker Launcher
    val photoPickerLauncher = androidx.activity.compose.rememberLauncherForActivityResult(
        contract = androidx.activity.result.contract.ActivityResultContracts.GetContent()
    ) { uri: android.net.Uri? ->
        uri?.let { selectedUri ->
            try {
                val inputStream = context.contentResolver.openInputStream(selectedUri)
                val bitmap = android.graphics.BitmapFactory.decodeStream(inputStream)
                if (bitmap != null) {
                    rawPhotoBitmap = bitmap
                    photoScale = 1.0f
                    photoOffsetX = 0f
                    photoOffsetY = 0f
                    showCropDialog = true
                }
            } catch (e: Exception) {
                photoUploadMsg = "Gagal memuat gambar dari galeri."
            }
        }
    }

    // Password Change Dialog State
    var showChangePasswordDialog by remember { mutableStateOf(false) }
    var currentPassInput by remember { mutableStateOf("") }
    var newPassInput by remember { mutableStateOf("") }
    var confirmPassInput by remember { mutableStateOf("") }
    var showCurrentPass by remember { mutableStateOf(false) }
    var showNewPass by remember { mutableStateOf(false) }
    var showConfirmPass by remember { mutableStateOf(false) }
    var passErrorMsg by remember { mutableStateOf<String?>(null) }
    var passSuccessMsg by remember { mutableStateOf<String?>(null) }
    var isSubmittingPass by remember { mutableStateOf(false) }

    var pendingGradeChange by remember { mutableStateOf<Int?>(null) }
    var gradeCodeInput by remember { mutableStateOf("") }
    var gradeCodeErrorMsg by remember { mutableStateOf<String?>(null) }

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
                        Box(contentAlignment = Alignment.BottomEnd) {
                            Surface(
                                shape = CircleShape,
                                color = tempAvatar.backgroundColor,
                                modifier = Modifier
                                    .size(96.dp)
                                    .shadow(4.dp, CircleShape)
                            ) {
                                Box(contentAlignment = Alignment.Center) {
                                    if (customPhotoBitmap != null) {
                                        androidx.compose.foundation.Image(
                                            bitmap = customPhotoBitmap!!.asImageBitmap(),
                                            contentDescription = "Foto Profil Custom",
                                            contentScale = androidx.compose.ui.layout.ContentScale.Crop,
                                            modifier = Modifier.fillMaxSize().clip(CircleShape)
                                        )
                                    } else {
                                        Icon(
                                            imageVector = getAvatarIcon(tempAvatar.iconName),
                                            contentDescription = null,
                                            tint = Color(0xFF4F46E5),
                                            modifier = Modifier.size(48.dp)
                                        )
                                    }
                                }
                            }
                            SmallFloatingActionButton(
                                onClick = { photoPickerLauncher.launch("image/*") },
                                containerColor = MaterialTheme.colorScheme.primary,
                                contentColor = Color.White,
                                shape = CircleShape,
                                modifier = Modifier.size(32.dp)
                            ) {
                                Icon(Icons.Default.PhotoCamera, contentDescription = "Pilih Foto Profil", modifier = Modifier.size(16.dp))
                            }
                        }

                        Spacer(modifier = Modifier.height(10.dp))

                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Text(
                                text = if (customPhotoBitmap != null) "Foto kustom aktif" else tempAvatar.name,
                                fontSize = 12.sp,
                                fontWeight = FontWeight.Bold,
                                color = MaterialTheme.colorScheme.primary
                            )
                            Spacer(modifier = Modifier.width(6.dp))
                            TextButton(
                                onClick = { photoPickerLauncher.launch("image/*") },
                                contentPadding = PaddingValues(0.dp)
                            ) {
                                Icon(Icons.Default.UploadFile, contentDescription = null, modifier = Modifier.size(14.dp))
                                Spacer(modifier = Modifier.width(4.dp))
                                Text("Upload Foto GALERI", fontSize = 11.sp, fontWeight = FontWeight.Bold)
                            }
                        }

                        if (photoUploadMsg != null) {
                            Text(
                                text = photoUploadMsg!!,
                                fontSize = 11.sp,
                                color = if (isUploadingPhoto) MaterialTheme.colorScheme.primary else Color(0xFF166534),
                                fontWeight = FontWeight.Medium
                            )
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        // Input Nama Siswa & Nomor Induk Card
                        OutlinedTextField(
                            value = nameInput,
                            onValueChange = { nameInput = it },
                            label = { Text("Nama Lengkap Siswa") },
                            leadingIcon = { Icon(Icons.Default.Person, contentDescription = null) },
                            singleLine = true,
                            shape = RoundedCornerShape(16.dp),
                            modifier = Modifier.fillMaxWidth()
                        )

                        Spacer(modifier = Modifier.height(10.dp))

                        // Field Identitas Akun: Nomor Induk (NISN / NIK)
                        OutlinedTextField(
                            value = nomorInduk.ifBlank { "—" },
                            onValueChange = { },
                            readOnly = true,
                            enabled = false,
                            label = { Text("Nomor Induk Siswa (NISN / NIK)") },
                            leadingIcon = { Icon(Icons.Default.Badge, contentDescription = null) },
                            trailingIcon = {
                                if (nomorInduk.isNotBlank()) Surface(
                                    shape = RoundedCornerShape(8.dp),
                                    color = MaterialTheme.colorScheme.secondaryContainer
                                ) {
                                    Text(
                                        text = "Terverifikasi",
                                        fontSize = 10.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = MaterialTheme.colorScheme.onSecondaryContainer,
                                        modifier = Modifier.padding(horizontal = 6.dp, vertical = 2.dp)
                                    )
                                }
                            },
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

            // Grade Selector (Kelas 1 s/d 6 SD) - Bebas untuk Admin, Konfirmasi Kode jika Siswa
            item {
                Card(
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Text(
                                text = "Tingkat Kelas SD Aktif:",
                                fontSize = 15.sp,
                                fontWeight = FontWeight.Bold,
                                color = MaterialTheme.colorScheme.onSurface
                            )
                            if (isAdmin) {
                                Surface(
                                    shape = RoundedCornerShape(8.dp),
                                    color = Color(0xFFDCFCE7)
                                ) {
                                    Text(
                                        text = "Akses Admin (Bebas Ubah)",
                                        fontSize = 11.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = Color(0xFF166534),
                                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 3.dp)
                                    )
                                }
                            }
                        }
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
                                        onClick = {
                                            if (g != tempGrade) {
                                                if (isAdmin) {
                                                    tempGrade = g
                                                } else {
                                                    pendingGradeChange = g
                                                    gradeCodeInput = ""
                                                    gradeCodeErrorMsg = null
                                                }
                                            }
                                        },
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
                                        onClick = {
                                            if (g != tempGrade) {
                                                if (isAdmin) {
                                                    tempGrade = g
                                                } else {
                                                    pendingGradeChange = g
                                                    gradeCodeInput = ""
                                                    gradeCodeErrorMsg = null
                                                }
                                            }
                                        },
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

            // Keamanan & Ubah Password Card
            item {
                Card(
                    shape = RoundedCornerShape(20.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(16.dp)
                    ) {
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Surface(
                                shape = CircleShape,
                                color = MaterialTheme.colorScheme.primaryContainer,
                                modifier = Modifier.size(40.dp)
                            ) {
                                Box(contentAlignment = Alignment.Center) {
                                    Icon(Icons.Default.Lock, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.size(20.dp))
                                }
                            }
                            Spacer(modifier = Modifier.width(12.dp))
                            Column(modifier = Modifier.weight(1f)) {
                                Text(text = "Keamanan Akun", fontSize = 15.sp, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.onSurface)
                                Text(text = "Ganti password kata sandi akun kamu", fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
                            }
                        }

                        Spacer(modifier = Modifier.height(12.dp))

                        Button(
                            onClick = {
                                currentPassInput = ""
                                newPassInput = ""
                                confirmPassInput = ""
                                passErrorMsg = null
                                passSuccessMsg = null
                                showChangePasswordDialog = true
                            },
                            colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primaryContainer),
                            shape = RoundedCornerShape(12.dp),
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Icon(Icons.Default.LockReset, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.size(18.dp))
                            Spacer(modifier = Modifier.width(8.dp))
                            Text("Ubah Password Akun", fontSize = 13.sp, fontWeight = FontWeight.Bold, color = MaterialTheme.colorScheme.primary)
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

    // Modal Dialog Code Entry to change grade level
    if (pendingGradeChange != null) {
        AlertDialog(
            onDismissRequest = { pendingGradeChange = null },
            icon = {
                Surface(
                    shape = CircleShape,
                    color = Color(0xFFFEF3C7),
                    modifier = Modifier.size(48.dp)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Icon(
                            imageVector = Icons.Default.Lock,
                            contentDescription = null,
                            tint = Color(0xFFD97706),
                            modifier = Modifier.size(24.dp)
                        )
                    }
                }
            },
            title = {
                Text(
                    text = "Ganti Tingkat Kelas SD",
                    fontWeight = FontWeight.Bold,
                    fontSize = 18.sp
                )
            },
            text = {
                Column {
                    Text(
                        text = "Perubahan dari Kelas $tempGrade SD ke Kelas ${pendingGradeChange} SD memerlukan verifikasi kode khusus.",
                        fontSize = 13.sp,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Spacer(modifier = Modifier.height(4.dp))
                    Text(
                        text = "Masukkan kode khusus untuk mengonfirmasi perpindahan tingkat kelas.",
                        fontSize = 12.sp,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                    Spacer(modifier = Modifier.height(14.dp))
                    OutlinedTextField(
                        value = gradeCodeInput,
                        onValueChange = {
                            gradeCodeInput = it
                            gradeCodeErrorMsg = null
                        },
                        label = { Text("Kode Khusus") },
                        singleLine = true,
                        visualTransformation = PasswordVisualTransformation(),
                        isError = gradeCodeErrorMsg != null,
                        modifier = Modifier.fillMaxWidth()
                    )
                    if (gradeCodeErrorMsg != null) {
                        Spacer(modifier = Modifier.height(6.dp))
                        Text(
                            text = gradeCodeErrorMsg!!,
                            color = Color(0xFFDC2626),
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold
                        )
                    }
                }
            },
            confirmButton = {
                Button(
                    onClick = {
                        if (gradeCodeInput.trim().equals("unpkediri", ignoreCase = true)) {
                            val targetGrade = pendingGradeChange!!
                            tempGrade = targetGrade
                            onUpdateProfile(nameInput, targetGrade, tempAvatar)
                            pendingGradeChange = null
                            isSavedShow = true
                        } else {
                            gradeCodeErrorMsg = "Kode khusus salah! Perubahan kelas dibatalkan."
                        }
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF4F46E5)),
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Text("Konfirmasi & Ubah", fontWeight = FontWeight.Bold)
                }
            },
            dismissButton = {
                TextButton(onClick = { pendingGradeChange = null }) {
                    Text("Batal")
                }
            }
        )
    }

    // Modal Dialog Ubah Password
    if (showChangePasswordDialog) {
        AlertDialog(
            onDismissRequest = { if (!isSubmittingPass) showChangePasswordDialog = false },
            icon = {
                Surface(
                    shape = CircleShape,
                    color = MaterialTheme.colorScheme.primaryContainer,
                    modifier = Modifier.size(48.dp)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Icon(Icons.Default.LockReset, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.size(24.dp))
                    }
                }
            },
            title = {
                Text(text = "Ubah Password Akun", fontWeight = FontWeight.Bold, fontSize = 18.sp)
            },
            text = {
                Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                    OutlinedTextField(
                        value = currentPassInput,
                        onValueChange = { currentPassInput = it; passErrorMsg = null },
                        label = { Text("Password Lama") },
                        singleLine = true,
                        visualTransformation = if (showCurrentPass) androidx.compose.ui.text.input.VisualTransformation.None else PasswordVisualTransformation(),
                        trailingIcon = {
                            IconButton(onClick = { showCurrentPass = !showCurrentPass }) {
                                Icon(
                                    imageVector = if (showCurrentPass) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                    contentDescription = "Lihat Password"
                                )
                            }
                        },
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    )
                    OutlinedTextField(
                        value = newPassInput,
                        onValueChange = { newPassInput = it; passErrorMsg = null },
                        label = { Text("Password Baru") },
                        singleLine = true,
                        visualTransformation = if (showNewPass) androidx.compose.ui.text.input.VisualTransformation.None else PasswordVisualTransformation(),
                        trailingIcon = {
                            IconButton(onClick = { showNewPass = !showNewPass }) {
                                Icon(
                                    imageVector = if (showNewPass) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                    contentDescription = "Lihat Password"
                                )
                            }
                        },
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    )
                    OutlinedTextField(
                        value = confirmPassInput,
                        onValueChange = { confirmPassInput = it; passErrorMsg = null },
                        label = { Text("Konfirmasi Password Baru") },
                        singleLine = true,
                        visualTransformation = if (showConfirmPass) androidx.compose.ui.text.input.VisualTransformation.None else PasswordVisualTransformation(),
                        trailingIcon = {
                            IconButton(onClick = { showConfirmPass = !showConfirmPass }) {
                                Icon(
                                    imageVector = if (showConfirmPass) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                    contentDescription = "Lihat Password"
                                )
                            }
                        },
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    )

                    if (passErrorMsg != null) {
                        Text(text = passErrorMsg!!, color = Color(0xFFDC2626), fontSize = 12.sp, fontWeight = FontWeight.Bold)
                    }
                    if (passSuccessMsg != null) {
                        Text(text = passSuccessMsg!!, color = Color(0xFF166534), fontSize = 12.sp, fontWeight = FontWeight.Bold)
                    }
                }
            },
            confirmButton = {
                Button(
                    onClick = {
                        if (currentPassInput.isEmpty() || newPassInput.isEmpty()) {
                            passErrorMsg = "Password lama dan password baru harus diisi."
                            return@Button
                        }
                        if (newPassInput != confirmPassInput) {
                            passErrorMsg = "Konfirmasi password tidak cocok."
                            return@Button
                        }
                        if (newPassInput.length < 4) {
                            passErrorMsg = "Password minimal 4 karakter."
                            return@Button
                        }
                        isSubmittingPass = true
                        passErrorMsg = null
                        onChangePassword(currentPassInput, newPassInput) { success, msg ->
                            isSubmittingPass = false
                            if (success) {
                                passSuccessMsg = msg
                                showChangePasswordDialog = false
                            } else {
                                passErrorMsg = msg
                            }
                        }
                    },
                    enabled = !isSubmittingPass,
                    shape = RoundedCornerShape(12.dp)
                ) {
                    if (isSubmittingPass) {
                        CircularProgressIndicator(modifier = Modifier.size(18.dp), color = Color.White, strokeWidth = 2.dp)
                    } else {
                        Text("Simpan Password", fontWeight = FontWeight.Bold)
                    }
                }
            },
            dismissButton = {
                TextButton(onClick = { showChangePasswordDialog = false }, enabled = !isSubmittingPass) {
                    Text("Batal")
                }
            }
        )
    }

    // Modal Dialog Pengatur Posisi Foto (Crop & Position Adjuster)
    if (showCropDialog && rawPhotoBitmap != null) {
        AlertDialog(
            onDismissRequest = { if (!isUploadingPhoto) showCropDialog = false },
            icon = {
                Icon(Icons.Default.Crop, contentDescription = null, tint = MaterialTheme.colorScheme.primary, modifier = Modifier.size(32.dp))
            },
            title = {
                Text("Atur Posisi & Ukuran Foto", fontWeight = FontWeight.Bold, fontSize = 18.sp)
            },
            text = {
                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Text(
                        text = "Geser dan perbesar foto agar pas di dalam lingkaran profil.",
                        fontSize = 12.sp,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )

                    // Preview Lingkaran Foto dengan Offset & Zoom
                    Surface(
                        shape = CircleShape,
                        color = Color.Black,
                        border = BorderStroke(2.dp, MaterialTheme.colorScheme.primary),
                        modifier = Modifier.size(140.dp)
                    ) {
                        Box(
                            contentAlignment = Alignment.Center,
                            modifier = Modifier.fillMaxSize().clip(CircleShape)
                        ) {
                            androidx.compose.foundation.Image(
                                bitmap = rawPhotoBitmap!!.asImageBitmap(),
                                contentDescription = "Preview Crop Foto",
                                contentScale = androidx.compose.ui.layout.ContentScale.Fit,
                                modifier = Modifier
                                    .fillMaxSize()
                                    .graphicsLayer(
                                        scaleX = photoScale,
                                        scaleY = photoScale,
                                        translationX = photoOffsetX,
                                        translationY = photoOffsetY
                                    )
                            )
                        }
                    }

                    // Slider Zoom Scale
                    Column(modifier = Modifier.fillMaxWidth()) {
                        Text(text = "Zoom (Skala): ${(photoScale * 100).toInt()}%", fontSize = 12.sp, fontWeight = FontWeight.Bold)
                        Slider(
                            value = photoScale,
                            onValueChange = { photoScale = it },
                            valueRange = 0.5f..3.0f,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }

                    // Slider Offset Vertikal Y
                    Column(modifier = Modifier.fillMaxWidth()) {
                        Text(text = "Posisi Vertikal (Atas/Bawah)", fontSize = 12.sp, fontWeight = FontWeight.Bold)
                        Slider(
                            value = photoOffsetY,
                            onValueChange = { photoOffsetY = it },
                            valueRange = -150f..150f,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }

                    // Slider Offset Horizontal X
                    Column(modifier = Modifier.fillMaxWidth()) {
                        Text(text = "Posisi Horizontal (Kiri/Kanan)", fontSize = 12.sp, fontWeight = FontWeight.Bold)
                        Slider(
                            value = photoOffsetX,
                            onValueChange = { photoOffsetX = it },
                            valueRange = -150f..150f,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }
            },
            confirmButton = {
                Button(
                    onClick = {
                        showCropDialog = false
                        isUploadingPhoto = true
                        photoUploadMsg = "Mengunggah foto..."
                        
                        // Render cropped bitmap onto result canvas
                        try {
                            val resultBitmap = android.graphics.Bitmap.createBitmap(300, 300, android.graphics.Bitmap.Config.ARGB_8888)
                            val canvas = android.graphics.Canvas(resultBitmap)
                            val paint = android.graphics.Paint(android.graphics.Paint.ANTI_ALIAS_FLAG)
                            
                            val matrix = android.graphics.Matrix()
                            val srcW = rawPhotoBitmap!!.width.toFloat()
                            val srcH = rawPhotoBitmap!!.height.toFloat()
                            val scale = (300f / Math.max(srcW, srcH)) * photoScale
                            matrix.postScale(scale, scale)
                            matrix.postTranslate((300f - srcW * scale) / 2f + photoOffsetX, (300f - srcH * scale) / 2f + photoOffsetY)
                            
                            canvas.drawBitmap(rawPhotoBitmap!!, matrix, paint)
                            customPhotoBitmap = resultBitmap

                            onUploadPhoto(resultBitmap) { success, msg ->
                                isUploadingPhoto = false
                                photoUploadMsg = msg
                            }
                        } catch (e: Exception) {
                            customPhotoBitmap = rawPhotoBitmap
                            onUploadPhoto(rawPhotoBitmap!!) { success, msg ->
                                isUploadingPhoto = false
                                photoUploadMsg = msg
                            }
                        }
                    },
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Text("Gunakan Foto Ini", fontWeight = FontWeight.Bold)
                }
            },
            dismissButton = {
                TextButton(onClick = { showCropDialog = false }) {
                    Text("Batal")
                }
            }
        )
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
