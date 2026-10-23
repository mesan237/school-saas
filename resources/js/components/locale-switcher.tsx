import { router } from '@inertiajs/react';
import { useTranslation } from '@/hooks/use-translation';
import { supportedLocales } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { update as updateLocale } from '@/routes/locale';

const labels = { fr: 'Français', en: 'English' } as const;

export default function LocaleSwitcher({ className }: { className?: string }) {
    const { t, locale } = useTranslation();

    return (
        <div
            role="group"
            aria-label={t('Language')}
            className={cn('flex gap-1 text-sm', className)}
        >
            {supportedLocales.map((code) => (
                <button
                    key={code}
                    type="button"
                    lang={code}
                    aria-pressed={code === locale}
                    disabled={code === locale}
                    onClick={() =>
                        router.post(
                            updateLocale.url(),
                            { locale: code },
                            { preserveScroll: true },
                        )
                    }
                    className={cn(
                        'rounded-md px-2 py-1 text-muted-foreground hover:text-foreground',
                        code === locale &&
                            'bg-muted font-medium text-foreground',
                    )}
                >
                    {labels[code]}
                </button>
            ))}
        </div>
    );
}
