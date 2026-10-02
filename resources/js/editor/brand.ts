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
