<template>
  <div v-if="visible" class="fixed top-0 left-0 right-0 z-[9999] bg-gradient-to-r from-amber-600 to-orange-600 text-white px-4 py-3 shadow-xl flex items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-sm">
      <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
      <span>
        New version <strong>{{ latestVersion }}</strong> available
        <span class="opacity-75 ml-1">(installed: {{ currentVersion }})</span>
      </span>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <button @click="dismiss" class="text-white/70 hover:text-white text-xs px-2 py-1">Later</button>
      <a :href="apkUrl" @click.prevent="download"
         class="bg-white text-orange-700 font-semibold text-xs px-4 py-1.5 rounded-full shadow hover:bg-orange-50 active:scale-95 transition-all">
        Download
      </a>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const visible = ref(false)
const currentVersion = ref(0)
const latestVersion = ref(0)
const apkUrl = ref('')

async function check () {
  if (typeof window.AndroidBridge === 'undefined') return

  const installed = window.AndroidBridge.getAppVersionCode()
  currentVersion.value = installed

  try {
    const res = await fetch('/version.json')
    const data = await res.json()
    latestVersion.value = data.versionCode

    if (data.versionCode > installed) {
      apkUrl.value = data.apkUrl
      visible.value = true
    }
  } catch (e) {
    // silently ignore
  }
}

function download () {
  if (apkUrl.value && typeof window.AndroidBridge !== 'undefined') {
    window.AndroidBridge.openInBrowser(apkUrl.value)
  }
  dismiss()
}

function dismiss () {
  visible.value = false
}

onMounted(check)
</script>
