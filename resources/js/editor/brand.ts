/**
 * Brand colors of the public pages: the primary color, the color of text on its tints, and the
 * accent of prices.
 */
export interface BrandColors {
  primary: string
  content: string
  accent: string
}

export interface BrandPreset extends BrandColors {
  // of its name in translations (`editor.brand.presets.{key}`)
  key: string
}

/**
 * Ready-made colors (all readable), the first one is the public pages' default (see `app.css`).
 * Their accents are the ones between their two colors.
 */
export const BRAND_PRESETS: BrandPreset[] = ([
  {key: 'green', primary: '#3bb517', content: '#284625'},
  {key: 'light_green', primary: '#71d855', content: '#284625'},
  {key: 'olive', primary: '#8a9a3b', content: '#3d4415'},
  {key: 'teal', primary: '#14a3a3', content: '#0b4d4d'},
  {key: 'blue', primary: '#6db0bb', content: '#295a5a'},
  {key: 'ocean', primary: '#2f7fd1', content: '#173f6b'},
  {key: 'lavender', primary: '#9b8cdb', content: '#3b2f73'},
  {key: 'purple', primary: '#b768b3', content: '#5e0679'},
  {key: 'rose', primary: '#e5739b', content: '#6e1f3d'},
  {key: 'burgundy', primary: '#a3324a', content: '#5a1424'},
  {key: 'coral', primary: '#f0776a', content: '#7a2a22'},
  {key: 'terracotta', primary: '#d9603b', content: '#6b2412'},
  {key: 'nude', primary: '#cc7a52', content: '#5f4237'},
  {key: 'saffron', primary: '#e0a526', content: '#5c4108'},
  {key: 'espresso', primary: '#8b5e3c', content: '#3f2a1a'},
  {key: 'charcoal', primary: '#6b7280', content: '#1f2937'},
] as Omit<BrandPreset, 'accent'>[]).map((preset) => ({...preset, accent: accentOf(preset.primary, preset.content)}))

/** The orange of allergens on the public pages. */
const ALLERGEN_ORANGE = '#ca3500'

/**
 * Colors of the restaurant: its own ones, or the default ones, and its accent, or the one between
 * those two.
 */
export function brandOf(restaurant: {
  brand_primary: string | null,
  brand_primary_content: string | null,
  brand_accent?: string | null,
}): BrandColors {
  const {primary, content} = restaurant.brand_primary && restaurant.brand_primary_content
    ? {primary: restaurant.brand_primary, content: restaurant.brand_primary_content}
    : BRAND_PRESETS[0]

  return {primary, content, accent: restaurant.brand_accent ?? accentOf(primary, content)}
}

/**
 * The preset with these colors, null for custom ones.
 */
export function presetOf(colors: BrandColors): BrandPreset | null {
  return BRAND_PRESETS.find((preset) => preset.primary === colors.primary.toLowerCase()
    && preset.content === colors.content.toLowerCase()
    && preset.accent === colors.accent.toLowerCase()) ?? null
}

/**
 * The accent of prices, which is the color between the text color and the primary one (`app.css`
 * mixes the same one, when the restaurant has none): lighter than the text, darker than the primary.
 */
export function accentOf(primary: string, content: string): string {
  const [first, second] = [toRgb(primary), toRgb(content)]

  return toHex(first.map((channel, index) => Math.round((channel + second[index]) / 2)))
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

function toHex(rgb: number[]): string {
  return '#' + rgb.map((channel) => channel.toString(16).padStart(2, '0')).join('')
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
export function contrastOnTints(colors: Pick<BrandColors, 'primary' | 'content'>): number {
  const text = toRgb(colors.content)
  const primary = toRgb(colors.primary)

  return Math.min(...TINTS.map((amount) => contrast(primary.map((channel) => 255 + (channel - 255) * amount), text)))
}

/** "8.7" for a contrast of 8.74 (rounded down, so 4.49 isn't shown as readable 4.5). */
export function formatContrast(value: number): string {
  return (Math.floor(value * 10) / 10).toFixed(1)
}

/** The color made darker step by step till it's readable, null if it can't be. */
function darkerTill(hex: string, readable: (darker: string) => boolean): string | null {
  const rgb = toRgb(hex)

  for (let step = 1; step <= 20; step++) {
    const darker = toHex(rgb.map((channel) => Math.round(channel * (1 - step * 0.05))))

    if (readable(darker)) {
      return darker
    }
  }

  return null
}

/**
 * The text color made darker till it's readable on the primary tints, null if it can't be.
 */
export function readableContent(colors: BrandColors): string | null {
  return darkerTill(colors.content, (content) => contrastOnTints({primary: colors.primary, content}) >= READABLE)
}

/** The background of the menu list, which prices are on. */
const LIST_BACKGROUND = [249, 249, 249]

/** The lowest contrast of prices (large bold text, WCAG AA). */
export const PRICES_READABLE = 3

/**
 * Contrast of prices in the accent color on the menu list (the same check as the server's
 * `ColorHelper::contrastOnList()`).
 */
export function contrastOnList(accent: string): number {
  return contrast(toRgb(accent), LIST_BACKGROUND)
}

/**
 * The accent color made darker till prices in it are readable, null if it can't be.
 */
export function readableAccent(accent: string): string | null {
  return darkerTill(accent, (darker) => contrastOnList(darker) >= PRICES_READABLE)
}

/** Hue (degrees) and saturation (0–1) of the color. */
function hueAndSaturation(hex: string): { hue: number, saturation: number } {
  const [r, g, b] = toRgb(hex).map((channel) => channel / 255)
  const max = Math.max(r, g, b)
  const min = Math.min(r, g, b)
  const delta = max - min
  const lightness = (max + min) / 2

  if (!delta) {
    return {hue: 0, saturation: 0}
  }

  const hue = max === r ? ((g - b) / delta) % 6 : (max === g ? (b - r) / delta + 2 : (r - g) / delta + 4)

  return {
    hue: (hue * 60 + 360) % 360,
    saturation: delta / (1 - Math.abs(2 * lightness - 1)),
  }
}

/**
 * Whether the primary color is close to the orange of allergens (a hue within about 12° of it,
 * and as vivid): allergen labels stand out less on the menu. It's allowed still.
 */
export function nearAllergens(colors: BrandColors): boolean {
  if (!isHex(colors.primary)) {
    return false
  }

  const color = hueAndSaturation(colors.primary)
  const orange = hueAndSaturation(ALLERGEN_ORANGE)
  const distance = Math.abs(color.hue - orange.hue)

  return Math.min(distance, 360 - distance) <= 12 && color.saturation >= 0.5
}
