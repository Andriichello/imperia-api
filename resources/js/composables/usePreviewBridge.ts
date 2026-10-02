import {onBeforeUnmount, onMounted, Ref} from 'vue'
import {EditorMessage, isBridgeMessage, PreviewMessage} from '@/editor/protocol'

/**
 * The editor's side of the preview: messages to the public page in the frame and from it,
 * only from that frame of the same origin.
 *
 * @param frame The preview's iframe
 * @param onMessage Handles messages of the public page
 */
export function usePreviewBridge(frame: Ref<HTMLIFrameElement | null>, onMessage: (message: PreviewMessage) => void) {
  function post(message: EditorMessage): void {
    // lost while the page loads: it asks for everything again when it's ready.
    // A plain copy: reactive values of the store can't be sent.
    frame.value?.contentWindow?.postMessage(JSON.parse(JSON.stringify(message)), window.location.origin)
  }

  function receive(event: MessageEvent): void {
    if (event.origin !== window.location.origin || !frame.value || event.source !== frame.value.contentWindow) {
      return
    }

    if (isBridgeMessage<PreviewMessage>(event.data)) {
      onMessage(event.data)
    }
  }

  onMounted(() => window.addEventListener('message', receive))
  onBeforeUnmount(() => window.removeEventListener('message', receive))

  return {post}
}
