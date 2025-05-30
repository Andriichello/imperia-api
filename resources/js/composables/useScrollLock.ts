import { ref } from 'vue'

export function useScrollLock() {
  // Store the scroll position
  const scrollPosition = ref(0)

  // Store the target element reference
  let targetElement: HTMLElement | null = null

  // Function to disable scrolling
  const disableScroll = (element: HTMLElement) => {
    targetElement = element

    // Store current scroll position before disabling
    scrollPosition.value = element.scrollTop

    // Apply styles to disable scrolling
    const originalStyles = {
      overflow: element.style.overflow,
      position: element.style.position,
      height: element.style.height,
    }

    // Store original styles on the element for later restoration
    element.dataset.originalStyles = JSON.stringify(originalStyles)

    // Disable scrolling
    element.style.overflow = 'hidden'
    element.style.position = 'relative'
    element.style.height = `${element.offsetHeight}px`
  }

  // Function to enable scrolling again
  const enableScroll = () => {
    if (!targetElement) return

    // Restore original styles
    if (targetElement.dataset.originalStyles) {
      const originalStyles = JSON.parse(targetElement.dataset.originalStyles)
      Object.entries(originalStyles).forEach(([key, value]) => {
        // @ts-ignore - dynamic property assignment
        targetElement!.style[key] = value as string
      })
      delete targetElement.dataset.originalStyles
    }

    // Restore scroll position
    targetElement.scrollTop = scrollPosition.value

    // Clear the reference
    targetElement = null
  }

  return {
    disableScroll,
    enableScroll,
    scrollPosition
  }
}
