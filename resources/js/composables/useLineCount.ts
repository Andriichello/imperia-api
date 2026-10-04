import { onBeforeUnmount, onMounted, ref, watch, type Ref } from 'vue'

/**
 * Number of lines of the element's text: its line boxes, told apart by their tops. Lines clipped
 * by a max-height are counted too, so it's right while the text is clamped.
 */
export function countLines(el: HTMLElement): number {
  const range = document.createRange()
  range.selectNodeContents(el)
  const lh = parseFloat(getComputedStyle(el).lineHeight) || 22
  const tops: number[] = []
  for (const r of Array.from(range.getClientRects())) {
    if (r.width < 1) continue
    if (!tops.some((t) => Math.abs(t - r.top) < lh / 2)) tops.push(r.top)
  }
  return tops.length
}

/**
 * Number of lines of the element's text, measured again whenever its parent's size changes (the
 * screen turns, a photo beside it…). An element shown later is measured too, none has 0 lines.
 */
export function useLineCount(el: Ref<HTMLElement | null>) {
  const lines = ref(0)
  let ro: ResizeObserver | undefined
  let unmounted = false
  const measure = () => { lines.value = el.value ? countLines(el.value) : 0 }
  const observe = () => {
    ro?.disconnect()
    if (el.value?.parentElement) ro?.observe(el.value.parentElement)
    measure()
  }
  onMounted(async () => {
    await document.fonts?.ready
    if (unmounted) return
    ro = new ResizeObserver(measure)
    observe()
  })
  // e.g. a description added in the editor's preview
  watch(el, () => { if (ro) observe() }, { flush: 'post' })
  onBeforeUnmount(() => { unmounted = true; ro?.disconnect() })
  return { lines, measure }
}
