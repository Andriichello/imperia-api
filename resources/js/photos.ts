import type {Media} from "@/api";

/**
 * A photo's WebP: the one of its full size, or its largest smaller copy (see `MakeWebP` of the
 * API), or the photo itself, until it has them.
 */
export function photoUrl(photo: Media): string {
  const webps = (photo.variants ?? []).filter((variant) => variant.extension === 'webp');
  const largest = [...webps].sort((a, b) => (b.width ?? Infinity) - (a.width ?? Infinity))[0];

  return (largest ?? photo).url;
}

/**
 * Attributes of an `<img>` of a photo: the browser loads the smallest of its copies, which is
 * sharp at the width it's shown (the `sizes` of the `<img>`), on the screen it's on.
 */
export function photoSources(photo: Media): { src: string, srcset?: string } {
  const copies = (photo.variants ?? []).filter((variant) => variant.extension === 'webp' && variant.width);

  return {
    src: photoUrl(photo),
    srcset: copies.length
      ? copies.map((copy) => `${copy.url} ${copy.width}w`).join(', ')
      : undefined,
  };
}

/**
 * Whether the photo is wider than it's tall, when its copies say it (they're of its shape).
 */
export function isWidePhoto(photo: Media): boolean | null {
  const copy = (photo.variants ?? []).find((variant) => variant.width && variant.height);

  return copy ? (copy.width as number) > (copy.height as number) : null;
}
