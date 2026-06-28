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

import java.util.Random;

public class MyFirebaseMessagingService extends com.google.firebase.messaging.FirebaseMessagingService {

    private static final String CHANNEL_ID = "talabahan_orders";
    private static final String CHANNEL_NAME = "Order Updates";
    private static final String CHANNEL_DESC = "Notifications for order status changes and AI responses";

    @Override
    public void onNewToken(String token) {
        super.onNewToken(token);
        // Token is sent to backend via JavaScript bridge when WebView loads
        // Store locally so the JS layer can retrieve it
        getSharedPreferences("fcm_prefs", MODE_PRIVATE)
            .edit()
            .putString("fcm_token", token)
            .apply();
    }

    @Override
    public void onMessageReceived(com.google.firebase.messaging.RemoteMessage message) {
        super.onMessageReceived(message);

        String title;
        String body;
        String orderId = null;
        String notificationType = null;

        if (message.getData().size() > 0) {
            title = message.getData().get("title");
            if (title == null) title = "MJ Talabahan";
            body = message.getData().get("body");
            if (body == null) body = "";
            orderId = message.getData().get("order_id");
            notificationType = message.getData().get("type");
        } else if (message.getNotification() != null) {
            title = message.getNotification().getTitle();
            if (title == null) title = "MJ Talabahan";
            body = message.getNotification().getBody();
            if (body == null) body = "";
        } else {
            title = "MJ Talabahan";
            body = "";
        }

        createNotificationChannel();

        int icon = "chatbot_response".equals(notificationType)
            ? R.drawable.ic_chat_notification
            : R.drawable.ic_chat_notification;

        showNotification(title, body, orderId, icon);
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                CHANNEL_ID,
                CHANNEL_NAME,
                NotificationManager.IMPORTANCE_HIGH
            );
            channel.setDescription(CHANNEL_DESC);
            channel.enableVibration(true);
            NotificationManager manager = getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }

    private void showNotification(String title, String body, String orderId, int icon) {
        Intent intent = new Intent(this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TOP);
        if (orderId != null && !orderId.isEmpty()) {
            intent.setData(android.net.Uri.parse("talabahan://order/" + orderId));
        }

        PendingIntent pendingIntent = PendingIntent.getActivity(
            this, 0, intent,
            PendingIntent.FLAG_UPDATE_CURRENT | PendingIntent.FLAG_IMMUTABLE
        );

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
            .setSmallIcon(icon)
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(new NotificationCompat.BigTextStyle().bigText(body))
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setContentIntent(pendingIntent)
            .setAutoCancel(true)
            .setDefaults(NotificationCompat.DEFAULT_SOUND | NotificationCompat.DEFAULT_VIBRATE);

        int notificationId = orderId != null
            ? orderId.hashCode()
            : new Random().nextInt(Integer.MAX_VALUE);

        NotificationManagerCompat.from(this).notify(notificationId, builder.build());
    }
}
