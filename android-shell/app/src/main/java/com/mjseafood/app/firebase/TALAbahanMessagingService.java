package com.mjseafood.app.firebase;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Intent;
import android.os.Build;

import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;

import com.mjseafood.app.MainActivity;
import com.mjseafood.app.R;

/**
 * Firebase Cloud Messaging service for push notifications.
 *
 * NOTE: To enable this service:
 * 1. Download google-services.json from Firebase Console and place it in android-shell/app/
 * 2. Uncomment the Firebase dependencies in app/build.gradle
 * 3. Add classpath 'com.google.gms:google-services:4.4.0' to project-level build.gradle
 * 4. Add id 'com.google.gms.google-services' to app/build.gradle plugins block
 * 5. Uncomment the <service> block in AndroidManifest.xml
 */
 /*
public class TALAbahanMessagingService extends com.google.firebase.messaging.FirebaseMessagingService {

    private static final String CHANNEL_ID = "talabahan_orders";
    private static final String CHANNEL_NAME = "Order Updates";
    private static final String CHANNEL_DESC = "Notifications for order status changes";

    @Override
    public void onNewToken(String token) {
        super.onNewToken(token);
        // Send the token to your backend server
        // POST /api/device-token with { token: token, platform: 'android' }
    }

    @Override
    public void onMessageReceived(com.google.firebase.messaging.RemoteMessage message) {
        super.onMessageReceived(message);

        String title = message.getNotification() != null ? message.getNotification().getTitle() : "TALAbahan";
        String body = message.getNotification() != null ? message.getNotification().getBody() : "";
        String orderId = message.getData().get("order_id");

        createNotificationChannel();
        showNotification(title, body, orderId);
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                CHANNEL_ID,
                CHANNEL_NAME,
                NotificationManager.IMPORTANCE_HIGH
            );
            channel.setDescription(CHANNEL_DESC);
            NotificationManager manager = getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }

    private void showNotification(String title, String body, String orderId) {
        Intent intent = new Intent(this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        if (orderId != null) {
            intent.setData(android.net.Uri.parse("talabahan://order/" + orderId));
        }

        PendingIntent pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE
        );

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setContentTitle(title)
            .setContentText(body)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setContentIntent(pendingIntent)
            .setAutoCancel(true);

        NotificationManagerCompat.from(this).notify(
            orderId != null ? orderId.hashCode() : (int) System.currentTimeMillis(),
            builder.build()
        );
    }
}
*/
