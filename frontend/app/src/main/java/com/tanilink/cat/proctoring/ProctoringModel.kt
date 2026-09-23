package com.tanilink.cat.proctoring

data class ProctoringViolation(
    val id: String = java.util.UUID.randomUUID().toString(),
    val type: ViolationType,
    val description: String,
    val timestamp: Long = System.currentTimeMillis()
)

enum class ViolationType {
    SWITCH_TAB,
    NO_FACE,
    MULTIPLE_FACES,
    LOOKING_AWAY,
    RAPID_ANSWERING,
    SNAPSHOT_CAPTURED
}

enum class FaceStatus {
    OK,
    NO_FACE,
    MULTIPLE_FACES,
    LOOKING_AWAY
}
