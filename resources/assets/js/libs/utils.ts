import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/**
 * IATI Validator severity levels that block an activity from being published.
 * See https://github.com/IATI/iatipublisher/issues/2007
 */
export const PUBLISH_BLOCKING_LEVELS = ['critical', 'error'];

export function isPublishBlocked(topLevelError?: string | null): boolean {
  return !!topLevelError && PUBLISH_BLOCKING_LEVELS.includes(topLevelError);
}
