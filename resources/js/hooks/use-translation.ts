import { usePage } from '@inertiajs/react';
import { useCallback, useEffect } from 'react';
import { translate } from '@/lib/i18n';
import type { Replacements } from '@/lib/i18n';

export function useTranslation() {
    const { locale } = usePage().props;

    // The Blade root (and its <html lang>) is not re-rendered on Inertia visits; keep it in sync
    // so screen readers announce content in the right language after a switch.
    useEffect(() => {
        document.documentElement.lang = locale;
    }, [locale]);

    const t = useCallback(
        (key: string, replacements?: Replacements) =>
            translate(locale, key, replacements),
        [locale],
    );

    return { t, locale };
}
