/**
 * The list with the item moved to another position.
 */
export function moveItem<T>(list: T[], from: number, to: number): T[] {
  const moved = [...list]
  const [item] = moved.splice(from, 1)

  moved.splice(to, 0, item)

  return moved
}

let keys = 0

/**
 * A key of a new item of a list (a note, a size, a special day), which differs from the ones
 * of drafts kept from before (they're kept over reloads).
 */
export function newKey(): string {
  return `new-${Date.now().toString(36)}-${++keys}`
}
