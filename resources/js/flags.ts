import type {Component} from "vue";
import {Droplet, Dumbbell, Feather, Flame, Leaf, MilkOff, Salad, Sprout, Zap} from "lucide-vue-next";

/**
 * Tags and allergens of dishes, stored in their `flags` (see `App\Enums\ProductFlag`).
 */

/** Groups of tags, in this order. */
export type TagGroup = 'diet' | 'spiciness' | 'nutrition';

export const TAG_GROUPS: TagGroup[] = ['diet', 'spiciness', 'nutrition'];

export interface DishTag {
  /** The flag */
  key: string,
  /** Translation key of the label */
  label: string,
  icon: Component,
  group: TagGroup,
  /** Level of hotness, from 1 (mild) to 4 (extra hot); none for spicy without a level */
  level?: number,
}

/** Tags shown on dishes and offered in search, in this order. Allergens are listed separately. */
export const DISH_TAGS: DishTag[] = [
  {key: 'vegan', label: 'badges.vegan', icon: Leaf, group: 'diet'},
  {key: 'vegetarian', label: 'badges.vegetarian', icon: Salad, group: 'diet'},
  {key: 'lactose-free', label: 'badges.lactose_free', icon: MilkOff, group: 'diet'},
  {key: 'dairy-free', label: 'badges.dairy_free', icon: MilkOff, group: 'diet'},
  {key: 'plant-milk', label: 'badges.plant_milk', icon: Sprout, group: 'diet'},
  {key: 'hotness', label: 'badges.hot', icon: Flame, group: 'spiciness'},
  {key: 'low-hotness', label: 'badges.low_hot', icon: Flame, group: 'spiciness', level: 1},
  {key: 'medium-hotness', label: 'badges.medium_hot', icon: Flame, group: 'spiciness', level: 2},
  {key: 'high-hotness', label: 'badges.high_hot', icon: Flame, group: 'spiciness', level: 3},
  {key: 'extreme-hotness', label: 'badges.extreme_hot', icon: Flame, group: 'spiciness', level: 4},
  {key: 'low-calorie', label: 'badges.low_calorie', icon: Feather, group: 'nutrition'},
  {key: 'high-calorie', label: 'badges.high_calorie', icon: Zap, group: 'nutrition'},
  {key: 'high-protein', label: 'badges.high_protein', icon: Dumbbell, group: 'nutrition'},
  {key: 'low-fat', label: 'badges.low_fat', icon: Droplet, group: 'nutrition'},
  {key: 'high-fat', label: 'badges.high_fat', icon: Droplet, group: 'nutrition'},
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
