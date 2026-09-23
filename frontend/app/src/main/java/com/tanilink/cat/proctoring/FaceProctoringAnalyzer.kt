package com.tanilink.cat.proctoring

import android.util.Log
import androidx.annotation.OptIn
import androidx.camera.core.ExperimentalGetImage
import androidx.camera.core.ImageAnalysis
import androidx.camera.core.ImageProxy
import com.google.mlkit.vision.common.InputImage
import com.google.mlkit.vision.face.FaceDetection
import com.google.mlkit.vision.face.FaceDetectorOptions

class FaceProctoringAnalyzer(
    private val onFaceStatusChanged: (FaceStatus, String) -> Unit
) : ImageAnalysis.Analyzer {

    companion object {
        private const val TAG = "CAT_FaceProctor"
    }

    private val options = FaceDetectorOptions.Builder()
        .setPerformanceMode(FaceDetectorOptions.PERFORMANCE_MODE_FAST)
        .setLandmarkMode(FaceDetectorOptions.LANDMARK_MODE_NONE)
        .setClassificationMode(FaceDetectorOptions.CLASSIFICATION_MODE_NONE)
        .enableTracking()
        .build()

    private val detector = FaceDetection.getClient(options)
    private var lastAnalysisTime = 0L
    private var frameCount = 0

    init {
        Log.i(TAG, "FaceProctoringAnalyzer initialized — ML Kit Face Detection READY")
    }

    @OptIn(ExperimentalGetImage::class)
    override fun analyze(imageProxy: ImageProxy) {
        val currentTime = System.currentTimeMillis()
        // Limit processing frequency to once every 500ms to save battery and performance
        if (currentTime - lastAnalysisTime < 500L) {
            imageProxy.close()
            return
        }
        lastAnalysisTime = currentTime
        frameCount++

        val mediaImage = imageProxy.image
        if (mediaImage != null) {
            val image = InputImage.fromMediaImage(mediaImage, imageProxy.imageInfo.rotationDegrees)
            detector.process(image)
                .addOnSuccessListener { faces ->
                    when {
                        faces.isEmpty() -> {
                            Log.w(TAG, "[Frame #$frameCount] NO_FACE — Wajah tidak terdeteksi")
                            onFaceStatusChanged(FaceStatus.NO_FACE, "Wajah siswa tidak terdeteksi! Mohon tetap menghadap kamera.")
                        }
                        faces.size > 1 -> {
                            Log.w(TAG, "[Frame #$frameCount] MULTIPLE_FACES — ${faces.size} wajah terdeteksi!")
                            onFaceStatusChanged(FaceStatus.MULTIPLE_FACES, "Terdeteksi lebih dari 1 orang! Harap kerjakan ujian sendiri.")
                        }
                        else -> {
                            val face = faces[0]
                            val rotY = face.headEulerAngleY // Head turn left/right
                            val rotZ = face.headEulerAngleZ // Head tilt

                            if (rotY > 25 || rotY < -25 || rotZ > 25 || rotZ < -25) {
                                Log.w(TAG, "[Frame #$frameCount] LOOKING_AWAY — rotY=${"%.1f".format(rotY)}° rotZ=${"%.1f".format(rotZ)}°")
                                onFaceStatusChanged(FaceStatus.LOOKING_AWAY, "Pandangan menoleh ke luar layar! Mohon fokus ke soal.")
                            } else {
                                Log.i(TAG, "[Frame #$frameCount] OK — 1 wajah, rotY=${"%.1f".format(rotY)}° rotZ=${"%.1f".format(rotZ)}°")
                                onFaceStatusChanged(FaceStatus.OK, "Fokus Terjaga")
                            }
                        }
                    }
                }
                .addOnFailureListener { e ->
                    Log.e(TAG, "[Frame #$frameCount] ML Kit error: ${e.message}")
                }
                .addOnCompleteListener {
                    imageProxy.close()
                }
        } else {
            imageProxy.close()
        }
    }
}

