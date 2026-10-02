import { onUnmounted, watch } from 'vue'

/**
 * Page scroll lock shared by the drawers: the page doesn't scroll while any of
 * them is open, and gets back to the same position when the last one closes.
 */

let lockCount = 0
let savedScrollY = 0

function lockScroll(): void {
  if (lockCount === 0) {
    savedScrollY = window.scrollY
    Object.assign(document.body.style, {
      position: 'fixed',
      top: `-${savedScrollY}px`,
      left: '0',
      right: '0',
      width: '100%',
      overflow: 'hidden',
    })
  }

  lockCount++
}

function unlockScroll(): void {
  if (lockCount === 0) {
    return
  }

  lockCount--

  if (lockCount === 0) {
    Object.assign(document.body.style, {
      position: '',
      top: '',
      left: '',
      right: '',
      width: '',
      overflow: '',
    })

    // Restore the previous scroll position
    window.scrollTo({ top: savedScrollY })
  }
}

/**
 * Lock the page scroll while `isLocked` returns true (and unlock it on unmount).
 *
 * @param isLocked Getter, e.g. `() => props.open`
 */
export function useScrollLock(isLocked: () => boolean): void {
  let locked = false

  const update = (lock: boolean) => {
    if (lock && !locked) {
      lockScroll()
    }

    if (!lock && locked) {
      unlockScroll()
    }

    locked = lock
  }

  watch(isLocked, update, { immediate: true })
  onUnmounted(() => update(false))
}
