/** Brand colors of the public pages: the primary color and the color of text on its tints. */
export interface BrandColors {
  primary: string
  content: string
}

export interface BrandPreset extends BrandColors {
  // of its name in translations (`editor.brand.presets.{key}`)
  key: string
}

/** Ready-made colors (from `app.css`), the first one is the public pages' default. */
export const BRAND_PRESETS: BrandPreset[] = [
  {key: 'green', primary: '#3bb517', content: '#284625'},
  {key: 'light_green', primary: '#71d855', content: '#284625'},
  {key: 'blue', primary: '#6db0bb', content: '#295a5a'},
  {key: 'nude', primary: '#cc7a52', content: '#5f4237'},
  {key: 'purple', primary: '#b768b3', content: '#5e0679'},
]

/**
 * Colors of the restaurant: its own ones, or the default ones.
 */
export function brandOf(restaurant: { brand_primary: string | null, brand_primary_content: string | null }): BrandColors {
  return restaurant.brand_primary && restaurant.brand_primary_content
    ? {primary: restaurant.brand_primary, content: restaurant.brand_primary_content}
    : {primary: BRAND_PRESETS[0].primary, content: BRAND_PRESETS[0].content}
}

/**
 * The preset with these colors, null for custom ones.
 */
export function presetOf(colors: BrandColors): BrandPreset | null {
  return BRAND_PRESETS.find((preset) => preset.primary === colors.primary.toLowerCase()
    && preset.content === colors.content.toLowerCase()) ?? null
}

/** Tints of the primary color the public pages put text on (`bg-primary/10`, `/15`, `/20`). */
const TINTS = [0.1, 0.15, 0.2]

/** The lowest contrast of readable text (WCAG AA). */
export const READABLE = 4.5

export function isHex(value: string): boolean {
  return /^#[0-9a-fA-F]{6}$/.test(value)
}

function toRgb(hex: string): number[] {
  return [1, 3, 5].map((index) => parseInt(hex.slice(index, index + 2), 16))
}

function luminance(rgb: number[]): number {
  const [r, g, b] = rgb.map((channel) => {
    const value = channel / 255

    return value <= 0.03928 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4
  })

  return 0.2126 * r + 0.7152 * g + 0.0722 * b
}

function contrast(first: number[], second: number[]): number {
  const lighter = Math.max(luminance(first), luminance(second))
  const darker = Math.min(luminance(first), luminance(second))

  return (lighter + 0.05) / (darker + 0.05)
}

/**
 * Contrast of the text color on the tints of the primary one over white: the lowest one
 * (the same check as the server's `ColorHelper::contrastOnTints()`).
 */
export function contrastOnTints(colors: BrandColors): number {
  const text = toRgb(colors.content)
  const primary = toRgb(colors.primary)

  return Math.min(...TINTS.map((amount) => contrast(primary.map((channel) => 255 + (channel - 255) * amount), text)))
}

/** "8.7" for a contrast of 8.74 (rounded down, so 4.49 isn't shown as readable 4.5). */
export function formatContrast(value: number): string {
  return (Math.floor(value * 10) / 10).toFixed(1)
}

/**
 * The text color made darker till it's readable on the primary tints, null if it can't be.
 */
export function readableContent(colors: BrandColors): string | null {
  const rgb = toRgb(colors.content)

  for (let step = 1; step <= 20; step++) {
    const darker = rgb.map((channel) => Math.round(channel * (1 - step * 0.05)))
    const hex = '#' + darker.map((channel) => channel.toString(16).padStart(2, '0')).join('')

    if (contrastOnTints({primary: colors.primary, content: hex}) >= READABLE) {
      return hex
    }
  }

  return null
}
