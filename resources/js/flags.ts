import type {Component} from "vue";
import {Droplet, DropletOff, Dumbbell, Flame, Leaf, Milk, MilkOff, Salad, Vegan} from "lucide-vue-next";

/**
 * Tags and allergens of dishes, stored in their `flags` (see `App\Enums\ProductFlag`).
 */

export interface DishTag {
  /** The flag, or `hotness` for any level of hotness */
  key: string,
  /** Translation key of the label */
  label: string,
  icon: Component,
}

/** Hotness flags from the hottest, with their labels. */
const HOTNESS_LABELS: [string, string][] = [
  ['extreme-hotness', 'badges.extreme_hot'],
  ['high-hotness', 'badges.high_hot'],
  ['medium-hotness', 'badges.medium_hot'],
  ['low-hotness', 'badges.low_hot'],
  ['hotness', 'badges.hot'],
];

/** Tags shown on dishes and offered in search, in this order. Allergens are listed separately. */
export const DISH_TAGS: DishTag[] = [
  {key: 'vegan', label: 'badges.vegan', icon: Vegan},
  {key: 'low-calorie', label: 'badges.low_calorie', icon: Salad},
  {key: 'vegetarian', label: 'badges.vegetarian', icon: Leaf},
  {key: 'hotness', label: 'badges.hot', icon: Flame},
  {key: 'lactose-free', label: 'badges.lactose_free', icon: Milk},
  {key: 'dairy-free', label: 'badges.dairy_free', icon: MilkOff},
  {key: 'plant-milk', label: 'badges.plant_milk', icon: Milk},
  {key: 'high-calorie', label: 'badges.high_calorie', icon: Flame},
  {key: 'high-protein', label: 'badges.high_protein', icon: Dumbbell},
  {key: 'low-fat', label: 'badges.low_fat', icon: DropletOff},
  {key: 'high-fat', label: 'badges.high_fat', icon: Droplet},
];

/** Whether a dish with given flags has the tag. */
export function hasTag(flags: string[] | null | undefined, key: string): boolean {
  if (key === 'hotness') {
    return HOTNESS_LABELS.some(([flag]) => flags?.includes(flag));
  }

  return !!flags?.includes(key);
}

/** Whether a dish with given flags matches the tag in search (vegan dishes are vegetarian too). */
export function matchesTag(flags: string[] | null | undefined, key: string): boolean {
  return hasTag(flags, key) || (key === 'vegetarian' && hasTag(flags, 'vegan'));
}

/** Tags of a dish with given flags, labelled by its level of hotness. */
export function getDishTags(flags: string[] | null | undefined): DishTag[] {
  return DISH_TAGS
    .filter((tag) => hasTag(flags, tag.key))
    .map((tag) => tag.key === 'hotness'
      ? {...tag, label: HOTNESS_LABELS.find(([flag]) => flags?.includes(flag))![1]}
      : tag);
}

/** Allergen flags (`alg-…`) of a dish. */
export function getAllergens(flags: string[] | null | undefined): string[] {
  return (flags ?? []).filter((flag) => flag.startsWith('alg-'));
}

/** Translation key of an allergen flag's label. */
export function getAllergenLabel(flag: string): string {
  return `badges.${flag.replace('alg-', '')}`;
}
