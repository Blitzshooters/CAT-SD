package com.tanilink.cat.proctoring

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

    private val options = FaceDetectorOptions.Builder()
        .setPerformanceMode(FaceDetectorOptions.PERFORMANCE_MODE_FAST)
        .setLandmarkMode(FaceDetectorOptions.LANDMARK_MODE_NONE)
        .setClassificationMode(FaceDetectorOptions.CLASSIFICATION_MODE_NONE)
        .enableTracking()
        .build()

    private val detector = FaceDetection.getClient(options)
    private var lastAnalysisTime = 0L

    @OptIn(ExperimentalGetImage::class)
    override fun analyze(imageProxy: ImageProxy) {
        val currentTime = System.currentTimeMillis()
        // Limit processing frequency to once every 500ms to save battery and performance
        if (currentTime - lastAnalysisTime < 500L) {
            imageProxy.close()
            return
        }
        lastAnalysisTime = currentTime

        val mediaImage = imageProxy.image
        if (mediaImage != null) {
            val image = InputImage.fromMediaImage(mediaImage, imageProxy.imageInfo.rotationDegrees)
            detector.process(image)
                .addOnSuccessListener { faces ->
                    when {
                        faces.isEmpty() -> {
                            onFaceStatusChanged(FaceStatus.NO_FACE, "Wajah siswa tidak terdeteksi! Mohon tetap menghadap kamera.")
                        }
                        faces.size > 1 -> {
                            onFaceStatusChanged(FaceStatus.MULTIPLE_FACES, "Terdeteksi lebih dari 1 orang! Harap kerjakan ujian sendiri.")
                        }
                        else -> {
                            val face = faces[0]
                            val rotY = face.headEulerAngleY // Head turn left/right
                            val rotZ = face.headEulerAngleZ // Head tilt

                            if (rotY > 25 || rotY < -25 || rotZ > 25 || rotZ < -25) {
                                onFaceStatusChanged(FaceStatus.LOOKING_AWAY, "Pandangan menoleh ke luar layar! Mohon fokus ke soal.")
                            } else {
                                onFaceStatusChanged(FaceStatus.OK, "Fokus Terjaga")
                            }
                        }
                    }
                }
                .addOnFailureListener {
                    // Ignore transient ML Kit processing errors
                }
                .addOnCompleteListener {
                    imageProxy.close()
                }
        } else {
            imageProxy.close()
        }
    }
}
