package com.tanilink.cat.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.tanilink.cat.data.CatApiClient
import com.tanilink.cat.data.SampleData
import com.tanilink.cat.model.*

import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoginScreen(
    onLoginSuccess: (String, UserAvatar, String, Boolean, String, String, String) -> Unit // name, avatar, token, isAdmin, username, nomorInduk, serverAvatar
) {
    var usernameInput by remember { mutableStateOf("") }
    var passwordInput by remember { mutableStateOf("") }
    var isPasswordVisible by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }
    val coroutineScope = rememberCoroutineScope()

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background
    ) { innerPadding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(innerPadding),
            contentAlignment = Alignment.Center
        ) {
            Column(
                modifier = Modifier
                .widthIn(max = 420.dp)
                .padding(24.dp)
                .verticalScroll(rememberScrollState()),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                // Header Logo Banner
                Surface(
                    shape = CircleShape,
                    color = Color(0xFF4F46E5),
                    modifier = Modifier
                        .size(76.dp)
                        .shadow(8.dp, CircleShape)
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Icon(
                            imageVector = Icons.Default.School,
                            contentDescription = null,
                            tint = Color.White,
                            modifier = Modifier.size(40.dp)
                        )
                    }
                }

                Spacer(modifier = Modifier.height(14.dp))

                Text(
                    text = "Aplikasi CAT Ujian SD",
                    fontSize = 24.sp,
                    fontWeight = FontWeight.ExtraBold,
                    color = MaterialTheme.colorScheme.onSurface
                )
                Text(
                    text = "Masuk Menggunakan Akun Terdaftar",
                    fontSize = 13.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )

                Spacer(modifier = Modifier.height(24.dp))

                // Card Container for Manual Input Fields
                Card(
                    shape = RoundedCornerShape(24.dp),
                    colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
                    elevation = CardDefaults.cardElevation(defaultElevation = 3.dp),
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Column(
                        modifier = Modifier.padding(20.dp),
                        verticalArrangement = Arrangement.spacedBy(16.dp)
                    ) {
                        Text(
                            text = "Silakan Masukkan Kredensial:",
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold,
                            color = MaterialTheme.colorScheme.onSurface
                        )

                        // Username Input Field
                        OutlinedTextField(
                            value = usernameInput,
                            onValueChange = {
                                usernameInput = it
                                if (errorMessage.isNotEmpty()) errorMessage = ""
                            },
                            isError = errorMessage.isNotEmpty(),
                            label = { Text("Nama Pengguna (Username)") },
                            placeholder = { Text("Contoh: yusuf / zamzam") },
                            leadingIcon = { Icon(Icons.Default.Person, contentDescription = null) },
                            singleLine = true,
                            shape = RoundedCornerShape(16.dp),
                            modifier = Modifier.fillMaxWidth()
                        )

                        // Password Input Field
                        OutlinedTextField(
                            value = passwordInput,
                            onValueChange = {
                                passwordInput = it
                                if (errorMessage.isNotEmpty()) errorMessage = ""
                            },
                            isError = errorMessage.isNotEmpty(),
                            label = { Text("Kata Sandi (Password)") },
                            placeholder = { Text("Masukkan password akun...") },
                            leadingIcon = { Icon(Icons.Default.Lock, contentDescription = null) },
                            trailingIcon = {
                                IconButton(onClick = { isPasswordVisible = !isPasswordVisible }) {
                                    Icon(
                                        imageVector = if (isPasswordVisible) Icons.Default.Visibility else Icons.Default.VisibilityOff,
                                        contentDescription = null
                                    )
                                }
                            },
                            visualTransformation = if (isPasswordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                            singleLine = true,
                            shape = RoundedCornerShape(16.dp),
                            modifier = Modifier.fillMaxWidth()
                        )

                        if (errorMessage.isNotEmpty()) {
                            Surface(
                                shape = RoundedCornerShape(12.dp),
                                color = MaterialTheme.colorScheme.errorContainer,
                                modifier = Modifier.fillMaxWidth()
                            ) {
                                Row(
                                    modifier = Modifier.padding(12.dp),
                                    verticalAlignment = Alignment.CenterVertically
                                ) {
                                    Icon(
                                        imageVector = Icons.Default.ErrorOutline,
                                        contentDescription = null,
                                        tint = MaterialTheme.colorScheme.error,
                                        modifier = Modifier.size(20.dp)
                                    )
                                    Spacer(modifier = Modifier.width(8.dp))
                                    Text(
                                        text = errorMessage,
                                        color = MaterialTheme.colorScheme.onErrorContainer,
                                        fontSize = 12.sp,
                                        fontWeight = FontWeight.SemiBold
                                    )
                                }
                            }
                        }

                        // Login Button
                        Button(
                            onClick = {
                                val trimmedUsername = usernameInput.trim()
                                if (trimmedUsername.isBlank()) {
                                    errorMessage = "Ketik username akun terlebih dahulu."
                                    return@Button
                                }
                                if (passwordInput.isBlank()) {
                                    errorMessage = "Ketik kata sandi terlebih dahulu."
                                    return@Button
                                }
                                isLoading = true
                                errorMessage = ""
                                coroutineScope.launch {
                                    val result = CatApiClient.login(trimmedUsername, passwordInput)
                                    isLoading = false
                                    if (result != null) {
                                        // Login sukses dari database backend
                                        onLoginSuccess(
                                            result.name,
                                            SampleData.avatars[0],
                                            result.token,
                                            result.isAdmin,
                                            trimmedUsername,
                                            result.nomorInduk,
                                            result.avatar
                                        )
                                    } else {
                                        errorMessage = "Username atau password salah. Pastikan akun terdaftar di database server."
                                    }
                                }
                            },
                            enabled = !isLoading,
                            colors = ButtonDefaults.buttonColors(
                                containerColor = MaterialTheme.colorScheme.primary,
                                disabledContainerColor = MaterialTheme.colorScheme.primary.copy(alpha = 0.7f),
                                disabledContentColor = Color.White
                            ),
                            elevation = ButtonDefaults.buttonElevation(
                                defaultElevation = 4.dp,
                                pressedElevation = 1.dp
                            ),
                            shape = RoundedCornerShape(16.dp),
                            modifier = Modifier
                                .fillMaxWidth()
                                .height(52.dp)
                        ) {
                            if (isLoading) {
                                CircularProgressIndicator(
                                    color = Color.White,
                                    strokeWidth = 2.5.dp,
                                    modifier = Modifier.size(22.dp)
                                )
                                Spacer(modifier = Modifier.width(10.dp))
                                Text(
                                    text = "Memverifikasi Akun...",
                                    fontSize = 15.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = Color.White
                                )
                            } else {
                                Icon(Icons.Default.Login, contentDescription = null, tint = Color.White)
                                Spacer(modifier = Modifier.width(8.dp))
                                Text(
                                    text = "Masuk ke Akun Ujian",
                                    fontSize = 15.sp,
                                    fontWeight = FontWeight.Bold,
                                    color = Color.White
                                )
                            }
                        }
                    }
                }

                Spacer(modifier = Modifier.height(20.dp))

                Text(
                    text = "CAT Ujian SD v2.0 • Sistem Terhubung ke Database MySQL",
                    fontSize = 11.sp,
                    color = MaterialTheme.colorScheme.onSurfaceVariant.copy(alpha = 0.7f),
                    textAlign = androidx.compose.ui.text.style.TextAlign.Center
                )
            }
        }
    }
}
