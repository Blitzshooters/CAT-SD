package com.tanilink.cat.ui.components

import android.graphics.Bitmap
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import com.tanilink.cat.data.CatApiClient

@Composable
fun CatImage(
    imageUrl: String?,
    contentDescription: String? = null,
    modifier: Modifier = Modifier,
    maxHeight: Dp? = null
) {
    // If URL is blank or null representation, don't show anything (no loading spinner, no empty space)
    if (imageUrl.isNullOrBlank() || imageUrl.equals("null", ignoreCase = true) || imageUrl.endsWith("/null")) return

    var bitmap by remember(imageUrl) { mutableStateOf<Bitmap?>(null) }
    var isLoading by remember(imageUrl) { mutableStateOf(true) }
    var loadFailed by remember(imageUrl) { mutableStateOf(false) }

    LaunchedEffect(imageUrl) {
        isLoading = true
        loadFailed = false
        val result = CatApiClient.loadBitmap(imageUrl)
        bitmap = result
        isLoading = false
        loadFailed = (result == null)
    }

    // If still loading: show compact spinner
    if (isLoading) {
        Box(
            modifier = modifier
                .fillMaxWidth()
                .height(80.dp),
            contentAlignment = Alignment.Center
        ) {
            CircularProgressIndicator(
                modifier = Modifier.size(22.dp),
                strokeWidth = 2.dp,
                color = MaterialTheme.colorScheme.primary
            )
        }
        return
    }

    // If load failed or no bitmap: show nothing (no broken image icon)
    if (loadFailed || bitmap == null) return

    val baseModifier = modifier
        .fillMaxWidth()
        .clip(RoundedCornerShape(12.dp))
        .background(MaterialTheme.colorScheme.surfaceVariant.copy(alpha = 0.3f))

    val finalModifier = if (maxHeight != null) {
        baseModifier.heightIn(max = maxHeight)
    } else {
        baseModifier
    }

    // Show the loaded image, filling full width with full adaptive height
    Image(
        bitmap = bitmap!!.asImageBitmap(),
        contentDescription = contentDescription,
        contentScale = ContentScale.FillWidth,
        modifier = finalModifier
    )
}
