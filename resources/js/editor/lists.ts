/**
 * The list with the item moved to another position.
 */
export function moveItem<T>(list: T[], from: number, to: number): T[] {
  const moved = [...list]
  const [item] = moved.splice(from, 1)

  moved.splice(to, 0, item)

  return moved
}
