package com.tanilink.cat.data

import android.content.ContentValues
import android.content.Context
import android.database.sqlite.SQLiteDatabase
import android.database.sqlite.SQLiteOpenHelper
import com.tanilink.cat.model.ExamResult
import com.tanilink.cat.model.Question
import org.json.JSONArray
import org.json.JSONObject

class ExamDatabaseHelper(context: Context) : SQLiteOpenHelper(context, DATABASE_NAME, null, DATABASE_VERSION) {

    companion object {
        private const val DATABASE_NAME = "cat_exam_database.db"
        private const val DATABASE_VERSION = 1

        private const val TABLE_EXAM_RESULTS = "exam_results"
        private const val COLUMN_ID = "id"
        private const val COLUMN_STUDENT_NAME = "student_name"
        private const val COLUMN_SUBJECT_ID = "subject_id"
        private const val COLUMN_SUBJECT_TITLE = "subject_title"
        private const val COLUMN_GRADE = "grade"
        private const val COLUMN_SCORE = "score"
        private const val COLUMN_CORRECT_COUNT = "correct_count"
        private const val COLUMN_WRONG_COUNT = "wrong_count"
        private const val COLUMN_UNANSWERED_COUNT = "unanswered_count"
        private const val COLUMN_TOTAL_QUESTIONS = "total_questions"
        private const val COLUMN_TIME_SPENT = "time_spent_seconds"
        private const val COLUMN_TIMESTAMP = "timestamp"
        private const val COLUMN_USER_ANSWERS = "user_answers_json"
        private const val COLUMN_FLAGGED_QUESTIONS = "flagged_questions_json"
        private const val COLUMN_QUESTIONS = "questions_json"
    }

    override fun onCreate(db: SQLiteDatabase) {
        val createTableQuery = """
            CREATE TABLE $TABLE_EXAM_RESULTS (
                $COLUMN_ID INTEGER PRIMARY KEY AUTOINCREMENT,
                $COLUMN_STUDENT_NAME TEXT,
                $COLUMN_SUBJECT_ID TEXT,
                $COLUMN_SUBJECT_TITLE TEXT,
                $COLUMN_GRADE INTEGER,
                $COLUMN_SCORE INTEGER,
                $COLUMN_CORRECT_COUNT INTEGER,
                $COLUMN_WRONG_COUNT INTEGER,
                $COLUMN_UNANSWERED_COUNT INTEGER,
                $COLUMN_TOTAL_QUESTIONS INTEGER,
                $COLUMN_TIME_SPENT INTEGER,
                $COLUMN_TIMESTAMP INTEGER,
                $COLUMN_USER_ANSWERS TEXT,
                $COLUMN_FLAGGED_QUESTIONS TEXT,
                $COLUMN_QUESTIONS TEXT
            )
        """.trimIndent()
        db.execSQL(createTableQuery)
    }

    override fun onOpen(db: SQLiteDatabase) {
        super.onOpen(db)
        try {
            db.execSQL("ALTER TABLE $TABLE_EXAM_RESULTS ADD COLUMN $COLUMN_QUESTIONS TEXT")
        } catch (_: Exception) {
            // Column already exists
        }
    }

    override fun onUpgrade(db: SQLiteDatabase, oldVersion: Int, newVersion: Int) {
        db.execSQL("DROP TABLE IF EXISTS $TABLE_EXAM_RESULTS")
        onCreate(db)
    }

    fun saveExamResult(studentName: String, result: ExamResult): Long {
        val db = writableDatabase
        val values = ContentValues().apply {
            put(COLUMN_STUDENT_NAME, studentName)
            put(COLUMN_SUBJECT_ID, result.subjectId)
            put(COLUMN_SUBJECT_TITLE, result.subjectTitle)
            put(COLUMN_GRADE, result.grade)
            put(COLUMN_SCORE, result.score)
            put(COLUMN_CORRECT_COUNT, result.correctCount)
            put(COLUMN_WRONG_COUNT, result.wrongCount)
            put(COLUMN_UNANSWERED_COUNT, result.unansweredCount)
            put(COLUMN_TOTAL_QUESTIONS, result.totalQuestions)
            put(COLUMN_TIME_SPENT, result.timeSpentSeconds)
            put(COLUMN_TIMESTAMP, result.timestamp)

            // Convert userAnswers map to JSON string
            val answersJson = JSONObject()
            result.userAnswers.forEach { (qIdx, aIdx) ->
                answersJson.put(qIdx.toString(), aIdx)
            }
            put(COLUMN_USER_ANSWERS, answersJson.toString())

            // Convert flaggedQuestions set to JSON array string
            val flaggedJson = JSONArray()
            result.flaggedQuestions.forEach { qIdx ->
                flaggedJson.put(qIdx)
            }
            put(COLUMN_FLAGGED_QUESTIONS, flaggedJson.toString())

            // Convert questions list to JSON array string
            val questionsJson = JSONArray()
            result.questions.forEach { q ->
                val qObj = JSONObject().apply {
                    put("id", q.id)
                    put("prompt", q.prompt)
                    put("image_url", q.imageUrl ?: "")
                    put("options", JSONArray(q.options))
                    put("option_images", JSONArray(q.optionImages ?: emptyList<String>()))
                    put("correct_answer_index", q.correctAnswerIndex)
                    put("explanation", q.explanation)
                    put("subject_id", q.subjectId)
                    put("grade_level", q.gradeLevel)
                }
                questionsJson.put(qObj)
            }
            put(COLUMN_QUESTIONS, questionsJson.toString())
        }

        return db.insert(TABLE_EXAM_RESULTS, null, values)
    }

    fun getAllExamResults(): List<ExamResult> {
        return queryExamResults(null, null)
    }

    fun getExamResultsForStudent(studentName: String): List<ExamResult> {
        return queryExamResults("$COLUMN_STUDENT_NAME = ?", arrayOf(studentName))
    }

    private fun queryExamResults(selection: String?, selectionArgs: Array<String>?): List<ExamResult> {
        val resultsList = mutableListOf<ExamResult>()
        val db = readableDatabase
        val cursor = db.query(
            TABLE_EXAM_RESULTS,
            null,
            selection,
            selectionArgs,
            null,
            null,
            "$COLUMN_TIMESTAMP DESC"
        )

        with(cursor) {
            while (moveToNext()) {
                val subjectId = getString(getColumnIndexOrThrow(COLUMN_SUBJECT_ID))
                val subjectTitle = getString(getColumnIndexOrThrow(COLUMN_SUBJECT_TITLE))
                val grade = getInt(getColumnIndexOrThrow(COLUMN_GRADE))
                val score = getInt(getColumnIndexOrThrow(COLUMN_SCORE))
                val correctCount = getInt(getColumnIndexOrThrow(COLUMN_CORRECT_COUNT))
                val wrongCount = getInt(getColumnIndexOrThrow(COLUMN_WRONG_COUNT))
                val unansweredCount = getInt(getColumnIndexOrThrow(COLUMN_UNANSWERED_COUNT))
                val totalQuestions = getInt(getColumnIndexOrThrow(COLUMN_TOTAL_QUESTIONS))
                val timeSpent = getLong(getColumnIndexOrThrow(COLUMN_TIME_SPENT))
                val timestamp = getLong(getColumnIndexOrThrow(COLUMN_TIMESTAMP))
                val answersJsonStr = getString(getColumnIndexOrThrow(COLUMN_USER_ANSWERS))
                val flaggedJsonStr = getString(getColumnIndexOrThrow(COLUMN_FLAGGED_QUESTIONS))

                val userAnswers = mutableMapOf<Int, Int>()
                if (!answersJsonStr.isNullOrEmpty()) {
                    val jsonObj = JSONObject(answersJsonStr)
                    jsonObj.keys().forEach { key ->
                        userAnswers[key.toInt()] = jsonObj.getInt(key)
                    }
                }

                val flaggedQuestions = mutableSetOf<Int>()
                if (!flaggedJsonStr.isNullOrEmpty()) {
                    val jsonArr = JSONArray(flaggedJsonStr)
                    for (i in 0 until jsonArr.length()) {
                        flaggedQuestions.add(jsonArr.getInt(i))
                    }
                }

                val questionsJsonStr = try {
                    val colIdx = getColumnIndex(COLUMN_QUESTIONS)
                    if (colIdx != -1) getString(colIdx) else null
                } catch (_: Exception) {
                    null
                }

                val questions = mutableListOf<Question>()
                if (!questionsJsonStr.isNullOrEmpty()) {
                    try {
                        val qArr = JSONArray(questionsJsonStr)
                        for (i in 0 until qArr.length()) {
                            val qObj = qArr.getJSONObject(i)
                            val opts = mutableListOf<String>()
                            val optsArr = qObj.optJSONArray("options")
                            if (optsArr != null) {
                                for (j in 0 until optsArr.length()) {
                                    opts.add(optsArr.getString(j))
                                }
                            }
                            val optImgs = mutableListOf<String>()
                            val optImgsArr = qObj.optJSONArray("option_images")
                            if (optImgsArr != null) {
                                for (j in 0 until optImgsArr.length()) {
                                    optImgs.add(optImgsArr.getString(j))
                                }
                            }
                            questions.add(
                                Question(
                                    id = qObj.optInt("id", i + 1),
                                    prompt = qObj.optString("prompt", ""),
                                    options = opts,
                                    correctAnswerIndex = qObj.optInt("correct_answer_index", 0),
                                    explanation = qObj.optString("explanation", ""),
                                    subjectId = qObj.optString("subject_id", subjectId),
                                    gradeLevel = qObj.optInt("grade_level", grade),
                                    imageUrl = qObj.optString("image_url", "").ifBlank { null },
                                    optionImages = optImgs.ifEmpty { null }
                                )
                            )
                        }
                    } catch (_: Exception) {}
                }

                resultsList.add(
                    ExamResult(
                        subjectId = subjectId,
                        subjectTitle = subjectTitle,
                        grade = grade,
                        score = score,
                        correctCount = correctCount,
                        wrongCount = wrongCount,
                        unansweredCount = unansweredCount,
                        totalQuestions = totalQuestions,
                        timeSpentSeconds = timeSpent,
                        timestamp = timestamp,
                        userAnswers = userAnswers,
                        flaggedQuestions = flaggedQuestions,
                        questions = questions
                    )
                )
            }
            close()
        }
        return resultsList
    }

    fun deleteExamResultByTimestamp(timestamp: Long): Int {
        val db = writableDatabase
        return db.delete(TABLE_EXAM_RESULTS, "$COLUMN_TIMESTAMP = ?", arrayOf(timestamp.toString()))
    }

    /**
     * Delete all local exam results for a specific subjectId + grade (used when syncing with backend:
     * if backend no longer has this result, it was deleted by admin, so remove locally too).
     */
    fun deleteExamResultBySubjectAndGrade(subjectId: String, grade: Int): Int {
        val db = writableDatabase
        return db.delete(
            TABLE_EXAM_RESULTS,
            "$COLUMN_SUBJECT_ID = ? AND $COLUMN_GRADE = ?",
            arrayOf(subjectId, grade.toString())
        )
    }

    fun deleteAllExamResults(): Int {
        val db = writableDatabase
        return db.delete(TABLE_EXAM_RESULTS, null, null)
    }
}
