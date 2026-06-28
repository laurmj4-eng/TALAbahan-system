import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const PULL_THRESHOLD = 75
const MAX_PULL = 90
const DAMPING = 0.5
const START_THRESHOLD = 10

export function usePullToRefresh() {
  const pullY = ref(0)
  const visible = ref(false)
  const refreshing = ref(false)
  const activated = ref(false)

  let startY = 0
  let tracking = false
  let touchId = null

  function onTouchStart(e) {
    if (refreshing.value) return
    const scrollContainer = document.querySelector('.smooth-scroll-container') || document.documentElement
    if (scrollContainer.scrollTop > 0) return

    const touch = e.changedTouches[0]
    startY = touch.clientY
    touchId = touch.identifier
    tracking = true
    activated.value = false
    pullY.value = 0
  }

  function onTouchMove(e) {
    if (!tracking || refreshing.value) return

    const touch = findTouch(e.changedTouches)
    if (!touch) return

    const dy = touch.clientY - startY
    if (dy <= 0) {
      pullY.value = 0
      activated.value = false
      visible.value = false
      return
    }

    const damped = Math.min(dy * DAMPING, MAX_PULL)
    pullY.value = damped
    activated.value = damped >= PULL_THRESHOLD
    visible.value = damped > START_THRESHOLD

    if (dy > 0) {
      e.preventDefault()
    }
  }

  function onTouchEnd(e) {
    if (!tracking || refreshing.value) return

    const touch = findTouch(e.changedTouches)
    if (!touch) return

    tracking = false

    if (activated.value) {
      refreshing.value = true
      pullY.value = PULL_THRESHOLD
      triggerRefresh()
    } else {
      pullY.value = 0
      setTimeout(() => {
        if (!refreshing.value) {
          visible.value = false
        }
      }, 300)
    }
  }

  function onTouchCancel() {
    tracking = false
    if (!refreshing.value) {
      pullY.value = 0
      activated.value = false
      setTimeout(() => {
        if (!refreshing.value) {
          visible.value = false
        }
      }, 300)
    }
  }

  function findTouch(touches) {
    for (let i = 0; i < touches.length; i++) {
      if (touches[i].identifier === touchId) {
        return touches[i]
      }
    }
    return null
  }

  function triggerRefresh() {
    try {
      router.reload()
    } catch {
      window.location.reload()
    }
  }

  function onPageShow() {
    refreshing.value = false
    activated.value = false
    pullY.value = 0
    visible.value = false
    tracking = false
  }

  onMounted(() => {
    document.addEventListener('touchstart', onTouchStart, { passive: true })
    document.addEventListener('touchmove', onTouchMove, { passive: false })
    document.addEventListener('touchend', onTouchEnd, { passive: true })
    document.addEventListener('touchcancel', onTouchCancel, { passive: true })
    window.addEventListener('pageshow', onPageShow)
  })

  onUnmounted(() => {
    document.removeEventListener('touchstart', onTouchStart)
    document.removeEventListener('touchmove', onTouchMove)
    document.removeEventListener('touchend', onTouchEnd)
    document.removeEventListener('touchcancel', onTouchCancel)
    window.removeEventListener('pageshow', onPageShow)
  })

  return { pullY, visible, refreshing, activated }
}
