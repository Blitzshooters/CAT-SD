package com.tanilink.cat.ui.screens

import androidx.compose.animation.animateColorAsState
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.itemsIndexed
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.foundation.gestures.detectDragGestures
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.input.pointer.pointerInput
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.IntOffset
import kotlin.math.roundToInt
import com.tanilink.cat.model.ExamSubject
import com.tanilink.cat.model.ProctorPosition
import com.tanilink.cat.model.Question
import com.tanilink.cat.proctoring.CameraProctoringView
import com.tanilink.cat.proctoring.FaceStatus
import com.tanilink.cat.proctoring.ProctoringViolation
import com.tanilink.cat.ui.components.CatImage
import java.util.Locale

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ExamScreen(
    subject: ExamSubject,
    questions: List<Question>,
    currentIndex: Int,
    userAnswers: Map<Int, Int>,
    flaggedQuestions: Set<Int>,
    remainingSeconds: Long,
    faceStatus: FaceStatus,
    proctoringWarningText: String,
    proctoringLogs: List<ProctoringViolation>,
    violationCount: Int,
    isAdmin: Boolean = false,
    isExamLocked: Boolean = false,
    lockReason: String = "",
    remedyError: String? = null,
    isVerifyingRemedy: Boolean = false,
    onVerifyRemedyCode: (String) -> Unit = {},
    onFaceStatusChanged: (FaceStatus, String) -> Unit,
    onSelectAnswer: (Int, Int) -> Unit,
    onToggleFlag: (Int) -> Unit,
    onGoToQuestion: (Int) -> Unit,
    onNextQuestion: () -> Unit,
    onPreviousQuestion: () -> Unit,
    onSubmitExam: () -> Unit,
    onCancelExam: () -> Unit
) {
    var showGridSheet by remember { mutableStateOf(false) }
    var showSubmitDialog by remember { mutableStateOf(false) }
    var showProctoringLogSheet by remember { mutableStateOf(false) }
    var proctorPosition by remember { mutableStateOf(ProctorPosition.TOP_RIGHT) }
    var dragOffset by remember { mutableStateOf(Offset.Zero) }

    val currentQuestion = questions.getOrNull(currentIndex)
    val selectedOptionIndex = userAnswers[currentIndex]
    val isFlagged = flaggedQuestions.contains(currentIndex)

    val minutes = remainingSeconds / 60
    val seconds = remainingSeconds % 60
    val formattedTime = String.format(Locale.getDefault(), "%02d:%02d", minutes, seconds)

    val timerColor = when {
        remainingSeconds < 60 -> Color(0xFFDC2626)
        remainingSeconds < 180 -> Color(0xFFD97706)
        else -> Color(0xFF16A34A)
    }

    val faceStatusColor = when (faceStatus) {
        FaceStatus.OK -> Color(0xFF16A34A)
        FaceStatus.LOOKING_AWAY -> Color(0xFFD97706)
        FaceStatus.NO_FACE -> Color(0xFFDC2626)
        FaceStatus.MULTIPLE_FACES -> Color(0xFFDC2626)
    }

    Scaffold(
        topBar = {
            Surface(
                shadowElevation = 4.dp,
                color = MaterialTheme.colorScheme.surface
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 10.dp)
                ) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        verticalAlignment = Alignment.CenterVertically,
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        IconButton(onClick = onCancelExam) {
                            Icon(Icons.Default.Close, contentDescription = "Batal Ujian", tint = MaterialTheme.colorScheme.onSurfaceVariant)
                        }

                        Column(horizontalAlignment = Alignment.CenterHorizontally) {
                            Text(
                                text = subject.title,
                                fontWeight = FontWeight.Bold,
                                fontSize = 16.sp,
                                color = MaterialTheme.colorScheme.onSurface
                            )
                            Text(
                                text = "Soal ${currentIndex + 1} dari ${questions.size}",
                                fontSize = 12.sp,
                                color = MaterialTheme.colorScheme.onSurfaceVariant
                            )
                        }

                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Surface(
                                shape = RoundedCornerShape(20.dp),
                                color = timerColor.copy(alpha = 0.1f),
                                border = androidx.compose.foundation.BorderStroke(1.dp, timerColor.copy(alpha = 0.4f))
                            ) {
                                Row(
                                    modifier = Modifier.padding(horizontal = 10.dp, vertical = 6.dp),
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    Icon(
                                        imageVector = Icons.Default.Timer,
                                        contentDescription = null,
                                        tint = timerColor,
                                        modifier = Modifier.size(16.dp)
                                    )
                                    Spacer(modifier = Modifier.width(4.dp))
                                    Text(
                                        text = formattedTime,
                                        fontSize = 13.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = timerColor
                                    )
                                }
                            }
                        }
                    }

                    Spacer(modifier = Modifier.height(8.dp))

                    // AI Proctoring Live Banner Status Bar
                    Surface(
                        shape = RoundedCornerShape(12.dp),
                        color = faceStatusColor.copy(alpha = 0.12f),
                        border = androidx.compose.foundation.BorderStroke(1.dp, faceStatusColor.copy(alpha = 0.3f)),
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable { showProctoringLogSheet = true }
                    ) {
                        Row(
                            modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp),
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.SpaceBetween
                        ) {
                            Row(verticalAlignment = Alignment.CenterVertically, modifier = Modifier.weight(1f)) {
                                Icon(
                                    imageVector = if (faceStatus == FaceStatus.OK) Icons.Default.Visibility else Icons.Default.Warning,
                                    contentDescription = null,
                                    tint = faceStatusColor,
                                    modifier = Modifier.size(18.dp)
                                )
                                Spacer(modifier = Modifier.width(8.dp))
                                Text(
                                    text = "AI Proctor: $proctoringWarningText",
                                    fontSize = 12.sp,
                                    fontWeight = FontWeight.SemiBold,
                                    color = faceStatusColor
                                )
                            }

                            Row(verticalAlignment = Alignment.CenterVertically) {
                                if (isAdmin) {
                                    Surface(
                                        shape = RoundedCornerShape(8.dp),
                                        color = Color(0xFF7C3AED)
                                    ) {
                                        Text(
                                            text = "👑 ADMIN",
                                            fontSize = 11.sp,
                                            fontWeight = FontWeight.Bold,
                                            color = Color.White,
                                            modifier = Modifier.padding(horizontal = 6.dp, vertical = 2.dp)
                                        )
                                    }
                                    Spacer(modifier = Modifier.width(6.dp))
                                }

                                Surface(
                                    shape = RoundedCornerShape(8.dp),
                                    color = if (violationCount > 0) Color(0xFFFEE2E2) else Color(0xFFDCFCE7)
                                ) {
                                    Text(
                                        text = "Pelanggaran: $violationCount/${subject.maxViolations}",
                                        fontSize = 11.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = if (violationCount > 0) Color(0xFFDC2626) else Color(0xFF166534),
                                        modifier = Modifier.padding(horizontal = 8.dp, vertical = 2.dp)
                                    )
                                }
                            }
                        }
                    }

                    Spacer(modifier = Modifier.height(8.dp))

                    val progress = if (questions.isNotEmpty()) (currentIndex + 1).toFloat() / questions.size else 0f
                    LinearProgressIndicator(
                        progress = { progress },
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(6.dp)
                            .clip(RoundedCornerShape(3.dp)),
                        color = subject.primaryColor,
                        trackColor = MaterialTheme.colorScheme.surfaceVariant
                    )
                }
            }
        },
        bottomBar = {
            Surface(
                shadowElevation = 8.dp,
                color = MaterialTheme.colorScheme.surface
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 16.dp, vertical = 12.dp),
                    horizontalArrangement = Arrangement.spacedBy(10.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Surface(
                        shape = RoundedCornerShape(12.dp),
                        color = MaterialTheme.colorScheme.surfaceVariant,
                        modifier = Modifier.clickable { showGridSheet = true }
                    ) {
                        Row(
                            modifier = Modifier.padding(horizontal = 12.dp, vertical = 10.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Icon(
                                imageVector = Icons.Default.GridView,
                                contentDescription = "Daftar Soal",
                                tint = MaterialTheme.colorScheme.onSurface,
                                modifier = Modifier.size(18.dp)
                            )
                            Spacer(modifier = Modifier.width(6.dp))
                            Text(
                                text = "Soal",
                                fontWeight = FontWeight.SemiBold,
                                fontSize = 13.sp,
                                color = MaterialTheme.colorScheme.onSurface
                            )
                        }
                    }

                    OutlinedButton(
                        onClick = { onToggleFlag(currentIndex) },
                        modifier = Modifier.weight(1f),
                        colors = ButtonDefaults.outlinedButtonColors(
                            containerColor = if (isFlagged) Color(0xFFFEF08A) else Color.Transparent,
                            contentColor = if (isFlagged) Color(0xFF854D0E) else MaterialTheme.colorScheme.onSurfaceVariant
                        ),
                        border = androidx.compose.foundation.BorderStroke(
                            1.dp,
                            if (isFlagged) Color(0xFFEAB308) else MaterialTheme.colorScheme.outline
                        ),
                        shape = RoundedCornerShape(12.dp),
                        contentPadding = PaddingValues(horizontal = 8.dp, vertical = 10.dp)
                    ) {
                        Icon(
                            imageVector = if (isFlagged) Icons.Default.Bookmark else Icons.Default.BookmarkBorder,
                            contentDescription = null,
                            modifier = Modifier.size(18.dp)
                        )
                        Spacer(modifier = Modifier.width(6.dp))
                        Text(
                            text = if (isFlagged) "Ragu-Ragu" else "Tanda Ragu",
                            fontWeight = FontWeight.SemiBold,
                            fontSize = 13.sp
                        )
                    }

                    if (currentIndex == questions.size - 1) {
                        Button(
                            onClick = { showSubmitDialog = true },
                            colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF16A34A)),
                            shape = RoundedCornerShape(12.dp)
                        ) {
                            Text("Selesai & Kirim", fontWeight = FontWeight.Bold, color = Color.White)
                        }
                    } else {
                        Button(
                            onClick = onNextQuestion,
                            colors = ButtonDefaults.buttonColors(containerColor = subject.primaryColor),
                            shape = RoundedCornerShape(12.dp)
                        ) {
                            Text("Berikutnya", fontWeight = FontWeight.Bold, color = Color.White)
                            Spacer(modifier = Modifier.width(4.dp))
                            Icon(Icons.Default.ArrowForward, contentDescription = null, tint = Color.White, modifier = Modifier.size(16.dp))
                        }
                    }
                }
            }
        },
        containerColor = MaterialTheme.colorScheme.background
    ) { innerPadding ->
        if (currentQuestion != null) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(innerPadding)
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                        .padding(16.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Button(
                            onClick = onPreviousQuestion,
                            enabled = currentIndex > 0,
                            colors = ButtonDefaults.buttonColors(
                                containerColor = MaterialTheme.colorScheme.surfaceVariant,
                                contentColor = MaterialTheme.colorScheme.onSurface
                            ),
                            shape = RoundedCornerShape(10.dp),
                            contentPadding = PaddingValues(horizontal = 12.dp, vertical = 6.dp)
                        ) {
                            Icon(Icons.Default.ArrowBack, contentDescription = null, modifier = Modifier.size(16.dp))
                            Spacer(modifier = Modifier.width(4.dp))
                            Text("Sebelumnya", fontSize = 12.sp, fontWeight = FontWeight.SemiBold)
                        }

                        if (isFlagged) {
                            Surface(
                                color = Color(0xFFFEF08A),
                                shape = RoundedCornerShape(8.dp)
                            ) {
                                Row(
                                    modifier = Modifier.padding(horizontal = 10.dp, vertical = 4.dp),
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    Icon(Icons.Default.Bookmark, contentDescription = null, tint = Color(0xFF854D0E), modifier = Modifier.size(14.dp))
                                    Spacer(modifier = Modifier.width(4.dp))
                                    Text("Ditandai Ragu", fontSize = 11.sp, fontWeight = FontWeight.Bold, color = Color(0xFF854D0E))
                                }
                            }
                        }
                    }

                    if (faceStatus == FaceStatus.NO_FACE) {
                        Card(
                            shape = RoundedCornerShape(20.dp),
                            colors = CardDefaults.cardColors(containerColor = Color(0xFFFEF2F2)),
                            border = androidx.compose.foundation.BorderStroke(2.dp, Color(0xFFEF4444)),
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Column(
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .padding(24.dp),
                                horizontalAlignment = Alignment.CenterHorizontally
                            ) {
                                Surface(
                                    shape = CircleShape,
                                    color = Color(0xFFDC2626),
                                    modifier = Modifier.size(60.dp)
                                ) {
                                    Box(contentAlignment = Alignment.Center) {
                                        Icon(
                                            imageVector = Icons.Default.VisibilityOff,
                                            contentDescription = null,
                                            tint = Color.White,
                                            modifier = Modifier.size(32.dp)
                                        )
                                    }
                                }

                                Spacer(modifier = Modifier.height(16.dp))

                                Text(
                                    text = "SOAL DISEMBUNYIKAN",
                                    fontSize = 17.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = Color(0xFF991B1B)
                                )

                                Spacer(modifier = Modifier.height(6.dp))

                                Text(
                                    text = "Wajah Anda tidak terdeteksi oleh kamera AI Proctoring!\nHarap posisikan wajah Anda tegak di depan kamera agar soal dan pilihan jawaban dapat terlihat kembali.",
                                    fontSize = 13.sp,
                                    color = Color(0xFF7F1D1D),
                                    textAlign = androidx.compose.ui.text.style.TextAlign.Center,
                                    lineHeight = 20.sp
                                )

                                Spacer(modifier = Modifier.height(14.dp))

                                Surface(
                                    shape = RoundedCornerShape(10.dp),
                                    color = Color(0xFFFEE2E2)
                                ) {
                                    Text(
                                        text = "⚠️ Pelanggaran dihitung jika wajah hilang selama 3 detik!",
                                        fontSize = 11.sp,
                                        fontWeight = FontWeight.Bold,
                                        color = Color(0xFFB91C1C),
                                        modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp)
                                    )
                                }
                            }
                        }
                    } else {
                        Card(
                            shape = RoundedCornerShape(20.dp),
                            colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                            elevation = CardDefaults.cardElevation(defaultElevation = 2.dp),
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Column(modifier = Modifier.padding(20.dp)) {
                                Text(
                                    text = "Pertanyaan ${currentIndex + 1}",
                                    fontSize = 13.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = subject.primaryColor
                                )
                                Spacer(modifier = Modifier.height(10.dp))
                                Text(
                                    text = currentQuestion.prompt,
                                    fontSize = 18.sp,
                                    fontWeight = FontWeight.SemiBold,
                                    color = MaterialTheme.colorScheme.onSurface,
                                    lineHeight = 26.sp
                                )

                                if (!currentQuestion.imageUrl.isNullOrBlank()) {
                                    Spacer(modifier = Modifier.height(12.dp))
                                    CatImage(
                                        imageUrl = currentQuestion.imageUrl,
                                        contentDescription = "Gambar Soal",
                                        modifier = Modifier.fillMaxWidth()
                                    )
                                }
                            }
                        }

                        Text(
                            text = "Pilih Salah Satu Jawaban:",
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurfaceVariant
                        )

                        val labels = listOf("A", "B", "C", "D")
                        currentQuestion.options.forEachIndexed { optIdx, optionText ->
                            val isSelected = selectedOptionIndex == optIdx

                            val cardBgColor by animateColorAsState(
                                targetValue = if (isSelected) subject.secondaryColor else MaterialTheme.colorScheme.surface,
                                label = "cardBg"
                            )
                            val borderColor by animateColorAsState(
                                targetValue = if (isSelected) subject.primaryColor else MaterialTheme.colorScheme.outline,
                                label = "borderColor"
                            )

                            Card(
                                shape = RoundedCornerShape(16.dp),
                                colors = CardDefaults.cardColors(containerColor = cardBgColor),
                                border = androidx.compose.foundation.BorderStroke(if (isSelected) 2.dp else 1.dp, borderColor),
                                elevation = CardDefaults.cardElevation(defaultElevation = if (isSelected) 3.dp else 1.dp),
                                modifier = Modifier
                                    .fillMaxWidth()
                                    .clickable { onSelectAnswer(currentIndex, optIdx) }
                            ) {
                                Row(
                                    modifier = Modifier
                                        .padding(16.dp)
                                        .fillMaxWidth(),
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    Surface(
                                        shape = CircleShape,
                                        color = if (isSelected) subject.primaryColor else MaterialTheme.colorScheme.surfaceVariant,
                                        modifier = Modifier.size(36.dp)
                                    ) {
                                        Box(contentAlignment = Alignment.Center) {
                                            Text(
                                                text = labels.getOrElse(optIdx) { "" },
                                                fontWeight = FontWeight.Bold,
                                                fontSize = 15.sp,
                                                color = if (isSelected) Color.White else MaterialTheme.colorScheme.onSurface
                                            )
                                        }
                                    }

                                    Spacer(modifier = Modifier.width(14.dp))

                                    Column(modifier = Modifier.weight(1f)) {
                                        Text(
                                            text = optionText,
                                            fontSize = 15.sp,
                                            fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                                            color = MaterialTheme.colorScheme.onSurface
                                        )

                                        val optImg = currentQuestion.optionImages?.getOrNull(optIdx)
                                        if (!optImg.isNullOrBlank()) {
                                            Spacer(modifier = Modifier.height(8.dp))
                                            CatImage(
                                                imageUrl = optImg,
                                                contentDescription = "Gambar Opsi ${labels.getOrElse(optIdx) { "" }}",
                                                maxHeight = 110.dp,
                                                modifier = Modifier.fillMaxWidth()
                                            )
                                        }
                                    }

                                    if (isSelected) {
                                        Icon(
                                            imageVector = Icons.Default.CheckCircle,
                                            contentDescription = null,
                                            tint = subject.primaryColor,
                                            modifier = Modifier.size(24.dp)
                                        )
                                    }
                                }
                            }
                        }
                    }

                    Spacer(modifier = Modifier.height(16.dp))
                }

                // AI Front Camera Preview Overlay Thumbnail (Repositionable to 4 corners)
                val cornerAlignment = when (proctorPosition) {
                    ProctorPosition.TOP_RIGHT -> Alignment.TopEnd
                    ProctorPosition.TOP_LEFT -> Alignment.TopStart
                    ProctorPosition.BOTTOM_LEFT -> Alignment.BottomStart
                    ProctorPosition.BOTTOM_RIGHT -> Alignment.BottomEnd
                }

                val cornerPadding = when (proctorPosition) {
                    ProctorPosition.TOP_RIGHT -> PaddingValues(top = 16.dp, end = 16.dp)
                    ProctorPosition.TOP_LEFT -> PaddingValues(top = 16.dp, start = 16.dp)
                    ProctorPosition.BOTTOM_LEFT -> PaddingValues(bottom = 80.dp, start = 16.dp)
                    ProctorPosition.BOTTOM_RIGHT -> PaddingValues(bottom = 80.dp, end = 16.dp)
                }

                Box(
                    modifier = Modifier
                        .align(cornerAlignment)
                        .padding(cornerPadding)
                        .offset { IntOffset(dragOffset.x.roundToInt(), dragOffset.y.roundToInt()) }
                        .pointerInput(Unit) {
                            detectDragGestures(
                                onDrag = { change, dragAmount ->
                                    change.consume()
                                    dragOffset += dragAmount
                                },
                                onDragEnd = {
                                    val isLeft = dragOffset.x < -30
                                    val isBottom = dragOffset.y > 40
                                    proctorPosition = when {
                                        !isLeft && !isBottom -> ProctorPosition.TOP_RIGHT
                                        isLeft && !isBottom -> ProctorPosition.TOP_LEFT
                                        isLeft && isBottom -> ProctorPosition.BOTTOM_LEFT
                                        else -> ProctorPosition.BOTTOM_RIGHT
                                    }
                                    dragOffset = Offset.Zero
                                }
                            )
                        }
                ) {
                    CameraProctoringView(
                        statusColor = faceStatusColor,
                        onFaceStatusChanged = onFaceStatusChanged
                    )

                    // Quick Corner Switch Button (Top-Right -> Top-Left -> Bottom-Left -> Bottom-Right)
                    Surface(
                        shape = CircleShape,
                        color = Color(0xDD0F172A),
                        modifier = Modifier
                            .align(Alignment.TopEnd)
                            .padding(2.dp)
                            .size(20.dp)
                            .clickable {
                                proctorPosition = when (proctorPosition) {
                                    ProctorPosition.TOP_RIGHT -> ProctorPosition.TOP_LEFT
                                    ProctorPosition.TOP_LEFT -> ProctorPosition.BOTTOM_LEFT
                                    ProctorPosition.BOTTOM_LEFT -> ProctorPosition.BOTTOM_RIGHT
                                    ProctorPosition.BOTTOM_RIGHT -> ProctorPosition.TOP_RIGHT
                                }
                                dragOffset = Offset.Zero
                            }
                    ) {
                        Icon(
                            imageVector = Icons.Default.OpenWith,
                            contentDescription = "Pindah Posisi Kamera",
                            tint = Color.White,
                            modifier = Modifier.padding(3.dp)
                        )
                    }
                }
            }
        }
    }

    if (showGridSheet) {
        ModalBottomSheet(
            onDismissRequest = { showGridSheet = false },
            containerColor = MaterialTheme.colorScheme.surface
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(20.dp)
            ) {
                Text(
                    text = "Navigasi 20 Nomor Soal",
                    fontSize = 18.sp,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )
                Spacer(modifier = Modifier.height(12.dp))

                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    LegendItem(color = Color(0xFF16A34A), label = "Sudah Dijawab")
                    LegendItem(color = Color(0xFFEAB308), label = "Ragu-Ragu")
                    LegendItem(color = MaterialTheme.colorScheme.surfaceVariant, label = "Belum Dijawab")
                }

                Spacer(modifier = Modifier.height(16.dp))
                HorizontalDivider()
                Spacer(modifier = Modifier.height(16.dp))

                LazyVerticalGrid(
                    columns = GridCells.Fixed(5),
                    horizontalArrangement = Arrangement.spacedBy(10.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp),
                    modifier = Modifier.height(280.dp)
                ) {
                    itemsIndexed(questions) { idx, _ ->
                        val isAns = userAnswers.containsKey(idx)
                        val isFlag = flaggedQuestions.contains(idx)
                        val isCurr = idx == currentIndex

                        val itemBg = when {
                            isFlag -> Color(0xFFFEF08A)
                            isAns -> Color(0xFFDCFCE7)
                            else -> MaterialTheme.colorScheme.surfaceVariant
                        }

                        val itemTextColor = when {
                            isFlag -> Color(0xFF854D0E)
                            isAns -> Color(0xFF166534)
                            else -> MaterialTheme.colorScheme.onSurface
                        }

                        Box(
                            contentAlignment = Alignment.Center,
                            modifier = Modifier
                                .aspectRatio(1f)
                                .clip(RoundedCornerShape(12.dp))
                                .background(itemBg)
                                .border(
                                    width = if (isCurr) 2.dp else 1.dp,
                                    color = if (isCurr) subject.primaryColor else Color.Transparent,
                                    shape = RoundedCornerShape(12.dp)
                                )
                                .clickable {
                                    onGoToQuestion(idx)
                                    showGridSheet = false
                                }
                        ) {
                            Text(
                                text = "${idx + 1}",
                                fontWeight = FontWeight.Bold,
                                fontSize = 16.sp,
                                color = itemTextColor
                            )
                        }
                    }
                }

                Spacer(modifier = Modifier.height(24.dp))
            }
        }
    }

    if (showProctoringLogSheet) {
        ModalBottomSheet(
            onDismissRequest = { showProctoringLogSheet = false },
            containerColor = MaterialTheme.colorScheme.surface
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(20.dp)
            ) {
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Icon(Icons.Default.Security, contentDescription = null, tint = Color(0xFF4F46E5))
                    Spacer(modifier = Modifier.width(8.dp))
                    Text(
                        text = "Log Catatan AI Proctoring",
                        fontSize = 18.sp,
                        fontWeight = FontWeight.Bold,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                }

                Spacer(modifier = Modifier.height(12.dp))

                if (proctoringLogs.isEmpty()) {
                    Text(
                        text = "Belum ada catatan pelanggaran. Tetap pertahankan fokus kamu!",
                        fontSize = 13.sp,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                } else {
                    Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                        proctoringLogs.forEach { log ->
                            Card(
                                colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant),
                                shape = RoundedCornerShape(10.dp),
                                modifier = Modifier.fillMaxWidth()
                            ) {
                                Row(
                                    modifier = Modifier.padding(12.dp),
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    Icon(
                                        imageVector = Icons.Default.ReportProblem,
                                        contentDescription = null,
                                        tint = Color(0xFFDC2626),
                                        modifier = Modifier.size(20.dp)
                                    )
                                    Spacer(modifier = Modifier.width(10.dp))
                                    Column {
                                        Text(
                                            text = log.type.name.replace("_", " "),
                                            fontSize = 12.sp,
                                            fontWeight = FontWeight.Bold,
                                            color = Color(0xFFDC2626)
                                        )
                                        Text(
                                            text = log.description,
                                            fontSize = 12.sp,
                                            color = MaterialTheme.colorScheme.onSurface
                                        )
                                    }
                                }
                            }
                        }
                    }
                }

                Spacer(modifier = Modifier.height(24.dp))
            }
        }
    }

    if (showSubmitDialog) {
        val totalAnswered = userAnswers.size
        val totalFlagged = flaggedQuestions.size
        val totalUnanswered = questions.size - totalAnswered

        AlertDialog(
            onDismissRequest = { showSubmitDialog = false },
            icon = {
                Icon(Icons.Default.HelpOutline, contentDescription = null, tint = Color(0xFF4F46E5), modifier = Modifier.size(36.dp))
            },
            title = {
                Text("Kirim Jawaban Ujian?", fontWeight = FontWeight.Bold, fontSize = 18.sp)
            },
            text = {
                Column {
                    Text("Apakah kamu yakin ingin mengakhiri ujian 20 soal ini?")
                    Spacer(modifier = Modifier.height(12.dp))
                    Card(
                        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Column(modifier = Modifier.padding(12.dp)) {
                            Text("• Sudah Dijawab: $totalAnswered / ${questions.size}", fontSize = 13.sp, fontWeight = FontWeight.SemiBold, color = Color(0xFF16A34A))
                            if (totalFlagged > 0) {
                                Text("• Soal Ragu-Ragu: $totalFlagged", fontSize = 13.sp, fontWeight = FontWeight.SemiBold, color = Color(0xFFD97706))
                            }
                            if (totalUnanswered > 0) {
                                Text("• Belum Terisi: $totalUnanswered", fontSize = 13.sp, fontWeight = FontWeight.Bold, color = Color(0xFFDC2626))
                            }
                        }
                    }
                }
            },
            confirmButton = {
                Button(
                    onClick = {
                        showSubmitDialog = false
                        onSubmitExam()
                    },
                    colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF16A34A))
                ) {
                    Text("Ya, Kirim Sekarang", color = Color.White)
                }
            },
            dismissButton = {
                OutlinedButton(onClick = { showSubmitDialog = false }) {
                    Text("Lanjutkan Ujian")
                }
            }
        )
    }

    if (isExamLocked) {
        var inputCode by remember { mutableStateOf("") }

        AlertDialog(
            onDismissRequest = { /* Non-dismissible */ },
            icon = {
                Surface(
                    shape = CircleShape,
                    color = Color(0xFFFEE2E2),
                    modifier = Modifier.size(60.dp)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Icon(
                            imageVector = Icons.Default.Lock,
                            contentDescription = "Terkunci",
                            tint = Color(0xFFDC2626),
                            modifier = Modifier.size(32.dp)
                        )
                    }
                }
            },
            title = {
                Text(
                    text = "Ujian Terkunci!",
                    fontWeight = FontWeight.ExtraBold,
                    fontSize = 20.sp,
                    textAlign = TextAlign.Center,
                    color = Color(0xFF991B1B)
                )
            },
            text = {
                Column(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalAlignment = Alignment.CenterHorizontally,
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    Text(
                        text = lockReason.ifEmpty { "Pelanggaran AI Proctoring telah mencapai batas maksimal!" },
                        fontSize = 13.sp,
                        color = MaterialTheme.colorScheme.onSurface,
                        textAlign = TextAlign.Center,
                        fontWeight = FontWeight.Medium
                    )

                    Surface(
                        shape = RoundedCornerShape(12.dp),
                        color = Color(0xFFFEF3C7),
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Row(
                            modifier = Modifier.padding(10.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Icon(
                                Icons.Default.Info,
                                contentDescription = null,
                                tint = Color(0xFFB45309),
                                modifier = Modifier.size(20.dp)
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                            Text(
                                text = "Minta Kode Remedi kepada Guru / Pengawas untuk membuka kembali ujian dan mereset hitungan pelanggaran.",
                                fontSize = 11.sp,
                                color = Color(0xFF78350F),
                                lineHeight = 16.sp
                            )
                        }
                    }

                    OutlinedTextField(
                        value = inputCode,
                        onValueChange = { inputCode = it.uppercase().trim() },
                        label = { Text("Kode Pembuka / Remedi") },
                        singleLine = true,
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(12.dp),
                        isError = remedyError != null,
                        supportingText = remedyError?.let {
                            { Text(text = it, color = Color(0xFFDC2626), fontSize = 11.sp, fontWeight = FontWeight.Bold) }
                        }
                    )
                }
            },
            confirmButton = {
                Column(
                    modifier = Modifier.fillMaxWidth(),
                    verticalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    Button(
                        onClick = { onVerifyRemedyCode(inputCode) },
                        enabled = !isVerifyingRemedy && inputCode.isNotBlank(),
                        colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF16A34A)),
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        if (isVerifyingRemedy) {
                            CircularProgressIndicator(
                                modifier = Modifier.size(16.dp),
                                color = Color.White,
                                strokeWidth = 2.dp
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                        }
                        Text("Buka Kunci & Lanjutkan Ujian", fontWeight = FontWeight.Bold, color = Color.White)
                    }

                    OutlinedButton(
                        onClick = { onSubmitExam() },
                        colors = ButtonDefaults.outlinedButtonColors(contentColor = Color(0xFFDC2626)),
                        border = androidx.compose.foundation.BorderStroke(1.5.dp, Color(0xFFDC2626)),
                        shape = RoundedCornerShape(12.dp),
                        modifier = Modifier.fillMaxWidth()
                    ) {
                        Icon(Icons.Default.DoneAll, contentDescription = null, modifier = Modifier.size(16.dp))
                        Spacer(modifier = Modifier.width(6.dp))
                        Text("Selesai & Kumpulkan Ujian", fontWeight = FontWeight.Bold)
                    }
                }
            },
            containerColor = MaterialTheme.colorScheme.surface,
            shape = RoundedCornerShape(20.dp)
        )
    }
}


@Composable
fun LegendItem(color: Color, label: String) {
    Row(verticalAlignment = Alignment.CenterVertically) {
        Box(
            modifier = Modifier
                .size(12.dp)
                .clip(CircleShape)
                .background(color)
        )
        Spacer(modifier = Modifier.width(6.dp))
        Text(text = label, fontSize = 11.sp, color = MaterialTheme.colorScheme.onSurfaceVariant)
    }
}
