import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import { translate } from '@/lib/i18n';
import type { Replacements } from '@/lib/i18n';

export function useTranslation() {
    const { locale } = usePage().props;

    const t = useCallback(
        (key: string, replacements?: Replacements) =>
            translate(locale, key, replacements),
        [locale],
    );

    return { t, locale };
}
