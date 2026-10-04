import type {Component} from "vue";
import {createLucideIcon, Droplet, Dumbbell, Feather, Leaf, MilkOff, Salad, Sprout, Star, Zap} from "lucide-vue-next";

/**
 * Tags and allergens of dishes, stored in their `flags` (see `App\Enums\ProductFlag`).
 */

/** A chili pepper: spicy dishes (a flame is their calories). */
export const Chili = createLucideIcon('chili', [
  ['path', {d: 'M14 7c0-2 1.2-3.6 3-4', key: 'stem'}],
  ['path', {d: 'M11 7.5c2-1.3 5-1 6 1.5.8 2-.2 5-2.5 7.5C12 19 8 20.5 4 21c2.5-2 4-4.5 4.8-7.5.5-2 .8-4.6 2.2-6z', key: 'pod'}],
]);

/** Groups of tags, in this order. */
export type TagGroup = 'highlight' | 'diet' | 'spiciness' | 'nutrition';

export const TAG_GROUPS: TagGroup[] = ['highlight', 'diet', 'spiciness', 'nutrition'];

/**
 * Colors of a tag's circle on the dish card and page: popular, spicy, plant-based, dairy,
 * nutrition, and the one of allergens (one circle for all of them on the card).
 */
export type TagTone = 'hit' | 'spicy' | 'veg' | 'nolactose' | 'protein' | 'allergen';

export const TAG_TONES: Record<TagTone, { bg: string, fg: string }> = {
  hit: {bg: '#FDF1D8', fg: '#8A5A00'},
  spicy: {bg: '#FDE9E5', fg: '#A3261A'},
  veg: {bg: '#E5F1E1', fg: '#2C6425'},
  nolactose: {bg: '#E3EDF8', fg: '#1E4D86'},
  protein: {bg: '#E9E8F2', fg: '#3D3A6B'},
  allergen: {bg: '#FDEAD7', fg: '#C2410C'},
};

export interface DishTag {
  /** The flag */
  key: string,
  /** Translation key of the label */
  label: string,
  icon: Component,
  group: TagGroup,
  tone: TagTone,
  /** Level of hotness, from 1 (mild) to 4 (extra hot); none for spicy without a level */
  level?: number,
}

/** Tags shown on dishes and offered in search, in this order. Allergens are listed separately. */
export const DISH_TAGS: DishTag[] = [
  {key: 'hit', label: 'badges.hit', icon: Star, group: 'highlight', tone: 'hit'},
  {key: 'vegan', label: 'badges.vegan', icon: Leaf, group: 'diet', tone: 'veg'},
  {key: 'vegetarian', label: 'badges.vegetarian', icon: Salad, group: 'diet', tone: 'veg'},
  {key: 'lactose-free', label: 'badges.lactose_free', icon: MilkOff, group: 'diet', tone: 'nolactose'},
  {key: 'dairy-free', label: 'badges.dairy_free', icon: MilkOff, group: 'diet', tone: 'nolactose'},
  {key: 'plant-milk', label: 'badges.plant_milk', icon: Sprout, group: 'diet', tone: 'nolactose'},
  {key: 'hotness', label: 'badges.hot', icon: Chili, group: 'spiciness', tone: 'spicy'},
  {key: 'low-hotness', label: 'badges.low_hot', icon: Chili, group: 'spiciness', tone: 'spicy', level: 1},
  {key: 'medium-hotness', label: 'badges.medium_hot', icon: Chili, group: 'spiciness', tone: 'spicy', level: 2},
  {key: 'high-hotness', label: 'badges.high_hot', icon: Chili, group: 'spiciness', tone: 'spicy', level: 3},
  {key: 'extreme-hotness', label: 'badges.extreme_hot', icon: Chili, group: 'spiciness', tone: 'spicy', level: 4},
  {key: 'low-calorie', label: 'badges.low_calorie', icon: Feather, group: 'nutrition', tone: 'protein'},
  {key: 'high-calorie', label: 'badges.high_calorie', icon: Zap, group: 'nutrition', tone: 'protein'},
  {key: 'high-protein', label: 'badges.high_protein', icon: Dumbbell, group: 'nutrition', tone: 'protein'},
  {key: 'low-fat', label: 'badges.low_fat', icon: Droplet, group: 'nutrition', tone: 'protein'},
  {key: 'high-fat', label: 'badges.high_fat', icon: Droplet, group: 'nutrition', tone: 'protein'},
];

/** Hotness flags: spicy, then its levels from the mildest. */
export const HOTNESS: string[] = DISH_TAGS.filter((tag) => tag.group === 'spiciness').map((tag) => tag.key);

/** Low and high of the same thing: a dish has one of them at most. */
export const OPPOSITE_TAGS: Record<string, string> = {
  'low-calorie': 'high-calorie',
  'high-calorie': 'low-calorie',
  'low-fat': 'high-fat',
  'high-fat': 'low-fat',
};

/** Tags of the group, in their order. */
export function tagsOf(group: TagGroup): DishTag[] {
  return DISH_TAGS.filter((tag) => tag.group === group);
}

/** The tag of the flag, none for an allergen or an unknown flag. */
export function findTag(key: string): DishTag | null {
  return DISH_TAGS.find((tag) => tag.key === key) ?? null;
}

/**
 * Flags with the tag picked: one level of hotness at most (spicy without a level is one of them
 * too), and either low or high of the same thing.
 */
export function withTag(flags: string[], key: string): string[] {
  const dropped = HOTNESS.includes(key) ? HOTNESS : [OPPOSITE_TAGS[key]];

  return [...flags.filter((flag) => flag !== key && !dropped.includes(flag)), key];
}

/**
 * Whether a dish with given flags matches the tag in search: spicy is any level of hotness,
 * and vegan dishes are vegetarian, dairy-free and lactose-free too (dairy-free ones are lactose-free).
 */
export function matchesTag(flags: string[] | null | undefined, key: string): boolean {
  const has = (flag: string) => !!flags?.includes(flag);

  switch (key) {
    case 'hotness':
      return HOTNESS.some(has);
    case 'vegetarian':
      return has('vegetarian') || has('vegan');
    case 'dairy-free':
      return has('dairy-free') || has('vegan');
    case 'lactose-free':
      return has('lactose-free') || has('dairy-free') || has('vegan');
    default:
      return has(key);
  }
}

/** Tags of a dish with given flags: one of hotness, its hottest level if it has several. */
export function getDishTags(flags: string[] | null | undefined): DishTag[] {
  const hottest = [...HOTNESS].reverse().find((flag) => flags?.includes(flag));

  return DISH_TAGS.filter((tag) => flags?.includes(tag.key) && (tag.group !== 'spiciness' || tag.key === hottest));
}

/** Allergen flags in the order they are listed in. */
export const ALLERGENS: string[] = [
  'alg-wheat',
  'alg-milk',
  'alg-eggs',
  'alg-fish',
  'alg-shellfish',
  'alg-nuts',
  'alg-peanuts',
  'alg-sesame',
  'alg-seeds',
  'alg-soy',
  'alg-celery',
];

/** Allergen flags (`alg-…`) of a dish, in their order. */
export function getAllergens(flags: string[] | null | undefined): string[] {
  return ALLERGENS.filter((flag) => flags?.includes(flag));
}

/** Translation key of an allergen flag's label. */
export function getAllergenLabel(flag: string): string {
  return `badges.${flag.replace('alg-', '')}`;
}
