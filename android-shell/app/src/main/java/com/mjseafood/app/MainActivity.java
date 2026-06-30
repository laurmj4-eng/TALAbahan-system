package com.mjseafood.app;

import android.Manifest;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.ConnectivityManager;
import android.net.NetworkCapabilities;
import android.net.Uri;
import android.provider.Settings;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.HandlerThread;
import android.os.Looper;
import android.os.Message;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.GeolocationPermissions;
import android.webkit.JavascriptInterface;
import android.webkit.PermissionRequest;
import android.webkit.SslErrorHandler;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ProgressBar;
import android.widget.Toast;

import androidx.core.app.ActivityCompat;
import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;
import androidx.core.content.FileProvider;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.google.firebase.messaging.FirebaseMessaging;
import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

public class MainActivity extends Activity {

    private WebView webView;
    private ProgressBar progressBar;
    private View chromeLoadingOverlay;
    private SwipeRefreshLayout swipeRefreshLayout;
    private final Handler mainHandler = new Handler(Looper.getMainLooper());
    private HandlerThread backgroundThread;
    private Handler backgroundHandler;
    private ValueCallback<Uri[]> fileUploadCallback;
    private static final int FILE_CHOOSER_REQUEST_CODE = 1001;
    private static final int LOCATION_PERMISSION_REQUEST_CODE = 1003;
    private GeolocationPermissions.Callback pendingGeolocationCallback;
    private String pendingGeolocationOrigin;
    private AlertDialog popupDialog;
    private boolean locationRequestPending = false;

    private static final String BASE_URL = "https://talabahan-system-1.onrender.com";
    private static final String SITE_URL = BASE_URL + "/?auth_mode=mobile";
    private static final String VERSION_URL = BASE_URL + "/version.json";
    private static final String AUTHORITY = "com.Mjtalabahan.app.fileprovider";
    private static final String DEEP_LINK_SCHEME = "talabahan:";
    private static final long PAGE_LOAD_TIMEOUT_MS = 30000;
    private static final String APP_CACHE_DIR = "updates";
    private static final String UPDATE_CHANNEL_ID = "talabahan_updates";
    private static final int UPDATE_NOTIFICATION_ID = 1000;

    private boolean isConnected() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm == null) return false;
        NetworkCapabilities nc = cm.getNetworkCapabilities(cm.getActiveNetwork());
        return nc != null
                && nc.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
                && nc.hasCapability(NetworkCapabilities.NET_CAPABILITY_VALIDATED);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        WindowCompat.setDecorFitsSystemWindows(getWindow(), false);

        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_FULLSCREEN,
                WindowManager.LayoutParams.FLAG_FULLSCREEN
        );

        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webview);
        progressBar = findViewById(R.id.progress_bar);
        chromeLoadingOverlay = findViewById(R.id.chromeLoadingOverlay);
        swipeRefreshLayout = findViewById(R.id.swipe_refresh);
        swipeRefreshLayout.setColorSchemeResources(android.R.color.holo_blue_light, android.R.color.holo_green_light);
        swipeRefreshLayout.setProgressBackgroundColorSchemeResource(android.R.color.white);
        swipeRefreshLayout.setOnRefreshListener(() -> webView.reload());
        swipeRefreshLayout.setEnabled(false);
        setupWebView();

        backgroundThread = new HandlerThread("BackgroundWork");
        backgroundThread.start();
        backgroundHandler = new Handler(backgroundThread.getLooper());

        if (savedInstanceState != null) {
            webView.restoreState(savedInstanceState);
        } else {
            if (!handleDeepLinkIntent(getIntent())) {
                webView.loadUrl(SITE_URL);
            }
        }

        createUpdateChannel();
        checkForUpdates();

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            if (checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS)
                    != PackageManager.PERMISSION_GRANTED) {
                ActivityCompat.requestPermissions(this,
                        new String[]{Manifest.permission.POST_NOTIFICATIONS}, 1002);
            }
        }
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleDeepLinkIntent(intent);
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        if (webView != null) {
            webView.saveState(outState);
        }
    }

    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        if (webView != null) {
            webView.restoreState(savedInstanceState);
        }
    }

    private boolean handleDeepLinkIntent(Intent intent) {
        if (intent == null || intent.getData() == null) return false;

        Uri data = intent.getData();
        String urlStr = data.toString();

        // Handle talabahan://auth?redirect=... (legacy custom scheme)
        if ("talabahan".equals(data.getScheme()) && "auth".equals(data.getHost())) {
            String redirectUrl = data.getQueryParameter("redirect");

            if (redirectUrl != null && !redirectUrl.isEmpty()) {
                webView.loadUrl(redirectUrl);
            } else {
                String rawQuery = data.getEncodedQuery();
                String callbackUrl = BASE_URL + "/auth/mobile-callback";
                if (rawQuery != null && !rawQuery.isEmpty()) {
                    callbackUrl += "?" + rawQuery;
                }
                webView.loadUrl(callbackUrl);
            }

            chromeLoadingOverlay.setVisibility(View.GONE);
            intent.setData(null);
            return true;
        }

        // Handle HTTPS callback URL from intent:// (Chrome opens app after Google auth)
        if (urlStr.startsWith(BASE_URL + "/auth/mobile-callback")) {
            webView.loadUrl(urlStr);
            chromeLoadingOverlay.setVisibility(View.GONE);
            intent.setData(null);
            return true;
        }

        // Handle talabahan://order/{id} (push notification tap → navigate to order detail)
        if ("talabahan".equals(data.getScheme()) && "order".equals(data.getHost())) {
            String orderPath = data.getPath();
            if (orderPath != null && orderPath.length() > 1) {
                String orderId = orderPath.substring(1);
                webView.loadUrl(BASE_URL + "/customer/order-details/" + orderId);
            } else {
                webView.loadUrl(BASE_URL);
            }
            chromeLoadingOverlay.setVisibility(View.GONE);
            intent.setData(null);
            return true;
        }

        return false;
    }

    private void checkForUpdates() {
        backgroundHandler.post(() -> {
            try {
                HttpURLConnection conn = (HttpURLConnection) new URL(VERSION_URL).openConnection();
                conn.setConnectTimeout(8000);
                conn.setReadTimeout(8000);
                conn.setRequestMethod("GET");

                if (conn.getResponseCode() != 200) return;

                BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder sb = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) sb.append(line);
                reader.close();
                conn.disconnect();

                JSONObject json = new JSONObject(sb.toString());
                int remoteVersion = json.getInt("versionCode");
                String apkUrl = json.getString("apkUrl");

                PackageManager pm = getPackageManager();
                try {
                    PackageInfo pkgInfo = pm.getPackageInfo(getPackageName(), 0);
                    int currentVersion = pkgInfo.versionCode;

                    if (remoteVersion > currentVersion) {
                        mainHandler.post(() -> downloadUpdate(apkUrl));
                    }
                } catch (PackageManager.NameNotFoundException ignored) {
                }
            } catch (Exception ignored) {
            }
        });
    }

    private void createUpdateChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            NotificationChannel channel = new NotificationChannel(
                UPDATE_CHANNEL_ID,
                "App Updates",
                NotificationManager.IMPORTANCE_LOW
            );
            channel.setDescription("APK download progress");
            channel.setShowBadge(false);
            NotificationManager manager = getSystemService(NotificationManager.class);
            if (manager != null) {
                manager.createNotificationChannel(channel);
            }
        }
    }

    private boolean canPostNotifications() {
        return Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU
            || checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS)
               == PackageManager.PERMISSION_GRANTED;
    }

    private void downloadUpdate(String apkUrl) {
        boolean canNotify = canPostNotifications();

        Toast.makeText(this, "New update available. Downloading...", Toast.LENGTH_LONG).show();

        final NotificationManagerCompat notificationManager = canNotify
            ? NotificationManagerCompat.from(this) : null;
        final NotificationCompat.Builder builder = canNotify
            ? new NotificationCompat.Builder(this, UPDATE_CHANNEL_ID)
                .setSmallIcon(android.R.drawable.stat_sys_download)
                .setContentTitle("Downloading update")
                .setContentText("Starting...")
                .setPriority(NotificationCompat.PRIORITY_LOW)
                .setOngoing(true)
                .setProgress(0, 0, true)
            : null;
        if (canNotify) {
            notificationManager.notify(UPDATE_NOTIFICATION_ID, builder.build());
        }

        backgroundHandler.post(() -> {
            try {
                URL url = new URL(apkUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.connect();

                int contentLength = conn.getContentLength();

                File cacheDir = new File(getCacheDir(), APP_CACHE_DIR);
                if (!cacheDir.exists()) cacheDir.mkdirs();
                File apkFile = new File(cacheDir, "talabahan-update.apk");

                InputStream input = new BufferedInputStream(url.openStream());
                FileOutputStream output = new FileOutputStream(apkFile);

                byte[] buffer = new byte[8192];
                int bytesRead;
                long totalBytes = 0;
                int lastProgressReport = 0;

                while ((bytesRead = input.read(buffer)) != -1) {
                    output.write(buffer, 0, bytesRead);
                    totalBytes += bytesRead;

                    if (canNotify && contentLength > 0) {
                        int pct = (int) (totalBytes * 100 / contentLength);
                        if (pct > lastProgressReport) {
                            lastProgressReport = pct;
                            int reportPct = pct;
                            mainHandler.post(() -> {
                                builder.setProgress(100, reportPct, false)
                                    .setContentText("Downloading... " + reportPct + "%");
                                notificationManager.notify(UPDATE_NOTIFICATION_ID, builder.build());
                            });
                        }
                    }
                }

                output.flush();
                output.close();
                input.close();

                mainHandler.post(() -> {
                    if (canNotify) {
                        builder.setContentTitle("Installing update")
                            .setContentText("Please wait...")
                            .setProgress(0, 0, true)
                            .setOngoing(false);
                        notificationManager.notify(UPDATE_NOTIFICATION_ID, builder.build());
                    }
                    Toast.makeText(MainActivity.this, "Installing update...", Toast.LENGTH_SHORT).show();
                    installApk(apkFile);
                });

            } catch (Exception e) {
                e.printStackTrace();
                mainHandler.post(() -> {
                    if (canNotify) {
                        builder.setContentTitle("Update failed")
                            .setContentText("Download error.")
                            .setProgress(0, 0, false)
                            .setOngoing(false);
                        notificationManager.notify(UPDATE_NOTIFICATION_ID, builder.build());
                        mainHandler.postDelayed(() ->
                            notificationManager.cancel(UPDATE_NOTIFICATION_ID), 5000);
                    }
                    Toast.makeText(MainActivity.this,
                        "Update download failed: " + e.getMessage(), Toast.LENGTH_LONG).show();
                });
            }
        });
    }

    private void installApk(File apkFile) {
        try {
            Uri apkUri = FileProvider.getUriForFile(this, AUTHORITY, apkFile);
            Intent intent = new Intent(Intent.ACTION_VIEW);
            intent.setDataAndType(apkUri, "application/vnd.android.package-archive");
            intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(intent);
            Toast.makeText(this, "Installing update...", Toast.LENGTH_SHORT).show();
            NotificationManagerCompat.from(this).cancel(UPDATE_NOTIFICATION_ID);
        } catch (Exception e) {
            e.printStackTrace();
            Toast.makeText(this, "Update install failed: " + e.getMessage(), Toast.LENGTH_LONG).show();
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private void setupWebView() {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setGeolocationEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setAllowFileAccess(false);
        settings.setLoadWithOverviewMode(true);
        settings.setUseWideViewPort(true);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
        settings.setMediaPlaybackRequiresUserGesture(false);

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            settings.setSafeBrowsingEnabled(true);
        }

        String defaultUA = WebSettings.getDefaultUserAgent(MainActivity.this);
        String cleanUA = defaultUA.replace("; wv", "").replace("Version/4.0 ", "");
        settings.setUserAgentString(cleanUA + " TALAbahanAndroidApp");

        settings.setSupportMultipleWindows(true);
        settings.setJavaScriptCanOpenWindowsAutomatically(true);

        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            cookieManager.setAcceptThirdPartyCookies(webView, false);
        }
        cookieManager.setAcceptFileSchemeCookies(false);

        webView.setWebViewClient(new WebViewClient() {
            private boolean isLoadingTimeout = false;

            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                return shouldOverrideUrl(view, request.getUrl().toString());
            }

            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                return shouldOverrideUrl(view, url);
            }

            private boolean shouldOverrideUrl(WebView view, String url) {
                if (url.startsWith("intent://")) {
                    return true;
                }
                if (url.startsWith("talabahan://")) {
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(intent);
                    return true;
                }
                if (url.startsWith(BASE_URL)) {
                    // Open mobile Google sign-in in external browser (popup doesn't work in WebView)
                    if (url.contains("/auth/mobile-login")) {
                        chromeLoadingOverlay.setVisibility(View.VISIBLE);
                        Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                        intent.addFlags(Intent.FLAG_ACTIVITY_NO_HISTORY);
                        startActivity(intent);
                        return true;
                    }
                    if (!url.contains("auth_mode=mobile")) {
                        String sep = url.contains("?") ? "&" : "?";
                        url = url + sep + "auth_mode=mobile";
                    }
                    view.loadUrl(url);
                    return true;
                }
                try {
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(intent);
                    return true;
                } catch (Exception e) {
                    return false;
                }
            }

            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                progressBar.setVisibility(View.VISIBLE);
                progressBar.setProgress(0);
                isLoadingTimeout = false;

                mainHandler.postDelayed(() -> {
                    if (progressBar.getVisibility() == View.VISIBLE && !isLoadingTimeout) {
                        isLoadingTimeout = true;
                        Toast.makeText(MainActivity.this,
                                "Page is taking longer than expected...", Toast.LENGTH_LONG).show();
                        view.stopLoading();
                        view.loadUrl("about:blank");
                        progressBar.setVisibility(View.GONE);
                    }
                }, PAGE_LOAD_TIMEOUT_MS);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                swipeRefreshLayout.setRefreshing(false);
                isLoadingTimeout = true;
                progressBar.setVisibility(View.GONE);

                view.evaluateJavascript(
                    "(function(){" +
                    "var o=document.getElementById('boot-splash');" +
                    "if(o){o.style.opacity='0';setTimeout(function(){o.remove()},400);}" +
                    "var b=document.querySelector('[class*=loading],[class*=spinner],[id*=loading],[id*=spinner]');" +
                    "if(b){b.style.display='none';}" +
                    "var s=document.getElementById('boot-text');" +
                    "if(s){s.textContent='';}" +
                    "return 'ok';" +
                    "})()", null
                );
            }

            @Override
            public void onReceivedError(WebView view, int errorCode, String description, String failingUrl) {
                super.onReceivedError(view, errorCode, description, failingUrl);
                swipeRefreshLayout.setRefreshing(false);
                isLoadingTimeout = true;
                progressBar.setVisibility(View.GONE);
            }

            @Override
            public void onReceivedHttpError(WebView view, WebResourceRequest request, WebResourceResponse errorResponse) {
                super.onReceivedHttpError(view, request, errorResponse);
                if (request.isForMainFrame()) {
                    swipeRefreshLayout.setRefreshing(false);
                    progressBar.setVisibility(View.GONE);
                }
            }

            @Override
            public void onReceivedSslError(WebView view, SslErrorHandler handler, android.net.http.SslError error) {
                StringBuilder msg = new StringBuilder("SSL Error: ");
                switch (error.getPrimaryError()) {
                    case android.net.http.SslError.SSL_UNTRUSTED:
                        msg.append("The certificate authority is not trusted.");
                        break;
                    case android.net.http.SslError.SSL_EXPIRED:
                        msg.append("The certificate has expired.");
                        break;
                    case android.net.http.SslError.SSL_IDMISMATCH:
                        msg.append("The certificate Hostname mismatch.");
                        break;
                    case android.net.http.SslError.SSL_NOTYETVALID:
                        msg.append("The certificate is not yet valid.");
                        break;
                    default:
                        msg.append("Unknown SSL error.");
                }
                Toast.makeText(MainActivity.this, msg.toString(), Toast.LENGTH_LONG).show();
                handler.cancel();
            }

            @Override
            public void onPageCommitVisible(WebView view, String url) {
                super.onPageCommitVisible(view, url);
                View decorView = getWindow().getDecorView();
                decorView.setSystemUiVisibility(
                    View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                        | View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                        | View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                        | View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
                        | View.SYSTEM_UI_FLAG_FULLSCREEN
                        | View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                );
            }
        });

        webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                progressBar.setProgress(newProgress);
            }

            @Override
            public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> filePathCallback,
                                             FileChooserParams params) {
                fileUploadCallback = filePathCallback;

                Intent intent = params.createIntent();
                intent.addCategory(Intent.CATEGORY_OPENABLE);

                try {
                    startActivityForResult(
                            Intent.createChooser(intent, "Select File"),
                            FILE_CHOOSER_REQUEST_CODE);
                } catch (Exception e) {
                    fileUploadCallback = null;
                    return false;
                }
                return true;
            }

            @Override
            public void onPermissionRequest(PermissionRequest request) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
                    request.grant(request.getResources());
                }
            }

            @Override
            public void onGeolocationPermissionsShowPrompt(String origin, GeolocationPermissions.Callback callback) {
                if (Build.VERSION.SDK_INT < Build.VERSION_CODES.M) {
                    callback.invoke(origin, true, false);
                    return;
                }
                if (checkSelfPermission(Manifest.permission.ACCESS_FINE_LOCATION)
                        == PackageManager.PERMISSION_GRANTED) {
                    callback.invoke(origin, true, false);
                } else {
                    pendingGeolocationCallback = callback;
                    pendingGeolocationOrigin = origin;
                    ActivityCompat.requestPermissions(MainActivity.this,
                            new String[]{Manifest.permission.ACCESS_FINE_LOCATION},
                            LOCATION_PERMISSION_REQUEST_CODE);
                }
            }

            @Override
            public boolean onCreateWindow(WebView view, boolean isDialog, boolean isUserGesture, Message resultMsg) {
                WebView popup = new WebView(MainActivity.this);
                WebSettings popupSettings = popup.getSettings();
                popupSettings.setJavaScriptEnabled(true);
                popupSettings.setDomStorageEnabled(true);
                popupSettings.setAllowFileAccess(false);
                popupSettings.setMixedContentMode(WebSettings.MIXED_CONTENT_NEVER_ALLOW);
                String defaultUA = WebSettings.getDefaultUserAgent(MainActivity.this);
                popupSettings.setUserAgentString(defaultUA.replace("; wv", "").replace("Version/4.0 ", "") + " TALAbahanAndroidApp");

                popup.setWebViewClient(new WebViewClient() {
                    @Override
                    public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                        return false;
                    }
                    @Override
                    public boolean shouldOverrideUrlLoading(WebView view, String url) {
                        return false;
                    }
                });

                AlertDialog.Builder builder = new AlertDialog.Builder(MainActivity.this);
                builder.setView(popup);
                builder.setCancelable(true);
                builder.setOnDismissListener(dialog -> {
                    if (popup != null) {
                        popup.destroy();
                    }
                });
                popupDialog = builder.show();

                WebView.WebViewTransport transport = (WebView.WebViewTransport) resultMsg.obj;
                transport.setWebView(popup);
                resultMsg.sendToTarget();
                return true;
            }

            @Override
            public void onCloseWindow(WebView window) {
                if (popupDialog != null && popupDialog.isShowing()) {
                    popupDialog.dismiss();
                    popupDialog = null;
                }
                super.onCloseWindow(window);
            }
        });

        webView.setOverScrollMode(View.OVER_SCROLL_NEVER);

        webView.addJavascriptInterface(new Object() {
            @JavascriptInterface
            public void openInBrowser(String url) {
                mainHandler.post(() -> {
                    try {
                        chromeLoadingOverlay.setVisibility(View.VISIBLE);
                        Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(url));
                        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                        intent.addFlags(Intent.FLAG_ACTIVITY_NO_HISTORY);
                        startActivity(intent);
                    } catch (Exception e) {
                        e.printStackTrace();
                    }
                });
            }

            @JavascriptInterface
            public boolean isConnected() {
                return MainActivity.this.isConnected();
            }

            @JavascriptInterface
            public int getAppVersionCode() {
                try {
                    PackageInfo pkgInfo = getPackageManager().getPackageInfo(getPackageName(), 0);
                    return pkgInfo.versionCode;
                } catch (Exception e) {
                    return 0;
                }
            }

            @JavascriptInterface
            public boolean isLocationEnabled() {
                LocationManager lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
                if (lm == null) return false;
                try {
                    return lm.isProviderEnabled(LocationManager.GPS_PROVIDER)
                            || lm.isProviderEnabled(LocationManager.NETWORK_PROVIDER);
                } catch (Exception e) {
                    return false;
                }
            }

            @JavascriptInterface
            public void openLocationSettings() {
                mainHandler.post(() -> {
                    Intent intent = new Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS);
                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                    startActivity(intent);
                });
            }

            @JavascriptInterface
            public String getDeviceModel() {
                return Build.MANUFACTURER + " " + Build.MODEL;
            }

            @JavascriptInterface
            public String getDeviceName() {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N_MR1) {
                    String name = Settings.Global.getString(getContentResolver(), Settings.Global.DEVICE_NAME);
                    if (name != null && !name.isEmpty()) return name;
                }
                return Build.MODEL;
            }

            @JavascriptInterface
            public String getFcmToken() {
                SharedPreferences prefs = getSharedPreferences("fcm_prefs", MODE_PRIVATE);
                String cached = prefs.getString("fcm_token", "");
                if (!cached.isEmpty()) {
                    return cached;
                }
                // No cached token — actively request one from Firebase
                FirebaseMessaging.getInstance().getToken()
                    .addOnCompleteListener(task -> {
                        if (task.isSuccessful() && task.getResult() != null) {
                            prefs.edit().putString("fcm_token", task.getResult()).apply();
                        }
                    });
                return "";
            }

            @JavascriptInterface
            public String getCurrentLocation() {
                LocationManager lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
                if (lm == null) return "{\"error\": \"Location service not available\"}";

                Location location = null;
                try {
                    if (checkSelfPermission(Manifest.permission.ACCESS_FINE_LOCATION)
                            == PackageManager.PERMISSION_GRANTED) {
                        location = lm.getLastKnownLocation(LocationManager.GPS_PROVIDER);
                        if (location == null) {
                            location = lm.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
                        }
                        if (location == null) {
                            location = lm.getLastKnownLocation(LocationManager.PASSIVE_PROVIDER);
                        }
                    } else {
                        return "{\"error\": \"Location permission not granted\"}";
                    }
                } catch (Exception e) {
                    return "{\"error\": \"" + e.getMessage().replace("\"", "\\\"") + "\"}";
                }

                if (location != null) {
                    try {
                        JSONObject json = new JSONObject();
                        json.put("lat", location.getLatitude());
                        json.put("lng", location.getLongitude());
                        json.put("accuracy", location.getAccuracy());
                        return json.toString();
                    } catch (Exception e) {
                        return "{\"error\": \"JSON error\"}";
                    }
                }

                // No cached location — request a fresh GPS fix asynchronously
                mainHandler.post(() -> requestFreshLocation());
                return "{\"pending\": true}";
            }
        }, "AndroidBridge");
    }

    private void requestFreshLocation() {
        if (locationRequestPending) return;
        locationRequestPending = true;

        LocationManager lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
        if (lm == null) {
            locationRequestPending = false;
            mainHandler.post(() -> webView.evaluateJavascript(
                "if(window._onNativeLocationError)_onNativeLocationError('Location service not available')", null));
            return;
        }

        LocationListener listener = new LocationListener() {
            @Override
            public void onLocationChanged(Location location) {
                if (!locationRequestPending) return;
                locationRequestPending = false;
                lm.removeUpdates(this);
                try {
                    JSONObject json = new JSONObject();
                    json.put("lat", location.getLatitude());
                    json.put("lng", location.getLongitude());
                    json.put("accuracy", location.getAccuracy());
                    String result = json.toString();
                    mainHandler.post(() -> webView.evaluateJavascript(
                        "if(window._onNativeLocation)_onNativeLocation(" + result + ")", null));
                } catch (Exception ignored) {}
            }

            @Override
            public void onStatusChanged(String provider, int status, Bundle extras) {}

            @Override
            public void onProviderEnabled(String provider) {}

            @Override
            public void onProviderDisabled(String provider) {}
        };

        try {
            if (checkSelfPermission(Manifest.permission.ACCESS_FINE_LOCATION)
                    == PackageManager.PERMISSION_GRANTED) {
                lm.requestSingleUpdate(LocationManager.GPS_PROVIDER, listener, Looper.getMainLooper());

                mainHandler.postDelayed(() -> {
                    if (locationRequestPending) {
                        locationRequestPending = false;
                        lm.removeUpdates(listener);
                        webView.evaluateJavascript(
                            "if(window._onNativeLocationError)_onNativeLocationError('GPS timed out. Try again.')", null);
                    }
                }, 20000);
            } else {
                locationRequestPending = false;
            }
        } catch (Exception e) {
            locationRequestPending = false;
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == FILE_CHOOSER_REQUEST_CODE) {
            if (fileUploadCallback != null) {
                Uri[] results = null;
                if (resultCode == RESULT_OK && data != null) {
                    String dataString = data.getDataString();
                    if (dataString != null) {
                        results = new Uri[]{Uri.parse(dataString)};
                    }
                }
                fileUploadCallback.onReceiveValue(results);
                fileUploadCallback = null;
            }
            return;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == LOCATION_PERMISSION_REQUEST_CODE && pendingGeolocationCallback != null) {
            boolean granted = grantResults.length > 0
                    && grantResults[0] == PackageManager.PERMISSION_GRANTED;
            pendingGeolocationCallback.invoke(pendingGeolocationOrigin, granted, false);
            pendingGeolocationCallback = null;
            pendingGeolocationOrigin = null;
        }
    }

    @Override
    public void onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack();
        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (webView != null) {
            webView.onResume();
            CookieManager.getInstance().flush();
        }
        if (chromeLoadingOverlay != null) {
            chromeLoadingOverlay.setVisibility(View.GONE);
        }
    }

    @Override
    protected void onPause() {
        if (webView != null) {
            webView.onPause();
        }
        super.onPause();
    }

    @Override
    protected void onDestroy() {
        if (webView != null) {
            webView.destroy();
        }
        if (backgroundThread != null) {
            backgroundThread.quitSafely();
        }
        super.onDestroy();
    }
}
