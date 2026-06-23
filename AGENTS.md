# Auto-Update APK Process

## How it works
Every push to `main` triggers the CI workflow (`.github/workflows/android-build.yml`) which:
1. Builds the debug APK
2. Uploads APK as a workflow artifact (for manual download)
3. Uploads APK to a GitHub Release tagged `latest` at:
   `https://github.com/laurmj4-eng/TALAbahan-system/releases/download/latest/app-debug.apk`

The app reads `BASE_URL/version.json` on startup. If `versionCode` is higher than the installed version, it downloads and installs the APK automatically.

## To release a new version
1. Bump `versionCode` in `android-shell/app/build.gradle`
2. Update `versionCode` in `public/version.json` to match
3. Push to GitHub

The CI will build the APK and upload it to the `latest` release. The app will auto-update on next launch.

## Quick install via USB (no auto-update)
1. Download APK from GitHub Actions → Artifacts → TALAbahan-app
2. Extract `app-debug.apk` next to `install-apk.bat`
3. Plug phone via USB with USB debugging enabled
4. Double-click `install-apk.bat`

## Files
- `public/version.json` — version metadata served to the app
- `.github/workflows/android-build.yml` — CI pipeline
- `install-apk.bat` — USB install helper
- `install-test.ps1` — auto-download + install (needs GH_TOKEN)
