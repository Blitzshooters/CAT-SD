package com.tanilink.cat.data

import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.util.Base64
import androidx.compose.ui.graphics.Color
import com.tanilink.cat.model.ExamSubject
import com.tanilink.cat.model.Question
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import org.json.JSONArray
import org.json.JSONObject
import java.io.BufferedReader
import java.io.InputStreamReader
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.URL

object CatApiClient {

    data class LoginResult(
        val name: String,
        val token: String,
        val isAdmin: Boolean,
        val nomorInduk: String,
        val avatar: String
    )

    // 10.0.2.2 connects to host computer localhost from Android Emulator.
    // 127.0.0.1 for local tests or adb reverse tcp:8000 tcp:8000 (USB device)
    // 192.168.1.2 for direct Wi-Fi LAN connection
    private const val BASE_URL_LOCAL = "http://127.0.0.1:8000/api"
    private const val BASE_URL_LAN = "http://192.168.1.4:8000/api"
    private const val BASE_URL_EMULATOR = "http://10.0.2.2:8000/api"

    var currentBaseUrl = BASE_URL_LOCAL

    private fun getCandidateUrls(): List<String> =
        listOf(currentBaseUrl, BASE_URL_LOCAL, BASE_URL_LAN, BASE_URL_EMULATOR).distinct()

    /**
     * Login ke backend — returns LoginResult atau null jika gagal
     */
    suspend fun login(username: String, password: String): LoginResult? = withContext(Dispatchers.IO) {
        val urlsToTry = getCandidateUrls()
        for (baseUrl in urlsToTry) {
            try {
                val url = URL("$baseUrl/auth/login")
                val conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "POST"
                    connectTimeout = 5000
                    readTimeout = 5000
                    doOutput = true
                    setRequestProperty("Content-Type", "application/json")
                    setRequestProperty("Accept", "application/json")
                }

                val body = JSONObject().apply {
                    put("username", username.trim())
                    put("password", password)
                }
                OutputStreamWriter(conn.outputStream).use { it.write(body.toString()) }

                if (conn.responseCode == 200) {
                    currentBaseUrl = baseUrl
                    val response = conn.inputStream.bufferedReader().use { it.readText() }
                    val json = JSONObject(response)
                    if (json.optBoolean("success")) {
                        val token = json.getString("token")
                        val userObj = json.getJSONObject("user")
                        val name = userObj.optString("name", username)
                        val isAdmin = userObj.optBoolean("is_admin", false)
                        val nomorInduk = userObj.optString("nomor_induk", "")
                        val avatar = userObj.optString("avatar", "")
                        return@withContext LoginResult(name, token, isAdmin, nomorInduk, avatar)
                    }
                }
            } catch (_: Exception) {
                // Coba URL berikutnya
            }
        }
        null
    }

    suspend fun getSubjects(grade: Int): List<ExamSubject>? = withContext(Dispatchers.IO) {
        val urlsToTry = getCandidateUrls()
        for (baseUrl in urlsToTry) {
            try {
                val url = URL("$baseUrl/subjects?grade=$grade")
                val conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "GET"
                    connectTimeout = 3000
                    readTimeout = 4000
                    setRequestProperty("Accept", "application/json")
                }

                if (conn.responseCode == 200) {
                    currentBaseUrl = baseUrl
                    val response = conn.inputStream.bufferedReader().use { it.readText() }
                    val json = JSONObject(response)
                    if (json.optBoolean("success")) {
                        val array = json.getJSONArray("subjects")
                        val list = mutableListOf<ExamSubject>()
                        for (i in 0 until array.length()) {
                            val item = array.getJSONObject(i)
                            list.add(
                                ExamSubject(
                                    id = item.optString("id", i.toString()),
                                    title = item.optString("title", "Ujian"),
                                    iconName = item.optString("icon_name", "school"),
                                    primaryColor = when (i % 4) {
                                        0 -> Color(0xFF4F46E5)
                                        1 -> Color(0xFF0284C7)
                                        2 -> Color(0xFF16A34A)
                                        else -> Color(0xFFDC2626)
                                    },
                                    secondaryColor = when (i % 4) {
                                        0 -> Color(0xFFEEF2FF)
                                        1 -> Color(0xFFE0F2FE)
                                        2 -> Color(0xFFDCFCE7)
                                        else -> Color(0xFFFEE2E2)
                                    },
                                    questionCount = item.optInt("question_count", 20),
                                    durationMinutes = item.optInt("duration_minutes", 45),
                                    description = item.optString("description", ""),
                                    maxViolations = item.optInt("max_violations", 3),
                                    remedyCode = item.optString("remedy_code", "REMEDI$grade"),
                                    gradeLevel = item.optInt("grade", grade)
                                )
                            )
                        }
                        return@withContext list
                    }
                }
            } catch (_: Exception) {
                // Try next URL or fallback
            }
        }
        null
    }

    suspend fun getQuestions(subjectId: String, grade: Int): List<Question>? = withContext(Dispatchers.IO) {
        val urlsToTry = getCandidateUrls()
        for (baseUrl in urlsToTry) {
            try {
                val url = URL("$baseUrl/subjects/$subjectId/questions")
                val conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "GET"
                    connectTimeout = 3000
                    readTimeout = 4000
                    setRequestProperty("Accept", "application/json")
                }

                if (conn.responseCode == 200) {
                    currentBaseUrl = baseUrl
                    val response = conn.inputStream.bufferedReader().use { it.readText() }
                    val json = JSONObject(response)
                    if (json.optBoolean("success")) {
                        val array = json.getJSONArray("questions")
                        val list = mutableListOf<Question>()
                        for (i in 0 until array.length()) {
                            val qObj = array.getJSONObject(i)

                            val optsArray = qObj.getJSONArray("options")
                            val optionsList = mutableListOf<String>()
                            for (o in 0 until optsArray.length()) {
                                optionsList.add(optsArray.getString(o))
                            }

                            val optImagesList = mutableListOf<String?>()
                            val optImagesJson = qObj.optJSONArray("option_images")
                            if (optImagesJson != null) {
                                for (o in 0 until optImagesJson.length()) {
                                    val img = optImagesJson.optString(o, "")
                                    optImagesList.add(if (img.isNotEmpty()) resolveImageUrl(baseUrl, img) else null)
                                }
                            } else {
                                repeat(4) { optImagesList.add(null) }
                            }

                            val qImg = qObj.optString("question_image", "")

                            list.add(
                                Question(
                                    id = qObj.optInt("id", i + 1),
                                    prompt = qObj.getString("prompt"),
                                    options = optionsList,
                                    correctAnswerIndex = qObj.getInt("correct_answer_index"),
                                    explanation = qObj.optString("explanation", ""),
                                    subjectId = subjectId,
                                    gradeLevel = grade,
                                    imageUrl = if (qImg.isNotEmpty()) resolveImageUrl(baseUrl, qImg) else null,
                                    optionImages = optImagesList
                                )
                            )
                        }
                        return@withContext list
                    }
                }
            } catch (_: Exception) {
                // Try next
            }
        }
        null
    }

    /**
     * Update profil pengguna ke backend (name, avatar, grade)
     * POST /api/user/profile
     */
    suspend fun updateProfile(
        username: String,
        name: String,
        avatarId: String,
        grade: Int
    ): Boolean = withContext(Dispatchers.IO) {
        try {
            val url = URL("$currentBaseUrl/user/profile")
            val conn = (url.openConnection() as HttpURLConnection).apply {
                requestMethod = "POST"
                connectTimeout = 4000
                readTimeout = 5000
                doOutput = true
                setRequestProperty("Content-Type", "application/json")
                setRequestProperty("Accept", "application/json")
            }
            val body = JSONObject().apply {
                put("username", username)
                put("name", name)
                put("avatar", avatarId)
                put("grade", grade)
            }
            OutputStreamWriter(conn.outputStream).use { it.write(body.toString()) }
            conn.responseCode in 200..299
        } catch (_: Exception) {
            false
        }
    }

    suspend fun changePassword(
        username: String,
        currentPass: String,
        newPass: String
    ): Pair<Boolean, String> = withContext(Dispatchers.IO) {
        val urlsToTry = getCandidateUrls()
        for (baseUrl in urlsToTry) {
            try {
                val url = URL("$baseUrl/user/change-password")
                val conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "POST"
                    connectTimeout = 5000
                    readTimeout = 6000
                    doOutput = true
                    setRequestProperty("Content-Type", "application/json")
                    setRequestProperty("Accept", "application/json")
                }
                val body = JSONObject().apply {
                    put("username", username)
                    put("current_password", currentPass)
                    put("new_password", newPass)
                }
                OutputStreamWriter(conn.outputStream).use { it.write(body.toString()) }
                val responseText = (if (conn.responseCode in 200..299) conn.inputStream else conn.errorStream)
                    ?.bufferedReader()?.use { it.readText() } ?: ""
                val json = JSONObject(responseText)
                val success = json.optBoolean("success", false)
                val msg = json.optString("message", if (success) "Password berhasil diubah!" else "Gagal mengubah password")
                if (conn.responseCode in 200..299 || conn.responseCode == 400 || conn.responseCode == 422) {
                    currentBaseUrl = baseUrl
                    return@withContext Pair(success, msg)
                }
            } catch (_: Exception) {
            }
        }
        Pair(false, "Tidak dapat terhubung ke server backend.")
    }

    suspend fun uploadAvatarBase64(
        username: String,
        base64Data: String
    ): Pair<String?, String> = withContext(Dispatchers.IO) {
        var lastError = "Tidak dapat terhubung ke server backend."
        val urlsToTry = getCandidateUrls()
        for (baseUrl in urlsToTry) {
            try {
                val url = URL("$baseUrl/user/upload-avatar")
                val conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "POST"
                    connectTimeout = 8000
                    readTimeout = 15000
                    doOutput = true
                    setRequestProperty("Content-Type", "application/json")
                    setRequestProperty("Accept", "application/json")
                }
                val body = JSONObject().apply {
                    put("username", username)
                    put("avatar_base64", base64Data)
                }
                OutputStreamWriter(conn.outputStream).use { it.write(body.toString()) }
                val responseText = (if (conn.responseCode in 200..299) conn.inputStream else conn.errorStream)
                    ?.bufferedReader()?.use { it.readText() } ?: ""
                if (responseText.isNotBlank()) {
                    val json = JSONObject(responseText)
                    val message = json.optString("message", "")
                    if (conn.responseCode in 200..299 && json.optBoolean("success")) {
                        currentBaseUrl = baseUrl
                        val avatarUrl = json.optString("avatar_url", "")
                        return@withContext Pair(avatarUrl.ifBlank { null }, "")
                    }
                    if (message.isNotBlank()) {
                        lastError = message
                    }
                }
            } catch (_: Exception) {
                // Try next URL
            }
        }
        Pair(null, lastError)
    }

    suspend fun verifyRemedyCode(subjectId: String, code: String): Boolean = withContext(Dispatchers.IO) {
        val urlsToTry = getCandidateUrls()
        for (baseUrl in urlsToTry) {
            try {
                val url = URL("$baseUrl/exam/verify-remedy")
                val conn = (url.openConnection() as HttpURLConnection).apply {
                    requestMethod = "POST"
                    connectTimeout = 3000
                    readTimeout = 4000
                    doOutput = true
                    setRequestProperty("Content-Type", "application/json")
                    setRequestProperty("Accept", "application/json")
                }

                val body = JSONObject().apply {
                    put("subject_id", subjectId.toIntOrNull() ?: 1)
                    put("code", code.trim())
                }

                OutputStreamWriter(conn.outputStream).use { it.write(body.toString()) }

                if (conn.responseCode == 200) {
                    val res = conn.inputStream.bufferedReader().use { it.readText() }
                    val json = JSONObject(res)
                    return@withContext json.optBoolean("success", false)
                }
            } catch (_: Exception) {
            }
        }
        false
    }

    suspend fun submitExamResult(
        subjectId: String,
        grade: Int,
        score: Int,
        correctCount: Int,
        wrongCount: Int,
        unansweredCount: Int,
        totalQuestions: Int,
        timeSpentSeconds: Long,
        studentName: String
    ): Boolean = withContext(Dispatchers.IO) {
        try {
            val url = URL("$currentBaseUrl/exam/submit")
            val conn = (url.openConnection() as HttpURLConnection).apply {
                requestMethod = "POST"
                connectTimeout = 3000
                readTimeout = 4000
                doOutput = true
                setRequestProperty("Content-Type", "application/json")
                setRequestProperty("Accept", "application/json")
            }

            val body = JSONObject().apply {
                put("subject_id", subjectId.toIntOrNull() ?: 1)
                put("grade", grade)
                put("score", score)
                put("correct_count", correctCount)
                put("wrong_count", wrongCount)
                put("unanswered_count", unansweredCount)
                put("total_questions", totalQuestions)
                put("time_spent_seconds", timeSpentSeconds)
                put("username", studentName.lowercase().replace(" ", ""))
            }

            OutputStreamWriter(conn.outputStream).use { it.write(body.toString()) }
            return@withContext conn.responseCode in 200..299
        } catch (_: Exception) {
            false
        }
    }

    private fun resolveImageUrl(baseUrl: String, path: String): String {
        if (path.isBlank()) return ""
        if (path.startsWith("data:")) return path
        if (path.startsWith("http://") || path.startsWith("https://")) return path

        val host = baseUrl.substringBefore("/api")
        val cleanPath = path.removePrefix("/")
        return "$host/$cleanPath"
    }

    suspend fun loadBitmap(urlOrBase64: String): Bitmap? = withContext(Dispatchers.IO) {
        if (urlOrBase64.isBlank()) return@withContext null
        try {
            if (urlOrBase64.startsWith("data:image")) {
                val base64Data = urlOrBase64.substringAfter("base64,")
                val decodedBytes = Base64.decode(base64Data, Base64.DEFAULT)
                return@withContext BitmapFactory.decodeByteArray(decodedBytes, 0, decodedBytes.size)
            }

            // Build candidate URLs to attempt loading image
            val candidateUrls = mutableListOf<String>()
            candidateUrls.add(urlOrBase64)

            // If it's a storage path or local URL, generate candidates with all known hosts
            val pathPortion = when {
                urlOrBase64.contains("/storage/") -> "/storage/" + urlOrBase64.substringAfter("/storage/")
                urlOrBase64.startsWith("/") -> urlOrBase64
                else -> null
            }

            if (pathPortion != null) {
                val cleanPath = pathPortion.removePrefix("/")
                val hosts = listOf(
                    currentBaseUrl.substringBefore("/api"),
                    "http://127.0.0.1:8000",
                    "http://192.168.1.2:8000",
                    "http://10.0.2.2:8000"
                ).distinct()

                for (h in hosts) {
                    val fullUrl = "$h/$cleanPath"
                    if (!candidateUrls.contains(fullUrl)) {
                        candidateUrls.add(fullUrl)
                    }
                }
            }

            for (candidate in candidateUrls) {
                try {
                    val conn = (URL(candidate).openConnection() as HttpURLConnection).apply {
                        connectTimeout = 3500
                        readTimeout = 4500
                        instanceFollowRedirects = true
                    }

                    if (conn.responseCode in 200..299) {
                        val bytes = conn.inputStream.use { it.readBytes() }
                        if (bytes.isNotEmpty()) {
                            val bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
                            if (bitmap != null) {
                                return@withContext bitmap
                            }
                        }
                    }
                } catch (_: Exception) {
                    // Try next candidate URL
                }
            }
        } catch (_: Exception) {
        }
        null
    }
}
