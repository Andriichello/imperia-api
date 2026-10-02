import {EDIT_KEY_ATTRIBUTE} from '@/editor/protocol'

/**
 * Whether the public page is shown in the editor's preview: framed, with `?editor=1`.
 */
export const isEditorPreview: boolean = window.self !== window.top
  && new URLSearchParams(window.location.search).get('editor') === '1'

/**
 * Attributes of a part of the public page, which the editor can select (`v-bind="editKey('notes')"`).
 * Only in the preview: guests get none, so nothing changes for them.
 *
 * @param key "photos", "details", "notes", "menus", "hours", "contact", "menu-tabs",
 *            "menu:{id}", "category:{id}" or "dish:{id}"
 */
export function editKey(key: string): Record<string, string | number> {
  if (!isEditorPreview) {
    return {}
  }

  // focusable, so it can be selected with Tab and Enter too
  return {[EDIT_KEY_ATTRIBUTE]: key, tabindex: 0}
}
