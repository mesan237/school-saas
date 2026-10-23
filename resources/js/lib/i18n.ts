import fr from '../../../lang/fr.json';

/*
 * Translations share Laravel's lang/<locale>.json files, keyed by the English source text.
 * English needs no dictionary: a missing key renders the key itself, which is readable English.
 */

export const supportedLocales = ['fr', 'en'] as const;

export type Locale = (typeof supportedLocales)[number];

const dictionaries: Record<Locale, Record<string, string>> = {
    fr,
    en: {},
};

export type Replacements = Record<string, string | number>;

/** Laravel-style placeholders: "Hello :name" with { name: 'Awa' } → "Hello Awa". */
export function translate(
    locale: Locale,
    key: string,
    replacements: Replacements = {},
): string {
    const text = dictionaries[locale][key] ?? key;

    return Object.entries(replacements).reduce(
        (result, [name, value]) => result.replaceAll(`:${name}`, String(value)),
        text,
    );
}
