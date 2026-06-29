import { ref, onMounted, onUnmounted } from 'vue'

const PULL_THRESHOLD = 40
const MAX_PULL = 45
const DAMPING = 0.5
const START_THRESHOLD = 0
const INTENT_THRESHOLD = 15

export function usePullToRefresh() {
  const pullY = ref(0)
  const visible = ref(false)
  const refreshing = ref(false)
  const activated = ref(false)

  let startY = 0
  let tracking = false
  let touchId = null
  let isRefreshEligible = false
  let lastY = 0

  function onTouchStart(e) {
    if (refreshing.value) return
    const scrollContainer = document.querySelector('.smooth-scroll-container') || document.documentElement
    const isAtTop = scrollContainer.scrollTop === 0

    const touch = e.changedTouches[0]
    startY = touch.clientY
    lastY = startY
    touchId = touch.identifier
    tracking = true
    isRefreshEligible = isAtTop
    activated.value = false
    pullY.value = 0
  }

  function onTouchMove(e) {
    if (!tracking || refreshing.value || !isRefreshEligible) return

    const touch = findTouch(e.changedTouches)
    if (!touch) return

    const currentY = touch.clientY
    const pullDistance = currentY - startY

    if (currentY < lastY || pullDistance <= 0) {
      isRefreshEligible = false
      pullY.value = 0
      visible.value = false
      activated.value = false
      lastY = currentY
      return
    }

    if (pullDistance < INTENT_THRESHOLD) {
      lastY = currentY
      return
    }

    const damped = Math.min(pullDistance * DAMPING, MAX_PULL)
    pullY.value = damped
    activated.value = damped >= PULL_THRESHOLD
    visible.value = true
    e.preventDefault()
    lastY = currentY
  }

  function onTouchEnd(e) {
    if (!tracking || refreshing.value) return

    const touch = findTouch(e.changedTouches)
    if (!touch) return

    tracking = false

    if (isRefreshEligible && activated.value) {
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

    isRefreshEligible = false
  }

  function onTouchCancel() {
    tracking = false
    isRefreshEligible = false
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
    setTimeout(() => {
      window.location.reload()
    }, 400)
  }

  function onPageShow() {
    refreshing.value = false
    activated.value = false
    pullY.value = 0
    visible.value = false
    tracking = false
    isRefreshEligible = false
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
