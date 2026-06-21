package com.mjseafood.app;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.Window;
import android.view.WindowManager;
import android.webkit.CookieManager;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ProgressBar;
import android.widget.Toast;

import androidx.core.content.FileProvider;

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
    private final Handler mainHandler = new Handler(Looper.getMainLooper());
    private static final String BASE_URL = "https://talabahan-system-1.onrender.com";
    private static final String SITE_URL = BASE_URL + "/?auth_mode=mobile";
    private static final String VERSION_URL = BASE_URL + "/version.json";
    private static final String MOBILE_AUTH_URL = BASE_URL + "/auth/mobile-login";
    private static final String AUTHORITY = "com.mjseafood.app.fileprovider";
    private static final String DEEP_LINK_SCHEME = "talabahan://auth";
    private static final String[] EXTERNAL_AUTH_HOSTS = {
        "accounts.google.com",
        "google.com",
        "googleapis.com",
        "gstatic.com"
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        requestWindowFeature(Window.FEATURE_NO_TITLE);
        getWindow().setFlags(
                WindowManager.LayoutParams.FLAG_FULLSCREEN,
                WindowManager.LayoutParams.FLAG_FULLSCREEN
        );

        setContentView(R.layout.activity_main);

        webView = findViewById(R.id.webview);
        progressBar = findViewById(R.id.progress_bar);
        setupWebView();

        if (!handleDeepLinkIntent(getIntent())) {
            webView.loadUrl(SITE_URL);
        }
        
        checkForUpdates();
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleDeepLinkIntent(intent);
    }

    private boolean handleDeepLinkIntent(Intent intent) {
        if (intent == null || intent.getData() == null) return false;

        Uri data = intent.getData();
        if ("talabahan".equals(data.getScheme()) && "auth".equals(data.getHost())) {
            String redirectTo = data.getQueryParameter("redirect");
            if (redirectTo != null && !redirectTo.isEmpty()) {
                webView.loadUrl(redirectTo);
            } else {
                webView.loadUrl(SITE_URL);
            }
            intent.setData(null);
            return true;
        }
        return false;
    }

    private void checkForUpdates() {
        new Thread(() -> {
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

                int currentVersion = 1;
                try {
                    currentVersion = getPackageManager().getPackageInfo(getPackageName(), 0).versionCode;
                } catch (Exception e) {
                    e.printStackTrace();
                }
                
                if (remoteVersion > currentVersion) {
                    mainHandler.post(() -> downloadUpdate(apkUrl));
                }
            } catch (Exception ignored) {
            }
        }).start();
    }

    private void downloadUpdate(String apkUrl) {
        Toast.makeText(this, "New update available. Downloading...", Toast.LENGTH_LONG).show();

        new Thread(() -> {
            try {
                URL url = new URL(apkUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.connect();

                File apkFile = new File(getCacheDir(), "talabahan-update.apk");
                InputStream input = new BufferedInputStream(url.openStream());
                OutputStream output = new FileOutputStream(apkFile);

                byte[] data = new byte[1024];
                int count;
                while ((count = input.read(data)) != -1) {
                    output.write(data, 0, count);
                }

                output.flush();
                output.close();
                input.close();

                mainHandler.post(() -> installApk(apkFile));
            } catch (Exception e) {
                e.printStackTrace();
                mainHandler.post(() -> Toast.makeText(MainActivity.this, "Update download failed.", Toast.LENGTH_SHORT).show());
            }
        }).start();
    }

    private void installApk(File apkFile) {
        try {
            Uri apkUri = FileProvider.getUriForFile(this, AUTHORITY, apkFile);
            Intent intent = new Intent(Intent.ACTION_VIEW);
            intent.setDataAndType(apkUri, "application/vnd.android.package-archive");
            intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
            startActivity(intent);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void setupWebView() {
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setAllowFileAccess(true);
        settings.setLoadWithOverviewMode(true);
        settings.setUseWideViewPort(true);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);
        settings.setMediaPlaybackRequiresUserGesture(false);

        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        cookieManager.setAcceptThirdPartyCookies(webView, true);

        webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                if (isExternalAuthUrl(url)) {
                    String mobileAuthRedirect = MOBILE_AUTH_URL + "?redirect_uri=" + Uri.encode(DEEP_LINK_SCHEME);
                    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(mobileAuthRedirect));
                    startActivity(intent);
                    return true;
                }
                if (url.startsWith(BASE_URL)) {
                    if (!url.contains("auth_mode=mobile")) {
                        String separator = url.contains("?") ? "&" : "?";
                        url = url + separator + "auth_mode=mobile";
                    }
                    return false;
                }
                return true;
            }

            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                progressBar.setVisibility(View.VISIBLE);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
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
        });

        webView.setWebChromeClient(new WebChromeClient());
        webView.setOverScrollMode(View.OVER_SCROLL_NEVER);
    }

    private boolean isExternalAuthUrl(String url) {
        String lower = url.toLowerCase();
        for (String host : EXTERNAL_AUTH_HOSTS) {
            if (lower.contains(host)) {
                return true;
            }
        }
        return false;
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
        webView.onResume();
        CookieManager.getInstance().flush();
    }

    @Override
    protected void onPause() {
        webView.onPause();
        super.onPause();
    }

    @Override
    protected void onDestroy() {
        webView.destroy();
        super.onDestroy();
    }
}
